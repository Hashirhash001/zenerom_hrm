<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;
use App\Models\StaffAttendance;

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

        $start_date = $request->get('start_date', Carbon::today('Asia/Kolkata')->toDateString());
        $end_date = $request->get('end_date', Carbon::today('Asia/Kolkata')->toDateString());

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

        if (in_array($user->role_id, [1, 2, 7, 9])) {
            // No additional filtering
        } elseif ($user->role_id == 3) {
            $departmentId = optional($user->employee)->department_id;
            if ($departmentId) {
                $query->where('employees.department_id', $departmentId);
            }
        } elseif (in_array($user->role_id, [4, 6])) {
            $query->where('staff_attendance.user_id', $user->id);
        } else {
            $query->where('staff_attendance.user_id', $user->id);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        if (in_array($user->role_id, [2, 7])) {
            return view('attendance.index', compact('attendances', 'start_date', 'end_date'));
        }

        return view('attendance.staffindex', compact('attendances', 'start_date', 'end_date'));
    }

    public function fetchAttendances(Request $request)
    {
        $user = Auth::user();

        $start_date = $request->get('start_date', Carbon::today('Asia/Kolkata')->toDateString());
        $end_date = $request->get('end_date', Carbon::today('Asia/Kolkata')->toDateString());

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

        if (in_array($user->role_id, [1, 2, 7, 9])) {
            // No additional filtering
        } elseif ($user->role_id == 3) {
            $departmentId = optional($user->employee)->department_id;
            if ($departmentId) {
                $query->where('employees.department_id', $departmentId);
            }
        } elseif (in_array($user->role_id, [4, 6])) {
            $query->where('staff_attendance.user_id', $user->id);
        } else {
            $query->where('staff_attendance.user_id', $user->id);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get()->map(function ($attendance) {
            $attendance->breaks = DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->select('break_start', 'break_end')
                ->get();
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

        // Check for today's task assignments for this staff
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
                return response()->json([
                    'success' => false,
                    'message' => 'You have incomplete tasks that need updates.',
                    'incomplete_tasks' => $incompleteTasks
                ]);
            }
        }

        // Update total_work_seconds before setting logout time
        $attendance = DB::table('staff_attendance')
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($attendance && !$attendance->logout && $attendance->last_timer_start) {
            $totalWorkSeconds = $attendance->total_work_seconds + Carbon::now('Asia/Kolkata')->diffInSeconds(Carbon::parse($attendance->last_timer_start));
            DB::table('staff_attendance')
                ->where('user_id', $userId)
                ->whereDate('attendance_date', $today)
                ->update([
                    'total_work_seconds' => $totalWorkSeconds,
                    'logout' => $now->toDateTimeString(),
                    'last_timer_start' => null
                ]);
        } else {
            DB::table('staff_attendance')
                ->where('user_id', $userId)
                ->whereDate('attendance_date', $today)
                ->update([
                    'logout' => $now->toDateTimeString(),
                    'last_timer_start' => null
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-out successful.'
        ]);
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

            $totalWorkSeconds = $attendance->total_work_seconds + Carbon::now('Asia/Kolkata')->diffInSeconds(Carbon::parse($attendance->last_timer_start));

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
                'message' => 'Break started successfully.'
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

            DB::table('staff_breaks')
                ->where('id', $activeBreak->id)
                ->update([
                    'break_end' => $now,
                    'updated_at' => $now
                ]);

            DB::table('staff_attendance')
                ->where('id', $attendance->id)
                ->update([
                    'last_timer_start' => $now
                ]);

            return response()->json([
                'success' => true,
                'breakTime' => $now->toDateTimeString(),
                'message' => 'Break ended successfully.'
            ]);
        }
    }

    public function syncTimer(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today('Asia/Kolkata');
        $totalWorkSeconds = $request->input('total_work_seconds', 0);

        $attendance = DB::table('staff_attendance')
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($attendance && !$attendance->logout && !$this->isOnBreak($attendance->id)) {
            DB::table('staff_attendance')
                ->where('id', $attendance->id)
                ->update([
                    'total_work_seconds' => $totalWorkSeconds,
                    'last_timer_start' => Carbon::now('Asia/Kolkata')
                ]);
        }

        return response()->json(['success' => true]);
    }

    public function getAttendanceStatus()
    {
        $userId = Auth::id();
        $today = Carbon::today('Asia/Kolkata');
        $attendance = DB::table('staff_attendance')
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $today)
            ->first();

        $response = [
            'isCheckedIn' => false,
            'hasCheckedOut' => false,
            'isOnBreak' => false,
            'totalWorkSeconds' => 0,
            'breakSeconds' => 0
        ];

        if ($attendance) {
            $response['isCheckedIn'] = true;
            $response['hasCheckedOut'] = !is_null($attendance->logout);
            $totalWorkSeconds = $attendance->total_work_seconds;
            if ($attendance->last_timer_start && !$response['hasCheckedOut'] && !$this->isOnBreak($attendance->id)) {
                $totalWorkSeconds += Carbon::now('Asia/Kolkata')->diffInSeconds(Carbon::parse($attendance->last_timer_start));
            }
            $response['totalWorkSeconds'] = $totalWorkSeconds;
            $response['isOnBreak'] = $this->isOnBreak($attendance->id);
            $response['breakSeconds'] = DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNotNull('break_start')
                ->whereNotNull('break_end')
                ->sum(DB::raw('TIMESTAMPDIFF(SECOND, break_start, break_end)'));
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

    public function todaysReport(Request $request)
    {
        // Default to today's date if not provided.
        $startDate = $request->input('start_date', \Carbon\Carbon::today()->toDateString());
        $endDate   = $request->input('end_date', \Carbon\Carbon::today()->toDateString());

        // Retrieve all employees.
        $employees = \App\Models\Employee::whereNull('resignation')
            ->where('status', 1)
            ->get();

        // Retrieve today's attendance records within the date range.
        $attendanceQuery = \App\Models\StaffAttendance::whereBetween('attendance_date', [$startDate, $endDate]);
        if ($request->filled('staff_id')) {
            $attendanceQuery->where('user_id', $request->staff_id);
        }
        $attendances = $attendanceQuery->get()->keyBy('user_id');

        // Retrieve today's task assignments for all employees within the date range.
        // Eager load the related task with its project and service.
        $assignedTasks = \App\Models\TaskAssigned::whereBetween('date', [$startDate, $endDate])
            ->with(['task.project', 'task.service'])
            ->get()
            ->groupBy('staff_id');

        $today = $startDate; // For header display

        return view('attendance.todays_report', compact('employees', 'attendances', 'today', 'assignedTasks'));
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
