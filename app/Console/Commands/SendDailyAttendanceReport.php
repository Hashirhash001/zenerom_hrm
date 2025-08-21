<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\StaffAttendance;
use App\Models\TaskAssigned;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDailyAttendanceReport extends Command
{
    protected $signature = 'attendance:send-daily-report {format=excel : The format of the report (excel or pdf)}';
    protected $description = 'Generate and send previous day\'s attendance report to admins and HR (role_id 1, 7)';

    public function handle()
    {
        $reportDate = Carbon::yesterday('Asia/Kolkata')->toDateString(); // e.g., 2025-08-07
        $reportDateFormatted = Carbon::yesterday('Asia/Kolkata')->format('d M Y'); // e.g., 07 Aug 2025
        $reportDateEnd = Carbon::yesterday('Asia/Kolkata')->endOfDay(); // e.g., 2025-08-07 23:59:59
        $format = $this->argument('format');

        if (!in_array($format, ['excel', 'pdf'])) {
            $this->error('Invalid format. Use "excel" or "pdf".');
            return 1;
        }

        // Fetch employees with role_id 1 (Admin) and 7 (HR)
        $recipients = Employee::whereIn('role_id', [1])
            ->whereNotNull('company_email')
            ->where('status', 1)
            ->pluck('company_email')
            ->toArray();

        // Add hrm.zenerom@gmail.com to recipients, ensuring no duplicates
        $additionalEmail = 'hrm.zenerom@gmail.com';
        if (!in_array($additionalEmail, $recipients)) {
            $recipients[] = $additionalEmail;
        }

        if (empty($recipients)) {
            Log::warning('No recipients found for daily attendance report', [
                'date' => $reportDate,
                'role_ids' => [1, 7]
            ]);
            $this->info('No recipients found. Report not sent.');
            return 0;
        }

        // Fetch employees with necessary fields
        $employees = Employee::whereNull('resignation')
            ->where('employees.status', 1)
            ->whereNotNull('employees.first_name')
            ->whereNotNull('employees.last_name')
            ->whereNotNull('employees.employee_id')
            ->whereNotNull('employees.user_id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('roles', 'employees.role_id', '=', 'roles.id')
            ->select(
                'employees.id',
                'employees.user_id',
                'employees.first_name',
                'employees.middle_name',
                'employees.last_name',
                'employees.employee_id',
                'employees.saturday_exempt',
                'departments.name as department',
                'roles.name as role'
            )
            ->get();

        // Log employees with null user_id for debugging
        $invalidEmployees = Employee::whereNull('resignation')
            ->where('employees.status', 1)
            ->whereNull('user_id')
            ->get();
        if ($invalidEmployees->isNotEmpty()) {
            Log::warning('Employees with null user_id found', [
                'invalid_employees' => $invalidEmployees->pluck('employee_id')->toArray(),
                'count' => $invalidEmployees->count()
            ]);
        }

        // Fetch attendance records for the previous day
        $attendances = StaffAttendance::where('attendance_date', $reportDate)
            ->whereNotNull('user_id')
            ->whereRaw('user_id REGEXP "^[0-9]+$"')
            ->select('id', 'user_id', 'attendance_date', 'mode', 'created_at', 'logout', 'total_work_seconds', 'last_timer_start')
            ->get();

        // Log attendance data
        Log::info('Attendance records fetched', [
            'date' => $reportDate,
            'count' => $attendances->count(),
            'user_ids' => $attendances->pluck('user_id')->toArray(),
            'attendance_dates' => $attendances->pluck('attendance_date')->toArray()
        ]);

        // Group and process attendances
        $attendances = $attendances->map(function ($attendance) use ($reportDate, $reportDateEnd) {
            $attendance->formatted_login_time = Carbon::parse($attendance->created_at)->format('h:i:s A');
            $attendance->formatted_logout_time = $attendance->logout ? Carbon::parse($attendance->logout)->format('h:i:s A') : 'Still Working';

            // Calculate work hours
            $totalWorkSeconds = $attendance->total_work_seconds ?? 0;
            if (!$attendance->logout && $attendance->last_timer_start && !$this->isOnBreak($attendance->id)) {
                $lastTimerStart = Carbon::parse($attendance->last_timer_start);
                $totalWorkSeconds += $lastTimerStart->diffInSeconds($reportDateEnd);
            }
            $attendance->formatted_work_hours = $totalWorkSeconds > 0 ? $this->formatHours($totalWorkSeconds) : '-';
            if (!$attendance->logout && $totalWorkSeconds > 0) {
                $attendance->formatted_work_hours .= ' (Still Working)';
            }

            // Calculate break hours
            $breaks = DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_end')
                ->select('break_start', 'break_end')
                ->get();

            // Log raw breaks for debugging
            Log::debug('Raw breaks for attendance', [
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'date' => $reportDate,
                'breaks' => $breaks->toArray()
            ]);

            $totalBreakSeconds = 0;
            $breakDetails = [];
            foreach ($breaks as $break) {
                $start = Carbon::parse($break->break_start, 'Asia/Kolkata');
                $end = Carbon::parse($break->break_end, 'Asia/Kolkata');
                $seconds = $start->diffInSeconds($end);
                $totalBreakSeconds += $seconds;
                $breakDetails[] = [
                    'start' => $start->toDateTimeString(),
                    'end' => $end->toDateTimeString(),
                    'seconds' => $seconds,
                    'formatted' => $this->formatHours($seconds)
                ];
            }

            $attendance->total_break_seconds = (int) $totalBreakSeconds;
            $attendance->formatted_break_hours = $this->formatHours($attendance->total_break_seconds);

            // Log break details
            Log::debug('Break hours calculation', [
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'date' => $reportDate,
                'total_break_seconds' => $attendance->total_break_seconds,
                'formatted_break_hours' => $attendance->formatted_break_hours,
                'breaks' => $breakDetails
            ]);

            $attendance->attendance_date_formatted = Carbon::parse($attendance->attendance_date)->format('d M Y');

            return $attendance;
        })->groupBy('user_id');

        // Fetch task assignments for the previous day
        $assignedTasks = TaskAssigned::where('date', $reportDate)
            ->with(['task.project', 'task.service'])
            ->get()
            ->groupBy(['staff_id', 'date']);

        // Prepare data for export
        $reportData = $employees->map(function ($employee) use ($attendances, $assignedTasks, $reportDate, $reportDateFormatted) {
            $userId = (string) $employee->user_id;
            $attendance = isset($attendances[$userId]) ? $attendances[$userId]->first() : null;

            // Log user_id mismatch
            if (!$attendance && $attendances->isNotEmpty()) {
                Log::warning('No attendance record found for user', [
                    'user_id' => $userId,
                    'date' => $reportDate
                ]);
            }

            $tasks = isset($assignedTasks[$userId][$reportDate]) ? $assignedTasks[$userId][$reportDate]->map(function ($task) {
                return $task->task->title . ' (' . ($task->task->project ? $task->task->project->name : 'No Project') . ')';
            })->implode(', ') : 'No Tasks';

            // Calculate leave, WFH, and half day counts for the previous day
            $leaveCount = 0;
            $wfhCount = 0;
            $halfDayCount = 0;
            $carbonDate = Carbon::parse($reportDate, 'Asia/Kolkata');

            // Skip Sundays and Saturdays for saturday_exempt employees
            if (!$carbonDate->isSunday() && !($employee->saturday_exempt && $carbonDate->isSaturday())) {
                if (!$attendance || strcasecmp($attendance->mode, 'Leave') === 0) {
                    $leaveCount = 1;
                }
                if ($attendance && strcasecmp($attendance->mode, 'Work from home') === 0) {
                    $wfhCount = 1;
                }
                if ($attendance && strcasecmp($attendance->mode, 'Half Day') === 0) {
                    $halfDayCount = 1;
                }
            }

            // Log counts for debugging
            Log::debug('Employee attendance counts', [
                'user_id' => $userId,
                'employee_id' => $employee->employee_id,
                'date' => $reportDate,
                'is_sunday' => $carbonDate->isSunday(),
                'is_saturday_exempt' => $employee->saturday_exempt,
                'attendance_exists' => !is_null($attendance),
                'mode' => $attendance ? $attendance->mode : 'None',
                'leave_count' => $leaveCount,
                'wfh_count' => $wfhCount,
                'half_day_count' => $halfDayCount
            ]);

            return [
                'Attendance Date' => $attendance ? $attendance->attendance_date_formatted : $reportDateFormatted,
                'Employee ID' => $employee->employee_id,
                'Name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->last_name),
                'Department' => $employee->department ?: 'N/A',
                'Role' => $employee->role ?: 'N/A',
                'Login Time' => $attendance ? $attendance->formatted_login_time : 'Absent',
                'Logout Time' => $attendance ? $attendance->formatted_logout_time : 'Absent',
                'Work Hours' => $attendance ? $attendance->formatted_work_hours : '-',
                'Break Hours' => $attendance ? $attendance->formatted_break_hours : '-',
                'Mode' => $attendance ? ($attendance->mode ?? 'Unknown') : 'Leave',
                'Tasks' => $tasks,
                'Leave Count' => $leaveCount,
                'WFH Count' => $wfhCount,
                'Half Day Count' => $halfDayCount
            ];
        })->toArray();

        // Generate file
        $fileName = 'Daily_Attendance_Report_' . $reportDate . '.' . ($format === 'excel' ? 'xlsx' : 'pdf');
        $filePath = storage_path('app/public/' . $fileName);

        if ($format === 'excel') {
            try {
                Excel::store(new class($reportData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                    protected $data;

                    public function __construct(array $data)
                    {
                        $this->data = $data;
                    }

                    public function array(): array
                    {
                        return $this->data;
                    }

                    public function headings(): array
                    {
                        return array_keys($this->data[0]);
                    }
                }, 'public/' . $fileName);
            } catch (\Exception $e) {
                Log::error('Failed to generate Excel report', [
                    'date' => $reportDate,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error('Failed to generate Excel: ' . $e->getMessage());
                return 1;
            }
        } else {
            try {
                if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                    throw new \Exception('PDF Facade class not found. Ensure barryvdh/laravel-dompdf is installed and configured.');
                }
                // Clear font cache
                $fontCacheDir = storage_path('fonts/');
                if (is_dir($fontCacheDir)) {
                    foreach (glob($fontCacheDir . '*.ufm') as $file) {
                        @unlink($file);
                    }
                }
                Pdf::setOptions([
                    'default_font' => 'Helvetica',
                    'defaultPaperSize' => 'a4',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0
                ]);

                $pdf = Pdf::loadView('attendance.report_pdf', [
                    'reportData' => $reportData,
                    'date' => Carbon::parse($reportDate)->format('d M Y')
                ]);
                $pdf->save($filePath);
            } catch (\Exception $e) {
                Log::error('Failed to generate PDF report', [
                    'date' => $reportDate,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error('Failed to generate PDF: ' . $e->getMessage());
                return 1;
            }
        }

        // Send email
        try {
            Mail::send('emails.attendance_report', [
                'date' => Carbon::parse($reportDate)->format('d M Y'),
                'format' => $format
            ], function ($message) use ($recipients, $filePath, $fileName, $reportDate) {
                $message->to($recipients)
                        ->subject('Daily Attendance Report - ' . Carbon::parse($reportDate)->format('d M Y'))
                        ->attach($filePath, [
                            'as' => $fileName,
                            'mime' => (str_ends_with($fileName, '.xlsx') ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/pdf')
                        ]);
            });

            Log::info('Daily attendance report sent via email', [
                'date' => $reportDate,
                'format' => $format,
                'recipients' => $recipients,
                'file' => $fileName
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send daily attendance report via email', [
                'date' => $reportDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Failed to send email report: ' . $e->getMessage());
            return 1;
        }

        // Clean up file
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return 0;
    }

    private function formatHours($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d hour%s %d minute%s", $hours, $hours != 1 ? 's' : '', $minutes, $minutes != 1 ? 's' : '');
    }

    private function isOnBreak($attendanceId)
    {
        return DB::table('staff_breaks')
            ->where('attendance_id', $attendanceId)
            ->whereNotNull('break_start')
            ->whereNull('break_end')
            ->exists();
    }
}
