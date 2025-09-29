<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Employee;

use App\Models\TaskAssigned;
use App\Models\Department;
use App\Models\Role;

use Illuminate\Http\Request;
use App\Models\PublicHoliday;
use App\Models\StaffAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Kolkata');

        // Set default date range based on role
        if (in_array($user->role_id, [4, 6, 12, 13]) || !in_array($user->role_id, [1, 2, 3, 7, 9])) {
            $start_date = $request->get('start_date', $today->copy()->subDays(6)->toDateString());
            $end_date = $request->get('end_date', $today->toDateString());
        } else {
            $start_date = $request->get('start_date', $today->toDateString());
            $end_date = $request->get('end_date', $today->toDateString());
        }

        $nameFilter = $request->get('name');
        $roleFilter = $request->get('role');
        $departmentFilter = $request->get('department');
        $statusFilter = $request->get('status');

        $query = DB::table('staff_attendance')
            ->join('employees', 'staff_attendance.user_id', '=', 'employees.user_id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('roles', 'employees.role_id', '=', 'roles.id')
            ->select(
                'staff_attendance.*',
                DB::raw("CONCAT(CONCAT_WS(' ', employees.first_name, employees.middle_name, employees.last_name), ' (', employees.employee_id, ')') as employee_name"),
                'departments.name as department',
                'roles.name as role'
            );

        $query->whereBetween('attendance_date', [$start_date, $end_date]);

        if ($nameFilter) {
            $query->where(function ($q) use ($nameFilter) {
                $q->where('employees.first_name', 'LIKE', "%{$nameFilter}%")
                    ->orWhere('employees.middle_name', 'LIKE', "%{$nameFilter}%")
                    ->orWhere('employees.last_name', 'LIKE', "%{$nameFilter}%")
                    ->orWhere('employees.employee_id', 'LIKE', "%{$nameFilter}%");
            });
        }

        if ($roleFilter) {
            $query->where('employees.role_id', $roleFilter);
        }

        if ($departmentFilter) {
            $query->where('employees.department_id', $departmentFilter);
        }

        if ($statusFilter) {
            if ($statusFilter === 'still_working') {
                $query->whereNull('staff_attendance.logout')
                      ->whereNotExists(function ($q) {
                          $q->select(DB::raw(1))
                            ->from('staff_breaks')
                            ->whereRaw('staff_breaks.attendance_id = staff_attendance.id')
                            ->whereNotNull('break_start')
                            ->whereNull('break_end');
                      });
            } elseif ($statusFilter === 'on_break') {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('staff_breaks')
                      ->whereRaw('staff_breaks.attendance_id = staff_attendance.id')
                      ->whereNotNull('break_start')
                      ->whereNull('break_end');
                });
            } elseif ($statusFilter === 'logged_out') {
                $query->whereNotNull('staff_attendance.logout');
            }
        }

        if (in_array($user->role_id, [1, 2, 7, 9])) {
            // Admins see all records
        } elseif ($user->role_id == 3) {
            $departmentId = optional($user->employee)->department_id;
            if ($departmentId) {
                $query->where('employees.department_id', $departmentId);
            }
        } else {
            $query->where('staff_attendance.user_id', $user->id);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get()->map(function ($attendance) {
            $attendance->is_on_break = $this->isOnBreak($attendance->id);
            $attendance->total_break_seconds = (int) DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_end')
                ->sum(DB::raw('TIMESTAMPDIFF(SECOND, break_start, break_end)'));
            return $attendance;
        });

        if ($attendances->isEmpty()) {
            Log::warning('No attendance records found for user', [
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'filters' => [
                    'name' => $nameFilter,
                    'role' => $roleFilter,
                    'department' => $departmentFilter,
                    'status' => $statusFilter
                ]
            ]);
        }

        $roles = DB::table('roles')->select('id', 'name')->get();
        $departments = DB::table('departments')->select('id', 'name')->get();

        if (in_array($user->role_id, [1, 2, 7])) {
            return view('attendance.index', compact('attendances', 'start_date', 'end_date', 'nameFilter', 'roleFilter', 'departmentFilter', 'statusFilter', 'roles', 'departments'));
        }

        return view('attendance.staffindex', compact('attendances', 'start_date', 'end_date', 'nameFilter', 'roleFilter', 'departmentFilter', 'statusFilter', 'roles', 'departments'));
    }

    public function fetchAttendances(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Kolkata');
        $now = Carbon::now('Asia/Kolkata');

        // Set default date range based on role
        if (in_array($user->role_id, [4, 6, 12, 13]) || !in_array($user->role_id, [1, 2, 3, 7, 9])) {
            $start_date = $request->get('start_date', $today->copy()->subDays(6)->toDateString());
            $end_date = $request->get('end_date', $today->toDateString());
        } else {
            $start_date = $request->get('start_date', $today->toDateString());
            $end_date = $request->get('end_date', $today->toDateString());
        }

        $nameFilter = $request->get('name');
        $roleFilter = $request->get('role');
        $departmentFilter = $request->get('department');
        $statusFilter = $request->get('status');

        $query = DB::table('staff_attendance')
            ->join('employees', 'staff_attendance.user_id', '=', 'employees.user_id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('roles', 'employees.role_id', '=', 'roles.id')
            ->select(
                'staff_attendance.*',
                DB::raw("CONCAT(CONCAT_WS(' ', employees.first_name, employees.middle_name, employees.last_name), ' (', employees.employee_id, ')') as employee_name"),
                'departments.name as department',
                'roles.name as role'
            );

        $query->whereBetween('attendance_date', [$start_date, $end_date]);

        if ($nameFilter) {
            $query->where(function ($q) use ($nameFilter) {
                $q->where('employees.first_name', 'LIKE', "%{$nameFilter}%")
                    ->orWhere('employees.middle_name', 'LIKE', "%{$nameFilter}%")
                    ->orWhere('employees.last_name', 'LIKE', "%{$nameFilter}%")
                    ->orWhere('employees.employee_id', 'LIKE', "%{$nameFilter}%");
            });
        }

        if ($roleFilter) {
            $query->where('employees.role_id', $roleFilter);
        }

        if ($departmentFilter) {
            $query->where('employees.department_id', $departmentFilter);
        }

        if ($statusFilter) {
            if ($statusFilter === 'still_working') {
                $query->whereNull('staff_attendance.logout')
                      ->whereNotExists(function ($q) {
                          $q->select(DB::raw(1))
                            ->from('staff_breaks')
                            ->whereRaw('staff_breaks.attendance_id = staff_attendance.id')
                            ->whereNotNull('break_start')
                            ->whereNull('break_end');
                      });
            } elseif ($statusFilter === 'on_break') {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('staff_breaks')
                      ->whereRaw('staff_breaks.attendance_id = staff_attendance.id')
                      ->whereNotNull('break_start')
                      ->whereNull('break_end');
                });
            } elseif ($statusFilter === 'logged_out') {
                $query->whereNotNull('staff_attendance.logout');
            }
        }

        if (in_array($user->role_id, [1, 2, 7, 9])) {
            // Admins see all records
        } elseif ($user->role_id == 3) {
            $departmentId = optional($user->employee)->department_id;
            if ($departmentId) {
                $query->where('employees.department_id', $departmentId);
            }
        } else {
            $query->where('staff_attendance.user_id', $user->id);
        }

        $attendances = $query->get()->map(function ($attendance) use ($now) {
            $attendance->is_on_break = $this->isOnBreak($attendance->id);
            $attendance->total_break_seconds = (int) DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_end')
                ->sum(DB::raw('TIMESTAMPDIFF(SECOND, break_start, break_end)'));
            // Calculate and update total_work_seconds for active sessions
            if (!$attendance->logout && !$attendance->is_on_break) {
                $start = Carbon::parse($attendance->created_at, 'Asia/Kolkata');
                $sessionSeconds = $now->diffInSeconds($start);
                $totalWorkSeconds = max(0, $sessionSeconds - $attendance->total_break_seconds);
                // Update database
                DB::table('staff_attendance')
                    ->where('id', $attendance->id)
                    ->update([
                        'total_work_seconds' => $totalWorkSeconds,
                        'last_timer_start' => $now
                    ]);
                $attendance->total_work_seconds = $totalWorkSeconds;
            }
            return $attendance;
        });

        if ($attendances->isEmpty()) {
            Log::warning('No attendance records found for user in fetchAttendances', [
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'filters' => [
                    'name' => $nameFilter,
                    'role' => $roleFilter,
                    'department' => $departmentFilter,
                    'status' => $statusFilter
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'attendances' => $attendances
        ]);
    }

    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Kolkata')->toDateString();
        $ipAddress = $request->ip();
        $mode = $request->input('mode', 'manual');

        $attendanceExists = DB::table('staff_attendance')
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->exists();

        if (!$attendanceExists) {
            DB::table('staff_attendance')->insert([
                'user_id' => $user->id,
                'attendance_date' => $today,
                'created_at' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'mode' => $mode,
                'system_ip' => $ipAddress,
                'total_work_seconds' => 0,
                'last_timer_start' => Carbon::now('Asia/Kolkata')->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'checkInTime' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'message' => 'Check-in recorded successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Check-in already recorded for today.'
        ], 400);
    }

    public function checkOut(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today('Asia/Kolkata')->toDateString();
        $now = Carbon::now('Asia/Kolkata');
        $forceCheckout = $request->input('force_checkout', false);

        Log::info('CheckOut called', [
            'user_id' => $userId,
            'force_checkout' => $forceCheckout,
            'now' => $now->toDateTimeString()
        ]);

        // Check for incomplete tasks
        $assignments = DB::table('task_assigned')
            ->where('staff_id', $userId)
            ->whereDate('date', $today)
            ->join('tasks', 'task_assigned.task_id', '=', 'tasks.id')
            ->select('task_assigned.id as assignment_id', 'task_assigned.task_id', 'tasks.title as task_name')
            ->get();

        if ($assignments->count() > 0) {
            $incompleteTasks = [];
            foreach ($assignments as $assignment) {
                $documentCount = DB::table('task_documents')
                    ->where('task_assigned_id', $assignment->assignment_id)
                    ->count();
                if ($documentCount < 1) {
                    $incompleteTasks[] = [
                        'task_id' => $assignment->task_id,
                        'task_name' => $assignment->task_name
                    ];
                }
            }
            if (!empty($incompleteTasks)) {
                Log::info('Incomplete tasks found', ['tasks' => $incompleteTasks]);
                return response()->json([
                    'success' => false,
                    'incomplete_tasks' => $incompleteTasks,
                    'message' => 'You have incomplete tasks that need updates.'
                ]);
            }
        }

        // Fetch current attendance record
        $attendance = DB::table('staff_attendance')
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance || $attendance->logout) {
            Log::warning('Invalid attendance record', [
                'attendance_exists' => !!$attendance,
                'already_checked_out' => $attendance ? !!$attendance->logout : false
            ]);
            return response()->json([
                'success' => false,
                'message' => $attendance ? 'You have already checked out today.' : 'No check-in record found for today.'
            ], 400);
        }

        // Calculate total work time
        $totalWorkSeconds = $attendance->total_work_seconds ?? 0;
        if ($attendance->last_timer_start && !$this->isOnBreak($attendance->id)) {
            $lastTimerStart = Carbon::parse($attendance->last_timer_start);
            $secondsSinceLastStart = $now->diffInSeconds($lastTimerStart);
            Log::info('Adding seconds since last timer start', [
                'last_timer_start' => $lastTimerStart->toDateTimeString(),
                'seconds_added' => $secondsSinceLastStart,
                'total_before' => $totalWorkSeconds
            ]);
            $totalWorkSeconds += $secondsSinceLastStart;
        }

        // Calculate total break time
        $totalBreakSeconds = DB::table('staff_breaks')
            ->where('attendance_id', $attendance->id)
            ->whereNotNull('break_end')
            ->select(DB::raw('SUM(TIME_TO_SEC(TIMEDIFF(break_end, break_start))) as total_break_seconds'))
            ->value('total_break_seconds') ?? 0;

        // Check for active break
        $activeBreak = DB::table('staff_breaks')
            ->where('attendance_id', $attendance->id)
            ->whereNotNull('break_start')
            ->whereNull('break_end')
            ->first();
        if ($activeBreak) {
            $breakSeconds = $now->diffInSeconds(Carbon::parse($activeBreak->break_start));
            $totalBreakSeconds += $breakSeconds;
            DB::table('staff_breaks')
                ->where('id', $activeBreak->id)
                ->update([
                    'break_end' => $now,
                    'updated_at' => $now
                ]);
        }

        Log::info('Calculated total work and break seconds', [
            'total_work_seconds' => $totalWorkSeconds,
            'total_break_seconds' => $totalBreakSeconds
        ]);

        // Fetch employee's work schedule
        $employee = Employee::where('user_id', $userId)->first();
        $updateData = [
            'total_work_seconds' => $totalWorkSeconds,
            'total_break_seconds' => $totalBreakSeconds,
            'logout' => $now->toDateTimeString(),
            'last_timer_start' => null
        ];

        $loginTime = Carbon::parse($attendance->created_at)->format('h:i:s A');
        $logoutTime = $now->format('h:i:s A');
        $totalBreakFormatted = $this->formatDuration($totalBreakSeconds);

        // Only apply force checkout for specific values
        if ($forceCheckout === 'half_day' || $forceCheckout === 'leave') {
            Log::info('Force checkout applied', ['force_checkout' => $forceCheckout]);
            if ($forceCheckout === 'half_day') {
                $updateData['mode'] = 'Half Day';
            } elseif ($forceCheckout === 'leave') {
                $updateData['mode'] = 'Leave';
            }
        } else {
            // Check work schedule or default 8-hour rule
            if ($employee->work_start_time && $employee->work_end_time) {
                try {
                    $workStart = Carbon::createFromFormat('H:i:s', $employee->work_start_time, 'Asia/Kolkata')->setDateFrom(Carbon::today('Asia/Kolkata'));
                    $workEnd = Carbon::createFromFormat('H:i:s', $employee->work_end_time, 'Asia/Kolkata')->setDateFrom(Carbon::today('Asia/Kolkata'));

                    if ($workEnd->lessThan($workStart)) {
                        $workEnd->addDay();
                    }
                    // 1 hour less + 5-minute buffer
                    $requiredWorkSeconds = $workEnd->diffInSeconds($workStart) - 3600 - 300; // 5-minute buffer
                    $halfDaySeconds = $requiredWorkSeconds / 2;

                    $checkInTime = Carbon::parse($attendance->created_at)->setDateFrom(Carbon::today('Asia/Kolkata'));
                    $lateThreshold = $workStart->copy()->addMinutes(15);
                    $latenessSeconds = $checkInTime->greaterThan($lateThreshold) ? $checkInTime->diffInSeconds($workStart) : 0;

                    $minCheckoutTime = $workEnd->copy()->addSeconds($latenessSeconds);

                    Log::info('Work schedule check', [
                        'work_start' => $workStart->toDateTimeString(),
                        'work_end' => $workEnd->toDateTimeString(),
                        'total_work_seconds' => $totalWorkSeconds,
                        'required_work_seconds' => $requiredWorkSeconds,
                        'half_day_seconds' => $halfDaySeconds,
                        'lateness_seconds' => $latenessSeconds,
                        'min_checkout_time' => $minCheckoutTime->toDateTimeString(),
                        'current_time' => $now->toDateTimeString()
                    ]);

                    if ($totalWorkSeconds < $halfDaySeconds) {
                        $hoursWorked = $this->formatHours($totalWorkSeconds);
                        return response()->json([
                            'success' => false,
                            'leave_warning' => true,
                            'message' => "You have worked {$hoursWorked}. Checking out now will mark your attendance as 'Leave'.",
                            'total_work_seconds' => $totalWorkSeconds,
                            'total_break_seconds' => $totalBreakSeconds,
                            'total_break_formatted' => $totalBreakFormatted,
                            'login_time' => $loginTime,
                            'logout_time' => $logoutTime
                        ]);
                    } elseif ($totalWorkSeconds < $requiredWorkSeconds) {
                        $hoursWorked = $this->formatHours($totalWorkSeconds);
                        $timeLeftSeconds = max(0, $requiredWorkSeconds - $totalWorkSeconds);
                        return response()->json([
                            'success' => false,
                            'half_day_warning' => true,
                            'message' => "You have worked {$hoursWorked}. You need {$this->formatDuration($timeLeftSeconds)} more to complete your schedule. Checking out now will mark your attendance as 'Half Day'.",
                            'total_work_seconds' => $totalWorkSeconds,
                            'total_break_seconds' => $totalBreakSeconds,
                            'total_break_formatted' => $totalBreakFormatted,
                            'login_time' => $loginTime,
                            'logout_time' => $logoutTime
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error parsing work schedule', [
                        'error' => $e->getMessage(),
                        'work_start_time' => $employee->work_start_time,
                        'work_end_time' => $employee->work_end_time
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid work schedule configuration. Please contact support.'
                    ], 500);
                }
            } else {
                // Default 8-hour rule
                $requiredWorkSeconds = 8 * 3600; // 8 hours
                $halfDaySeconds = 4 * 3600; // 4 hours

                Log::info('Default 8-hour rule applied', [
                    'total_work_seconds' => $totalWorkSeconds,
                    'required_work_seconds' => $requiredWorkSeconds,
                    'half_day_seconds' => $halfDaySeconds
                ]);

                if ($totalWorkSeconds < $halfDaySeconds) {
                    $hoursWorked = $this->formatHours($totalWorkSeconds);
                    return response()->json([
                        'success' => false,
                        'leave_warning' => true,
                        'message' => "You have worked {$hoursWorked}. Checking out now will mark your attendance as 'Leave'.",
                        'total_work_seconds' => $totalWorkSeconds,
                        'total_break_seconds' => $totalBreakSeconds,
                        'total_break_formatted' => $totalBreakFormatted,
                        'login_time' => $loginTime,
                        'logout_time' => $logoutTime
                    ]);
                } elseif ($totalWorkSeconds < $requiredWorkSeconds) {
                    $hoursWorked = $this->formatHours($totalWorkSeconds);
                    $timeLeftSeconds = max(0, $requiredWorkSeconds - $totalWorkSeconds);
                    return response()->json([
                        'success' => false,
                        'half_day_warning' => true,
                        'message' => "You have worked {$hoursWorked}. You need {$this->formatDuration($timeLeftSeconds)} more to complete 8 hours. Checking out now will mark your attendance as 'Half Day'.",
                        'total_work_seconds' => $totalWorkSeconds,
                        'total_break_seconds' => $totalBreakSeconds,
                        'total_break_formatted' => $totalBreakFormatted,
                        'login_time' => $loginTime,
                        'logout_time' => $logoutTime
                    ]);
                }
            }
        }

        // Proceed with checkout
        Log::info('Proceeding with checkout', ['update_data' => $updateData]);
        DB::table('staff_attendance')
            ->where('id', $attendance->id)
            ->update($updateData);

        $successMessage = isset($updateData['mode']) ? "Check-out successful. Marked as {$updateData['mode']}." : 'Full working hours completed successfully.';
        return response()->json([
            'success' => true,
            'message' => $successMessage,
            'login_time' => $loginTime,
            'logout_time' => $logoutTime,
            'total_break_seconds' => $totalBreakSeconds,
            'total_break_formatted' => $totalBreakFormatted
        ]);
    }

    /**
     * Helper function to format seconds to hours and minutes
     */
    private function formatHours($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d hour%s %d minute%s", $hours, $hours != 1 ? 's' : '', $minutes, $minutes != 1 ? 's' : '');
    }

    /**
     * Helper function to format duration in hours and minutes if >= 60 minutes, else minutes
     */
    private function formatDuration($seconds)
    {
        $minutes = floor($seconds / 60);
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return sprintf("%d hour%s %d minute%s", $hours, $hours != 1 ? 's' : '', $remainingMinutes, $remainingMinutes != 1 ? 's' : '');
        }
        return sprintf("%d minute%s", $minutes, $minutes != 1 ? 's' : '');
    }

    public function break(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today('Asia/Kolkata');
        $action = $request->input('action');
        $now = Carbon::now('Asia/Kolkata');

        if (!in_array($action, ['start', 'end'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid break action.'
            ], 400);
        }

        $attendance = DB::table('staff_attendance')
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No check-in record found for today. Cannot record break.'
            ], 400);
        }

        if ($attendance->logout) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot record break: You have already checked out today.'
            ], 400);
        }

        $totalBreakSeconds = (int) DB::table('staff_breaks')
            ->where('attendance_id', $attendance->id)
            ->whereNotNull('break_end')
            ->sum(DB::raw('TIMESTAMPDIFF(SECOND, break_start, break_end)'));

        if ($action === 'start') {
            $activeBreak = DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_start')
                ->whereNull('break_end')
                ->first();

            if ($activeBreak) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already on a break.'
                ], 400);
            }

            // Calculate total work seconds up to now
            $start = Carbon::parse($attendance->created_at, 'Asia/Kolkata');
            $sessionSeconds = $now->diffInSeconds($start);
            $totalWorkSeconds = max(0, $sessionSeconds - $totalBreakSeconds);

            DB::table('staff_attendance')
                ->where('id', $attendance->id)
                ->update([
                    'total_work_seconds' => $totalWorkSeconds,
                    'last_timer_start' => null
                ]);

            DB::table('staff_breaks')->insert([
                'user_id' => $userId,
                'attendance_id' => $attendance->id,
                'break_start' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            return response()->json([
                'success' => true,
                'breakTime' => $now->toDateTimeString(),
                'message' => 'Break started successfully.',
                'is_on_break' => true,
                'total_work_seconds' => $totalWorkSeconds,
                'total_break_seconds' => $totalBreakSeconds,
                'attendance_id' => $attendance->id
            ]);
        }

        if ($action === 'end') {
            $activeBreak = DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_start')
                ->whereNull('break_end')
                ->first();

            if (!$activeBreak) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active break found to end.'
                ], 400);
            }

            $breakSeconds = $now->diffInSeconds(Carbon::parse($activeBreak->break_start));
            $totalBreakSeconds += $breakSeconds;

            DB::table('staff_breaks')
                ->where('id', $activeBreak->id)
                ->update([
                    'break_end' => $now,
                    'updated_at' => $now
                ]);

            DB::table('staff_attendance')
                ->where('id', $attendance->id)
                ->update([
                    'total_break_seconds' => $totalBreakSeconds,
                    'last_timer_start' => $now
                ]);

            return response()->json([
                'success' => true,
                'breakTime' => $now->toDateTimeString(),
                'message' => 'Break ended successfully.',
                'is_on_break' => false,
                'total_work_seconds' => $attendance->total_work_seconds ?? 0,
                'total_break_seconds' => $totalBreakSeconds,
                'attendance_id' => $attendance->id,
                'approval_status' => $attendance->approval_status ?? 'pending'
            ]);
        }
    }

    public function syncTimer(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today('Asia/Kolkata');
        $now = Carbon::now('Asia/Kolkata');

        $attendance = DB::table('staff_attendance')
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($attendance && !$attendance->logout && !$this->isOnBreak($attendance->id)) {
            $start = Carbon::parse($attendance->created_at, 'Asia/Kolkata');
            $sessionSeconds = $now->diffInSeconds($start);
            $totalBreakSeconds = (int) DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_end')
                ->sum(DB::raw('TIMESTAMPDIFF(SECOND, break_start, break_end)'));
            $totalWorkSeconds = max(0, $sessionSeconds - $totalBreakSeconds);

            DB::table('staff_attendance')
                ->where('id', $attendance->id)
                ->update([
                    'total_work_seconds' => $totalWorkSeconds,
                    'last_timer_start' => $now
                ]);

            return response()->json([
                'success' => true,
                'total_work_seconds' => $totalWorkSeconds
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function getAttendanceStatus()
    {
        $userId = Auth::id();
        $today = Carbon::today('Asia/Kolkata');
        $now = Carbon::now('Asia/Kolkata');
        $attendance = DB::table('staff_attendance')
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $today)
            ->first();

        $response = [
            'isCheckedIn' => false,
            'hasCheckedOut' => false,
            'isOnBreak' => false,
            'totalWorkSeconds' => 0,
            'breakSeconds' => 0,
            'attendance_id' => null
        ];

        if ($attendance) {
            $response['isCheckedIn'] = true;
            $response['hasCheckedOut'] = !is_null($attendance->logout);
            $response['isOnBreak'] = $this->isOnBreak($attendance->id);
            $response['attendance_id'] = $attendance->id;

            $totalBreakSeconds = (int) DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_end')
                ->sum(DB::raw('TIMESTAMPDIFF(SECOND, break_start, break_end)'));
            $response['breakSeconds'] = $totalBreakSeconds;

            $totalWorkSeconds = $attendance->total_work_seconds ?? 0;
            if (!$response['hasCheckedOut'] && !$response['isOnBreak']) {
                $start = Carbon::parse($attendance->created_at, 'Asia/Kolkata');
                $sessionSeconds = $now->diffInSeconds($start);
                $totalWorkSeconds = max(0, $sessionSeconds - $totalBreakSeconds);
                // Update database
                DB::table('staff_attendance')
                    ->where('id', $attendance->id)
                    ->update([
                        'total_work_seconds' => $totalWorkSeconds,
                        'last_timer_start' => $now
                    ]);
            }
            $response['totalWorkSeconds'] = $totalWorkSeconds;
        }

        return response()->json($response);
    }

    private function isOnBreak($attendanceId)
    {
        return DB::table('staff_breaks')
            ->where('attendance_id', $attendanceId)
            ->whereNotNull('break_start')
            ->whereNull('break_end')
            ->exists();
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'attendance_id' => 'required|exists:staff_attendance,id',
            'login'         => 'required', // Expecting a valid date string
            'logout'        => 'nullable', // Logout can be empty
            'mode'          => 'required',
        ]);

        $attendance = StaffAttendance::findOrFail($validatedData['attendance_id']);
        $now = Carbon::now('Asia/Kolkata');

        // Parse login time
        $attendance->created_at = Carbon::parse($validatedData['login'], 'Asia/Kolkata');

        // Calculate total break seconds
        $totalBreakSeconds = (int) DB::table('staff_breaks')
            ->where('attendance_id', $attendance->id)
            ->whereNotNull('break_end')
            ->sum(DB::raw('TIMESTAMPDIFF(SECOND, break_start, break_end)'));

        // Check for active break
        $activeBreak = DB::table('staff_breaks')
            ->where('attendance_id', $attendance->id)
            ->whereNotNull('break_start')
            ->whereNull('break_end')
            ->first();

        if ($activeBreak) {
            $breakSeconds = $now->diffInSeconds(Carbon::parse($activeBreak->break_start, 'Asia/Kolkata'));
            $totalBreakSeconds += $breakSeconds;
            DB::table('staff_breaks')
                ->where('id', $activeBreak->id)
                ->update([
                    'break_end' => $now,
                    'updated_at' => $now
                ]);
        }

        // Handle logout and calculate total work seconds
        if (!empty($validatedData['logout'])) {
            $attendance->logout = Carbon::parse($validatedData['logout'], 'Asia/Kolkata');
            $sessionSeconds = $attendance->logout->diffInSeconds($attendance->created_at);
            $totalWorkSeconds = max(0, $sessionSeconds - $totalBreakSeconds);
            $attendance->last_timer_start = null;
        } else {
            $attendance->logout = null;
            $totalWorkSeconds = $attendance->total_work_seconds ?? 0;
            $isOnBreak = $this->isOnBreak($attendance->id);
            if (!$isOnBreak) {
                $sessionSeconds = $now->diffInSeconds($attendance->created_at);
                $totalWorkSeconds = max(0, $sessionSeconds - $totalBreakSeconds);
                $attendance->last_timer_start = $now;
            } else {
                $attendance->last_timer_start = null;
            }
        }

        $attendance->mode = $validatedData['mode'];
        $attendance->total_work_seconds = $totalWorkSeconds;
        $attendance->total_break_seconds = $totalBreakSeconds;
        $attendance->save();

        return response()->json([
            'id'         => $attendance->id,
            'created_at' => $attendance->created_at->toDateTimeString(),
            'logout'     => $attendance->logout ? $attendance->logout->toDateTimeString() : '',
            'mode'       => $attendance->mode,
            'total_work_seconds' => $attendance->total_work_seconds,
            'total_break_seconds' => $attendance->total_break_seconds,
        ]);
    }

    public function todaysReport(Request $request)
    {
        $user = Auth::user();

        // Restrict access to authorized users (role_id 1, 2, 3, 7)
        if (!in_array($user->role_id, [1, 2, 3, 7])) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        // Default to today's date if not provided
        $startDate = $request->input('start_date', Carbon::today('Asia/Kolkata')->toDateString());
        $endDate = $request->input('end_date', Carbon::today('Asia/Kolkata')->toDateString());

        // Initialize employee query
        $employeeQuery = Employee::whereNull('resignation')
            ->where('status', 1)
            ->whereNotNull('first_name')
            ->whereNotNull('last_name')
            ->whereNotNull('employee_id');

        // For team leads (role_id = 3), restrict to their department
        if ($user->role_id === 3) {
            $departmentId = optional($user->employee)->department_id;
            if ($departmentId) {
                $employeeQuery->where('department_id', $departmentId);
            } else {
                $employeeQuery->whereRaw('1 = 0'); // No results if no department
            }
        }

        $employees = $employeeQuery->get();

        // Retrieve all attendance records within the date range
        $attendanceQuery = StaffAttendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->with('user.employee'); // Load user and employee for name display
        if ($request->filled('staff_id')) {
            $attendanceQuery->where('user_id', $request->staff_id);
        }
        $attendances = $attendanceQuery->get()->map(function ($attendance) {
            // Format login and logout times in AM/PM
            $attendance->formatted_login_time = Carbon::parse($attendance->created_at)->format('h:i:s A');
            $attendance->formatted_logout_time = $attendance->logout ? Carbon::parse($attendance->logout)->format('h:i:s A') : 'Still Working';

            // Calculate work hours
            $totalWorkSeconds = $attendance->total_work_seconds ?? 0;
            if (!$attendance->logout && $attendance->last_timer_start && !$this->isOnBreak($attendance->id)) {
                $totalWorkSeconds += Carbon::now('Asia/Kolkata')->diffInSeconds(Carbon::parse($attendance->last_timer_start));
            }
            $attendance->formatted_work_hours = $totalWorkSeconds > 0 ? $this->formatHours($totalWorkSeconds) : '-';
            if (!$attendance->logout && $totalWorkSeconds > 0) {
                $attendance->formatted_work_hours .= ' (Still Working)';
            }

            // Add date for grouping
            $attendance->attendance_date_formatted = Carbon::parse($attendance->attendance_date)->format('d M Y');

            return $attendance;
        })->groupBy('user_id'); // Group by user_id to allow multiple records

        // Retrieve task assignments within the date range
        $assignedTasks = TaskAssigned::whereBetween('date', [$startDate, $endDate])
            ->with(['task.project', 'task.service'])
            ->get()
            ->groupBy(['staff_id', 'date']); // Group by staff_id and date

        return view('attendance.todays_report', compact('employees', 'attendances', 'startDate', 'endDate', 'assignedTasks'));
    }

    /**
     * Approve attendance record.
     */
    public function approve(Request $request)
    {
        $validatedData = $request->validate([
            'attendance_id' => 'required|exists:staff_attendance,id'
        ]);

        $attendance = StaffAttendance::findOrFail($validatedData['attendance_id']);

        // Update approval fields
        $attendance->approval_status = 'approved';
        $attendance->approved_by     = Auth::id();
        $attendance->approved_at     = now();
        $attendance->save();

        return response()->json([
            'id'              => $attendance->id,
            'approval_status' => $attendance->approval_status,
            'approved_by'     => $attendance->approved_by,
            'approved_at'     => $attendance->approved_at->toDateTimeString(),
        ]);
    }
    public function workFromOffice(Request $request)
    {
        // Default filters: today's date if not provided.
        $startDate = $request->input('start_date', \Carbon\Carbon::today('Asia/Kolkata')->toDateString());
        $endDate   = $request->input('end_date', \Carbon\Carbon::today('Asia/Kolkata')->toDateString());
        $staffId   = $request->input('staff_id', null);

        // According to your instruction, for the "Work From Office" page,
        // we show employees whose attendance mode is "Work from Home".
        $query = DB::table('staff_attendance')
            ->join('employees', 'staff_attendance.user_id', '=', 'employees.id')
            ->select(
                'employees.*',
                'staff_attendance.mode',
                'staff_attendance.attendance_date',
                'staff_attendance.created_at as login_time',
                'staff_attendance.logout as logout_time'
            )
            ->whereBetween('staff_attendance.attendance_date', [$startDate, $endDate])
            ->where('staff_attendance.mode', 'Work from office');

        if ($staffId) {
            $query->where('employees.id', $staffId);
        }

        $records = $query->get();
        $employees = \App\Models\Employee::all();

        return view('attendance.work_from_office', compact('records', 'startDate', 'endDate', 'employees', 'staffId'));
    }
    public function workFromHome(Request $request)
    {
        $startDate = $request->input('start_date', \Carbon\Carbon::today('Asia/Kolkata')->toDateString());
        $endDate   = $request->input('end_date', \Carbon\Carbon::today('Asia/Kolkata')->toDateString());
        $staffId   = $request->input('staff_id', null);

        // For the "Work From Home" page, show employees whose attendance mode is "Work from office".
        $query = DB::table('staff_attendance')
            ->join('employees', 'staff_attendance.user_id', '=', 'employees.id')
            ->select(
                'employees.*',
                'staff_attendance.mode',
                'staff_attendance.attendance_date',
                'staff_attendance.created_at as login_time',
                'staff_attendance.logout as logout_time'
            )
            ->whereBetween('staff_attendance.attendance_date', [$startDate, $endDate])
            ->where('staff_attendance.mode', 'Work from home');

        if ($staffId) {
            $query->where('employees.id', $staffId);
        }

        $records = $query->get();
        $employees = \App\Models\Employee::all();

        return view('attendance.work_from_home', compact('records', 'startDate', 'endDate', 'employees', 'staffId'));
    }
    
    public function leaveReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today('Asia/Kolkata')->toDateString());
        $endDate = $request->input('end_date', Carbon::today('Asia/Kolkata')->toDateString());
        $staffId = $request->input('staff_id', null);
        $departmentId = $request->input('department_id', null);
        $roleId = $request->input('role_id', null);

        // Ensure valid date range
        $start = Carbon::parse($startDate, 'Asia/Kolkata')->startOfDay();
        $end = Carbon::parse($endDate, 'Asia/Kolkata')->endOfDay();
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
            $startDate = $start->toDateString();
            $endDate = $end->toDateString();
        }

        // Generate dynamic export title
        $exportTitle = 'Detailed Attendance Report';
        if ($departmentId) {
            $department = Department::where('id', $departmentId)->select('name')->first();
            $exportTitle .= ' - ' . ($department ? $department->name : 'Unknown Department');
        } else {
            $exportTitle .= ' - All Departments';
        }
        $exportTitle .= ' - ' . Carbon::parse($startDate)->format('d M Y') . ' to ' . Carbon::parse($endDate)->format('d M Y');

        // Get all active employees with non-null user_id, excluding IDs 18 and 36
        $query = Employee::whereNull('resignation')
            ->where('employees.status', 1)
            ->whereNotNull('employees.user_id')
            ->whereNotIn('employees.id', [18, 36]) // Exclude employee IDs 18 and 36
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('roles', 'employees.role_id', '=', 'roles.id')
            ->select(
                'employees.id',
                'employees.user_id',
                'employees.first_name',
                'employees.middle_name',
                'employees.last_name',
                'employees.employee_id',
                'employees.company_email',
                'employees.saturday_exempt',
                'employees.created_at',
                'employees.role_id', // Added to access role_id for holiday checks
                'departments.name as department',
                'roles.name as role'
            );

        if ($staffId) {
            $query->where('employees.id', $staffId);
        }
        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }
        if ($roleId) {
            $query->where('employees.role_id', $roleId);
        }

        $employees = $query->get();

        // Log employees with null user_id for debugging
        $invalidEmployees = Employee::whereNull('resignation')
            ->where('employees.status', 1)
            ->whereNull('employees.user_id')
            ->whereNotIn('employees.id', [18, 36]) // Apply exclusion here as well
            ->get();
        if ($invalidEmployees->isNotEmpty()) {
            Log::warning('Employees with null user_id found', [
                'invalid_employees' => $invalidEmployees->pluck('employee_id')->toArray(),
                'count' => $invalidEmployees->count()
            ]);
        }

        // Get departments and roles for dropdowns (only those with active employees, excluding IDs 18 and 36)
        $departments = Department::whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('employees')
                      ->whereNull('employees.resignation')
                      ->where('employees.status', 1)
                      ->whereNotNull('employees.user_id')
                      ->whereNotIn('employees.id', [18, 36]) // Exclude employee IDs 18 and 36
                      ->whereColumn('employees.department_id', 'departments.id');
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $roles = Role::whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('employees')
                      ->whereNull('employees.resignation')
                      ->where('employees.status', 1)
                      ->whereNotNull('employees.user_id')
                      ->whereNotIn('employees.id', [18, 36]) // Exclude employee IDs 18 and 36
                      ->whereColumn('employees.role_id', 'roles.id');
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get public holidays within the date range
        $holidays = PublicHoliday::where('year', '>=', $start->year)
            ->where('year', '<=', $end->year)
            ->get()
            ->flatMap(function ($holiday) {
                return collect($holiday->dates)->map(function ($date) use ($holiday) {
                    return [
                        'date' => Carbon::parse($date)->toDateString(),
                        'role_ids' => $holiday->role_ids,
                        'name' => $holiday->name,
                    ];
                });
            })
            ->filter(function ($holiday) use ($startDate, $endDate) {
                return Carbon::parse($holiday['date'])->between($startDate, $endDate);
            })
            ->groupBy('date');

        // Log holidays for debugging
        Log::debug('Public holidays in date range', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'holidays' => $holidays->toArray(),
        ]);

        // Get attendance records for the date range, with strict validation
        $attendanceQuery = StaffAttendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->whereNotNull('attendance_date')
            ->whereRaw('user_id REGEXP "^[0-9]+$"')
            ->select('user_id', 'attendance_date', 'mode');

        // Log all attendance records before grouping
        $rawAttendances = $attendanceQuery->get();
        Log::debug('Raw attendance records', [
            'records' => $rawAttendances->toArray(),
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        // Group records manually
        $attendances = [];
        foreach ($rawAttendances as $record) {
            $userId = (string) $record->user_id;
            $date = $record->attendance_date instanceof Carbon ? $record->attendance_date->toDateString() : (string) $record->attendance_date;

            if (empty($userId) || empty($date)) {
                Log::warning('Skipping invalid attendance record', [
                    'record' => $record->toArray(),
                    'user_id' => $userId,
                    'attendance_date' => $date
                ]);
                continue;
            }

            if (!isset($attendances[$userId])) {
                $attendances[$userId] = [];
            }
            if (!isset($attendances[$userId][$date])) {
                $attendances[$userId][$date] = new \Illuminate\Support\Collection();
            }
            $attendances[$userId][$date]->push($record);
        }

        // Log any invalid attendance records
        $invalidRecords = StaffAttendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNull('user_id')
                      ->orWhereNull('attendance_date')
                      ->orWhereRaw('user_id NOT REGEXP "^[0-9]+$"');
            })
            ->get();
        if ($invalidRecords->isNotEmpty()) {
            Log::warning('Invalid staff_attendance records found', [
                'invalid_records' => $invalidRecords->toArray(),
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
        }

        // Calculate leave, WFH, and Half Day counts
        $records = $employees->map(function ($employee) use ($start, $end, $attendances, $holidays) {
            $userId = (string) $employee->user_id;
            $leaveCount = 0;
            $wfhCount = 0;
            $halfDayCount = 0;

            // Use employee's created_at as the start of their date range
            $employeeStart = Carbon::parse($employee->created_at, 'Asia/Kolkata')->startOfDay();
            $rangeStart = $employeeStart->gt($start) ? $employeeStart : $start;
            $rangeEnd = $end;

            // Generate date range for this employee
            $dateRange = [];
            $currentDate = $rangeStart->copy();
            while ($currentDate->lte($rangeEnd)) {
                $dateRange[] = $currentDate->toDateString();
                $currentDate->addDay();
            }

            foreach ($dateRange as $date) {
                $carbonDate = Carbon::parse($date);

                // Skip Sundays for leave count
                if ($carbonDate->isSunday()) {
                    Log::debug('Skipping Sunday for leave count', [
                        'user_id' => $userId,
                        'date' => $date,
                    ]);
                    continue;
                }

                // Skip Saturdays for saturday_exempt employees
                $isSaturdayExempt = $employee->saturday_exempt == 1;
                if ($isSaturdayExempt && $carbonDate->isSaturday()) {
                    Log::debug('Skipping Saturday for exempt employee', [
                        'user_id' => $userId,
                        'date' => $date,
                        'saturday_exempt' => $isSaturdayExempt,
                    ]);
                    continue;
                }

                // Skip public holidays for this employee's role
                $isHoliday = false;
                if (isset($holidays[$date])) {
                    foreach ($holidays[$date] as $holiday) {
                        $roleIds = is_array($holiday['role_ids']) ? $holiday['role_ids'] : json_decode($holiday['role_ids'], true) ?? [];
                        if (in_array($employee->role_id, $roleIds)) {
                            $isHoliday = true;
                            Log::debug('Skipping public holiday for employee', [
                                'user_id' => $userId,
                                'date' => $date,
                                'holiday_name' => $holiday['name'],
                                'employee_role_id' => $employee->role_id,
                                'holiday_role_ids' => $roleIds,
                            ]);
                            break;
                        }
                    }
                }
                if ($isHoliday) {
                    continue;
                }

                // Safely check if attendance exists
                $attendance = null;
                if (isset($attendances[$userId]) && isset($attendances[$userId][$date])) {
                    $attendance = $attendances[$userId][$date]->first();
                }

                // Log for debugging
                Log::debug('Processing attendance', [
                    'user_id' => $userId,
                    'date' => $date,
                    'is_sunday' => $carbonDate->isSunday(),
                    'is_saturday_exempt' => $isSaturdayExempt,
                    'is_holiday' => $isHoliday,
                    'attendance_exists' => !is_null($attendance),
                    'mode' => $attendance ? $attendance->mode : 'None',
                ]);

                // Count as leave if no attendance or mode is 'Leave'
                if (!$attendance || strcasecmp($attendance->mode, 'Leave') === 0) {
                    $leaveCount++;
                }

                // Count as WFH if mode is 'Work from home'
                if ($attendance && strcasecmp($attendance->mode, 'Work from home') === 0) {
                    $wfhCount++;
                }

                // Count as Half Day if mode is 'Half Day'
                if ($attendance && strcasecmp($attendance->mode, 'Half Day') === 0) {
                    $halfDayCount++;
                }
            }

            $record = (object) [
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name,
                'employee_id' => $employee->employee_id,
                'email' => $employee->company_email,
                'department' => $employee->department,
                'role' => $employee->role,
                'leave_count' => $leaveCount,
                'wfh_count' => $wfhCount,
                'half_day_count' => $halfDayCount,
            ];

            // Log each employee's record
            Log::debug('Employee record', [
                'user_id' => $userId,
                'employee_id' => $employee->employee_id,
                'saturday_exempt' => $employee->saturday_exempt,
                'created_at' => $employee->created_at->toDateString(),
                'leave_count' => $leaveCount,
                'wfh_count' => $wfhCount,
                'half_day_count' => $halfDayCount,
            ]);

            return $record;
        });

        // Log final records for debugging
        Log::debug('Leave report records', [
            'records' => $records->toArray(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'staff_id' => $staffId,
            'department_id' => $departmentId,
            'role_id' => $roleId,
        ]);

        // Get active employees for staff dropdown, excluding IDs 18 and 36
        $activeEmployees = Employee::whereNull('resignation')
            ->where('status', 1)
            ->whereNotNull('user_id')
            ->whereNotIn('id', [18, 36]) // Exclude employee IDs 18 and 36
            ->select('id', 'first_name', 'middle_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        return view('attendance.leave_report', compact('records', 'startDate', 'endDate', 'activeEmployees', 'staffId', 'departmentId', 'roleId', 'departments', 'roles', 'exportTitle'));
    }
    
    public function getEmployeeAttendanceDetails(Request $request, $employeeId)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Get employee information
        $employee = Employee::with(['department', 'role'])
            ->where('id', $employeeId)
            ->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        // Get employee's work schedule and format to AM/PM
        $workStartTime = $employee->work_start_time ?? '09:00:00';
        $workEndTime = $employee->work_end_time ?? '18:00:00';
        
        // Format work schedule times to AM/PM
        $formattedStartTime = $this->formatTimeToAMPM($workStartTime);
        $formattedEndTime = $this->formatTimeToAMPM($workEndTime);
        $formattedSchedule = $formattedStartTime . ' - ' . $formattedEndTime;
        
        $standardWorkHours = 8; // Standard work hours per day
        
        // Get detailed attendance records
        $attendanceRecords = DB::table('staff_attendance')
            ->where('user_id', $employee->user_id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'desc')
            ->get();
        
        // Get leave data from attendance records instead of staff_leaves table
        $leaveData = $this->getLeaveDataFromAttendance($attendanceRecords, $employee, $startDate, $endDate);
        
        // Get public holidays in the date range
        $holidays = PublicHoliday::where('year', '>=', Carbon::parse($startDate)->year)
            ->where('year', '<=', Carbon::parse($endDate)->year)
            ->get()
            ->flatMap(function ($holiday) {
                return collect($holiday->dates)->map(function ($date) use ($holiday) {
                    return [
                        'date' => Carbon::parse($date)->toDateString(),
                        'role_ids' => $holiday->role_ids,
                        'name' => $holiday->name,
                    ];
                });
            })
            ->filter(function ($holiday) use ($startDate, $endDate) {
                return Carbon::parse($holiday['date'])->between($startDate, $endDate);
            })
            ->groupBy('date');
        
        // Process each attendance record
        $detailedRecords = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        
        while ($currentDate <= $endDateCarbon) {
            $dateString = $currentDate->toDateString();
            
            // Skip Sundays
            if ($currentDate->isSunday()) {
                $currentDate->addDay();
                continue;
            }
            
            // Skip Saturdays for exempt employees
            if ($employee->saturday_exempt == 1 && $currentDate->isSaturday()) {
                $currentDate->addDay();
                continue;
            }
            
            // Check if it's a holiday for this employee's role
            $isHoliday = false;
            if (isset($holidays[$dateString])) {
                foreach ($holidays[$dateString] as $holiday) {
                    $roleIds = is_array($holiday['role_ids']) ? $holiday['role_ids'] : json_decode($holiday['role_ids'], true) ?? [];
                    if (in_array($employee->role_id, $roleIds)) {
                        $isHoliday = true;
                        break;
                    }
                }
            }
            
            if ($isHoliday) {
                $currentDate->addDay();
                continue;
            }
            
            // Find attendance record for this date
            $attendance = $attendanceRecords->where('attendance_date', $dateString)->first();
            
            if ($attendance) {
                // Calculate work timing indicators
                $loginTime = Carbon::parse($attendance->created_at);
                $logoutTime = $attendance->logout ? Carbon::parse($attendance->logout) : null;
                
                $expectedStartTime = Carbon::parse($dateString . ' ' . $workStartTime);
                $expectedEndTime = Carbon::parse($dateString . ' ' . $workEndTime);
                
                // Calculate late entry (more than 15 minutes late) - KEEP 15 MIN BUFFER
                $lateEntry = $loginTime->gt($expectedStartTime->copy()->addMinutes(15));
                $lateMinutes = $lateEntry ? $loginTime->diffInMinutes($expectedStartTime) : 0;
                
                // Calculate early exit (more than 15 minutes early) - KEEP 15 MIN BUFFER
                $earlyExit = false;
                $earlyMinutes = 0;
                if ($logoutTime && $logoutTime->lt($expectedEndTime->copy()->subMinutes(15))) {
                    $earlyExit = true;
                    $earlyMinutes = $expectedEndTime->diffInMinutes($logoutTime);
                }
                
                // Calculate total work time and overtime
                $totalWorkSeconds = $attendance->total_work_seconds ?? 0;
                if (!$logoutTime && $attendance->last_timer_start) {
                    $totalWorkSeconds += Carbon::now('Asia/Kolkata')->diffInSeconds(Carbon::parse($attendance->last_timer_start));
                }
                
                $totalWorkHours = $totalWorkSeconds / 3600;
                $overtimeHours = max(0, $totalWorkHours - $standardWorkHours);
                
                // Get break details
                $breaks = DB::table('staff_breaks')
                    ->where('attendance_id', $attendance->id)
                    ->whereNotNull('break_end')
                    ->select('break_start', 'break_end')
                    ->get();
                
                $totalBreakMinutes = 0;
                foreach ($breaks as $break) {
                    $totalBreakMinutes += Carbon::parse($break->break_start)->diffInMinutes(Carbon::parse($break->break_end));
                }
                
                $detailedRecords[] = [
                    'date' => $currentDate->format('d M Y'),
                    'day' => $currentDate->format('l'),
                    'login_time' => $loginTime->format('h:i A'),
                    'logout_time' => $logoutTime ? $logoutTime->format('h:i A') : 'Still Working',
                    'mode' => $attendance->mode ?? 'Present',
                    'total_work_hours' => $this->formatEmployeeWorkHours($totalWorkSeconds),
                    'total_break_time' => $this->formatEmployeeBreakTime($totalBreakMinutes * 60),
                    'breaks_count' => count($breaks),
                    'late_entry' => $lateEntry,
                    'late_minutes' => $lateMinutes,
                    'early_exit' => $earlyExit,
                    'early_minutes' => $earlyMinutes,
                    'overtime_hours' => $overtimeHours > 0.1 ? number_format($overtimeHours, 2) : 0,
                    'status_class' => $this->getStatusClass($attendance->mode ?? 'Present', $lateEntry, $earlyExit, $overtimeHours),
                    'raw_date' => $dateString
                ];
            } else {
                // No attendance record - marked as absent/leave
                $detailedRecords[] = [
                    'date' => $currentDate->format('d M Y'),
                    'day' => $currentDate->format('l'),
                    'login_time' => '-',
                    'logout_time' => '-',
                    'mode' => 'Absent',
                    'total_work_hours' => '-',
                    'total_break_time' => '-',
                    'breaks_count' => 0,
                    'late_entry' => false,
                    'late_minutes' => 0,
                    'early_exit' => false,
                    'early_minutes' => 0,
                    'overtime_hours' => 0,
                    'status_class' => 'status-absent',
                    'raw_date' => $dateString
                ];
            }
            
            $currentDate->addDay();
        }
        
        // Calculate total break count
        $totalBreakCount = array_sum(array_column($detailedRecords, 'breaks_count'));
        
        return response()->json([
            'employee' => [
                'name' => $employee->first_name . ' ' . ($employee->middle_name ? $employee->middle_name . ' ' : '') . $employee->last_name,
                'employee_id' => $employee->employee_id,
                'department' => $employee->department->name ?? 'N/A',
                'role' => $employee->role->name ?? 'N/A',
                'work_schedule' => $formattedSchedule
            ],
            'records' => $detailedRecords,
            'leave_data' => $leaveData,
            'summary' => [
                'total_days' => count($detailedRecords),
                'present_days' => count(array_filter($detailedRecords, function($record) {
                    return $record['mode'] !== 'Absent';
                })),
                'late_entries' => count(array_filter($detailedRecords, function($record) {
                    return $record['late_entry'];
                })),
                'early_exits' => count(array_filter($detailedRecords, function($record) {
                    return $record['early_exit'];
                })),
                'total_overtime_hours' => array_sum(array_column($detailedRecords, 'overtime_hours')),
                'total_break_count' => $totalBreakCount,
                'leave_count' => $leaveData['leave_count'],
                'wfh_count' => $leaveData['wfh_count'],
                'half_day_count' => $leaveData['half_day_count'],
            ]
        ]);
    }
    
    // Get leave data from attendance records instead of staff_leaves table
    private function getLeaveDataFromAttendance($attendanceRecords, $employee, $startDate, $endDate)
    {
        $leaveCount = 0;
        $wfhCount = 0;
        $halfDayCount = 0;
        $leaveDates = [];
        $wfhDates = [];
        $halfDayDates = [];
        
        // Process attendance records for leave modes
        foreach ($attendanceRecords as $record) {
            $formattedDate = Carbon::parse($record->attendance_date)->format('M d, Y');
            
            switch (strtolower($record->mode)) {
                case 'leave':
                    $leaveCount++;
                    $leaveDates[] = $formattedDate;
                    break;
                case 'work from home':
                case 'wfh':
                    $wfhCount++;
                    $wfhDates[] = $formattedDate;
                    break;
                case 'half day':
                    $halfDayCount++;
                    $halfDayDates[] = $formattedDate;
                    break;
            }
        }
        
        // Also check for absent days (days with no attendance record)
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        
        // Get public holidays to exclude them from absent days
        $holidays = PublicHoliday::where('year', '>=', Carbon::parse($startDate)->year)
            ->where('year', '<=', Carbon::parse($endDate)->year)
            ->get()
            ->flatMap(function ($holiday) {
                return collect($holiday->dates)->map(function ($date) use ($holiday) {
                    return [
                        'date' => Carbon::parse($date)->toDateString(),
                        'role_ids' => $holiday->role_ids,
                        'name' => $holiday->name,
                    ];
                });
            })
            ->filter(function ($holiday) use ($startDate, $endDate) {
                return Carbon::parse($holiday['date'])->between($startDate, $endDate);
            })
            ->groupBy('date');
        
        while ($currentDate <= $endDateCarbon) {
            $dateString = $currentDate->toDateString();
            $formattedDate = $currentDate->format('M d, Y');
            
            // Skip weekends and holidays
            if ($currentDate->isSunday() || 
                ($employee->saturday_exempt == 1 && $currentDate->isSaturday())) {
                $currentDate->addDay();
                continue;
            }
            
            // Check if it's a holiday for this employee's role
            $isHoliday = false;
            if (isset($holidays[$dateString])) {
                foreach ($holidays[$dateString] as $holiday) {
                    $roleIds = is_array($holiday['role_ids']) ? $holiday['role_ids'] : json_decode($holiday['role_ids'], true) ?? [];
                    if (in_array($employee->role_id, $roleIds)) {
                        $isHoliday = true;
                        break;
                    }
                }
            }
            
            if ($isHoliday) {
                $currentDate->addDay();
                continue;
            }
            
            // Check if there's no attendance record for this working day
            $hasAttendance = $attendanceRecords->where('attendance_date', $dateString)->first();
            
            if (!$hasAttendance) {
                // Consider it as a leave day
                $leaveCount++;
                $leaveDates[] = $formattedDate . ' (Absent)';
            }
            
            $currentDate->addDay();
        }
        
        return [
            'leave_count' => $leaveCount,
            'wfh_count' => $wfhCount,
            'half_day_count' => $halfDayCount,
            'leave_dates' => $leaveDates,
            'wfh_dates' => $wfhDates,
            'half_day_dates' => $halfDayDates,
        ];
    }
    
    // Helper method to format time to AM/PM
    private function formatTimeToAMPM($time) 
    {
        if (!$time || $time === '00:00:00') {
            return '';
        }
        
        try {
            return \Carbon\Carbon::createFromFormat('H:i:s', $time)->format('h:i A');
        } catch (\Exception $e) {
            return $time;
        }
    }
    
    // Get status class based on attendance mode and timing
    private function getStatusClass($mode, $lateEntry, $earlyExit, $overtimeHours)
    {
        switch (strtolower($mode)) {
            case 'leave':
                return 'status-leave';
            case 'half day':
                return 'status-half-day';
            case 'work from home':
                return 'status-wfh';
            case 'absent':
                return 'status-absent';
            default:
                if ($lateEntry && $earlyExit) {
                    return 'status-late-early';
                } elseif ($lateEntry) {
                    return 'status-late';
                } elseif ($earlyExit) {
                    return 'status-early';
                } elseif ($overtimeHours > 0.1) {
                    return 'status-overtime';
                }
                return 'status-present';
        }
    }
    
    // UNIQUE METHOD NAMES for employee attendance details
    private function formatEmployeeWorkHours($totalSeconds)
    {
        if ($totalSeconds === 0) {
            return '0h 0m';
        }
        
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        
        return $hours . 'h ' . $minutes . 'm';
    }
    
    private function formatEmployeeBreakTime($seconds)
    {
        if ($seconds === 0) {
            return '0m';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $result = '';
        if ($hours > 0) {
            $result .= $hours . 'h ';
        }
        if ($minutes > 0) {
            $result .= $minutes . 'm';
        }
        
        return trim($result);
    }

    // RENAMED: formatHours to formatAttendanceHours for other methods
    private function formatAttendanceHours($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf('%d hour%s %d minute%s', $hours, $hours != 1 ? 's' : '', $minutes, $minutes != 1 ? 's' : '');
    }

    // RENAMED: formatDuration to formatBreakDuration for other methods
    private function formatBreakDuration($seconds)
    {
        $minutes = floor($seconds / 60);
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return sprintf('%d hour%s %d minute%s', $hours, $hours != 1 ? 's' : '', $remainingMinutes, $remainingMinutes != 1 ? 's' : '');
        }
        return sprintf('%d minute%s', $minutes, $minutes != 1 ? 's' : '');
    }
}
