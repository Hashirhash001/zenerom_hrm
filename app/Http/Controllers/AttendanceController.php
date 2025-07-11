<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Employee;

use App\Models\TaskAssigned;

use Illuminate\Http\Request;
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
        if (in_array($user->role_id, [4, 6]) || !in_array($user->role_id, [1, 2, 3, 7, 9])) {
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
            ->join('employees', 'staff_attendance.user_id', '=', 'employees.id')
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
        if (in_array($user->role_id, [4, 6]) || !in_array($user->role_id, [1, 2, 3, 7, 9])) {
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
            ->join('employees', 'staff_attendance.user_id', '=', 'employees.id')
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
        $employee = Employee::find($userId);
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

                    $requiredWorkSeconds = $workEnd->diffInSeconds($workStart) - 300; // 5-minute buffer
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

        // Parse the login time; we assume login is stored in created_at.
        $attendance->created_at = \Carbon\Carbon::parse($validatedData['login']);

        // Check if logout is provided; if not, leave it as null.
        if (!empty($validatedData['logout'])) {
            $attendance->logout = \Carbon\Carbon::parse($validatedData['logout']);
        } else {
            $attendance->logout = null;
        }

        $attendance->mode = $validatedData['mode'];
        $attendance->save();

        return response()->json([
            'id'         => $attendance->id,
            'created_at' => $attendance->created_at->toDateTimeString(),
            'logout'     => $attendance->logout ? $attendance->logout->toDateTimeString() : '',
            'mode'       => $attendance->mode,
        ]);
    }

    public function todaysReport(Request $request)
    {
        // Default to today's date if not provided
        $startDate = $request->input('start_date', Carbon::today('Asia/Kolkata')->toDateString());
        $endDate = $request->input('end_date', Carbon::today('Asia/Kolkata')->toDateString());

        // Retrieve all active employees
        $employees = Employee::whereNull('resignation')
            ->where('status', 1)
            ->get();

        // Retrieve attendance records within the date range
        $attendanceQuery = StaffAttendance::whereBetween('attendance_date', [$startDate, $endDate]);
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

            return $attendance;
        })->keyBy('user_id');

        // Retrieve task assignments with related task, project, and service
        $assignedTasks = TaskAssigned::whereBetween('date', [$startDate, $endDate])
            ->with(['task.project', 'task.service'])
            ->get()
            ->groupBy('staff_id');

        $today = $startDate; // For header display

        return view('attendance.todays_report', compact('employees', 'attendances', 'today', 'assignedTasks'));
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
        $startDate = $request->input('start_date', \Carbon\Carbon::today('Asia/Kolkata')->toDateString());
        $endDate   = $request->input('end_date', \Carbon\Carbon::today('Asia/Kolkata')->toDateString());
        $staffId   = $request->input('staff_id', null);

        // Retrieve employees who do NOT have an attendance record in the given date range.
        $query = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('roles', 'employees.role_id', '=', 'roles.id')
            ->select('employees.*', 'departments.name as department', 'roles.name as role')
            ->whereNotExists(function ($q) use ($startDate, $endDate) {
                $q->select(DB::raw(1))
                    ->from('staff_attendance')
                    ->whereRaw('staff_attendance.user_id = employees.id')
                    ->whereBetween('staff_attendance.attendance_date', [$startDate, $endDate]);
            });

        if ($staffId) {
            $query->where('employees.id', $staffId);
        }

        $records = $query->get();
        $employees = \App\Models\Employee::all();

        return view('attendance.leave_report', compact('records', 'startDate', 'endDate', 'employees', 'staffId'));
    }
}
