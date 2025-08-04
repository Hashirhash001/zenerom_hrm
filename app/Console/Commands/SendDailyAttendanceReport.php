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
        $reportDate = Carbon::yesterday('Asia/Kolkata')->toDateString(); // e.g., 2025-08-01
        $reportDateFormatted = Carbon::yesterday('Asia/Kolkata')->format('d M Y'); // e.g., 01 Aug 2025
        $reportDateEnd = Carbon::yesterday('Asia/Kolkata')->endOfDay(); // e.g., 2025-08-01 23:59:59
        $format = $this->argument('format');

        if (!in_array($format, ['excel', 'pdf'])) {
            $this->error('Invalid format. Use "excel" or "pdf".');
            return 1;
        }

        // Fetch employees with role_id 1 (Admin) and 7 (HR)
        $recipients = Employee::whereIn('role_id', [1, 7])
            ->whereNotNull('company_email')
            ->where('status', 1)
            ->pluck('company_email')
            ->toArray();

        if (empty($recipients)) {
            Log::warning('No recipients found for daily attendance report', [
                'date' => $reportDate,
                'role_ids' => [1, 7]
            ]);
            $this->info('No recipients found. Report not sent.');
            return 0;
        }

        // Fetch employees
        $employees = Employee::whereNull('resignation')
            ->where('status', 1)
            ->whereNotNull('first_name')
            ->whereNotNull('last_name')
            ->whereNotNull('employee_id')
            ->get();

        // Fetch attendance records for the previous day
        $attendances = StaffAttendance::where('attendance_date', $reportDate)
            ->with('user.employee')
            ->get();

        // Log attendance data for debugging
        Log::info('Attendance records fetched', [
            'date' => $reportDate,
            'count' => $attendances->count(),
            'user_ids' => $attendances->pluck('user_id')->toArray(),
            'attendance_dates' => $attendances->pluck('attendance_date')->toArray()
        ]);

        $attendances = $attendances->map(function ($attendance) use ($reportDateEnd) {
            // Format login and logout times in AM/PM
            $attendance->formatted_login_time = Carbon::parse($attendance->created_at)->format('h:i:s A');
            $attendance->formatted_logout_time = $attendance->logout ? Carbon::parse($attendance->logout)->format('h:i:s A') : 'Still Working';

            // Calculate work hours
            $totalWorkSeconds = $attendance->total_work_seconds ?? 0;
            if (!$attendance->logout && $attendance->last_timer_start && !$this->isOnBreak($attendance->id)) {
                $lastTimerStart = Carbon::parse($attendance->last_timer_start);
                $totalWorkSeconds += $lastTimerStart->diffInSeconds($reportDateEnd); // Use end of previous day
            }
            $attendance->formatted_work_hours = $totalWorkSeconds > 0 ? $this->formatHours($totalWorkSeconds) : '-';
            if (!$attendance->logout && $totalWorkSeconds > 0) {
                $attendance->formatted_work_hours .= ' (Still Working)';
            }

            // Calculate break hours
            $attendance->total_break_seconds = (int) DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_end')
                ->sum(DB::raw('TIMESTAMPDIFF(SECOND, break_start, break_end)'));
            $attendance->formatted_break_hours = $this->formatHours($attendance->total_break_seconds);

            // Format attendance date
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

            // Log user_id mismatch for debugging
            if (!$attendance && $attendances->isNotEmpty()) {
                Log::warning('No attendance record found for user', [
                    'user_id' => $userId,
                    'date' => $reportDate
                ]);
            }

            $tasks = isset($assignedTasks[$userId][$reportDate]) ? $assignedTasks[$userId][$reportDate]->map(function ($task) {
                return $task->task->title . ' (' . ($task->task->project ? $task->task->project->name : 'No Project') . ')';
            })->implode(', ') : 'No Tasks';

            return [
                'Attendance Date' => $attendance ? $attendance->attendance_date_formatted : $reportDateFormatted,
                'Employee ID' => $employee->employee_id,
                'Name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->last_name),
                'Department' => $employee->department ? $employee->department->name : 'N/A',
                'Role' => $employee->role ? $employee->role->name : 'N/A',
                'Login Time' => $attendance ? $attendance->formatted_login_time : 'Absent',
                'Logout Time' => $attendance ? $attendance->formatted_logout_time : 'Absent',
                'Work Hours' => $attendance ? $attendance->formatted_work_hours : '-',
                'Break Hours' => $attendance ? $attendance->formatted_break_hours : '-',
                'Mode' => $attendance ? ($attendance->mode ?? 'Unknown') : 'Leave',
                'Tasks' => $tasks,
                'Approval Status' => $attendance ? ($attendance->approval_status ?? 'Pending') : 'Pending',
                'Notes' => $attendance ? ($attendance->notes ?? '-') : '-'
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
                // Clear font cache before generating PDF
                $fontCacheDir = storage_path('fonts/');
                if (is_dir($fontCacheDir)) {
                    foreach (glob($fontCacheDir . '*.ufm') as $file) {
                        @unlink($file);
                    }
                }
                // Set dompdf options
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

            Log::info('Daily attendance report sent', [
                'date' => $reportDate,
                'format' => $format,
                'recipients' => $recipients,
                'file' => $fileName
            ]);
            $this->info('Daily attendance report sent successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to send daily attendance report', [
                'date' => $reportDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Failed to send report: ' . $e->getMessage());
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
            ->whereNull('break_end')
            ->exists();
    }
}
