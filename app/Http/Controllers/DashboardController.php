<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function __construct()
    {
        // This middleware closure is applied to every method in the controller.
        $this->middleware(function ($request, $next) {
            if (!session()->has('uid')) {
                // Redirect immediately if the session does not contain 'uid'
                return redirect()->route('login');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        if (session('rid') != 1) {
            return redirect()->route('login');
        } else {
            $today = Carbon::today('Asia/Kolkata')->toDateString();

            // Global counts
            $total_employees = DB::table('employees')->count();
            $total_projects  = DB::table('projects')->count();
            $total_services  = DB::table('services')->count();
            $total_clients   = DB::table('customers')->count();

            // Attendance counts for today
            $work_from_office = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->where('mode', 'Work from office')
                ->count();

            $work_from_home = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->where('mode', 'Work from Home')
                ->count();

            // Count distinct employees who have marked attendance today
            $attendance_total = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->pluck('user_id')
                ->unique()
                ->count();

            // Employees with no attendance record today (considered as leave)
            $leave_count = $total_employees - $attendance_total;

            return view('dashboard.index', [
                'uid'               => session('uid'),
                'uname'             => session('uname'),
                'total_employees'   => $total_employees,
                'total_projects'    => $total_projects,
                'total_services'    => $total_services,
                'total_clients'     => $total_clients,
                'work_from_office'  => $work_from_office,
                'work_from_home'    => $work_from_home,
                'leave_count'       => $leave_count,
            ]);
        }
    }

    // Tech Head Dashboard (role 2)
   public function techhead(Request $request)
{
    if (session('rid') != 2) {
        return redirect()->route('login');
    } else {
        $today = \Carbon\Carbon::today('Asia/Kolkata')->toDateString();

        $total_employees = DB::table('employees')->count();
        $total_projects  = DB::table('projects')->count();
        $total_services  = DB::table('services')->count();
        $total_clients   = DB::table('customers')->count();

        $work_from_office = DB::table('staff_attendance')
            ->where('attendance_date', $today)
            ->where('mode', 'Work from office')
            ->count();

        $work_from_home = DB::table('staff_attendance')
            ->where('attendance_date', $today)
            ->where('mode', 'Work from Home')
            ->count();

        $attendance_total = DB::table('staff_attendance')
            ->where('attendance_date', $today)
            ->pluck('user_id')
            ->unique()
            ->count();

        $leave_count = $total_employees - $attendance_total;

        // Get the Tech Head's department.
        $techhead = \Illuminate\Support\Facades\Auth::user();
        $departmentId = optional($techhead->employee)->department_id;

        // Count employees under the Tech Head's department.
        $staff_count_in_dept = DB::table('employees')
            ->where('department_id', $departmentId)
            ->count();

        // Count employees in the department who are present today.
        $dept_attendance_total = DB::table('staff_attendance')
            ->where('attendance_date', $today)
            ->whereIn('user_id', function($query) use ($departmentId) {
                $query->select('id')
                      ->from('employees')
                      ->where('department_id', $departmentId);
            })
            ->pluck('user_id')
            ->unique()
            ->count();

        // Today's tasks for logged in Tech Head.
        $userId = \Illuminate\Support\Facades\Auth::id();
        $my_tasks = DB::table('task_assigned')
            ->whereDate('date', $today)
            ->where('staff_id', $userId)
            ->get();
        $my_tasks_total = $my_tasks->pluck('task_id')->unique()->count();
        $my_tasks_completed = $my_tasks->where('status', 1)->pluck('task_id')->unique()->count();

        // Today's tasks for the entire department.
        $dept_tasks = DB::table('task_assigned')
            ->whereDate('date', $today)
            ->whereIn('staff_id', function($query) use ($departmentId) {
                $query->select('id')
                      ->from('employees')
                      ->where('department_id', $departmentId);
            })
            ->get();
        $dept_tasks_total = $dept_tasks->pluck('task_id')->unique()->count();
        $dept_tasks_completed = $dept_tasks->where('status', 1)->pluck('task_id')->unique()->count();

        // Count of projects that include at least one service from the user's department.
        $dept_project_count = DB::table('projects')
            ->join('project_services', 'projects.id', '=', 'project_services.project_id')
            ->join('services', 'services.id', '=', 'project_services.service_id')
            ->where('services.department_id', $departmentId)
            ->distinct()
            ->count('projects.id');

        // Get all active notifications for the logged-in user (assuming active means unread: read_at is null).
        $notifications = \App\Models\Notification::where('user_id', $techhead->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.techhead', [
            'uid'                   => session('uid'),
            'uname'                 => session('uname'),
            'total_employees'       => $total_employees,
            'attendance_total'      => $attendance_total,
            'total_projects'        => $total_projects,
            'total_services'        => $total_services,
            'total_clients'         => $total_clients,
            'work_from_office'      => $work_from_office,
            'work_from_home'        => $work_from_home,
            'leave_count'           => $leave_count,
            'staff_count_in_dept'   => $staff_count_in_dept,
            'dept_attendance_total' => $dept_attendance_total,
            'my_tasks_total'        => $my_tasks_total,
            'my_tasks_completed'    => $my_tasks_completed,
            'dept_tasks_total'      => $dept_tasks_total,
            'dept_tasks_completed'  => $dept_tasks_completed,
            'dept_project_count'    => $dept_project_count,
            'today'                 => $today,
            'notifications'         => $notifications,
        ]);
    }
}





    // Team Lead Dashboard (role 3)
    public function teamlead(Request $request)
    {
        if (session('rid') != 3) {
            return redirect()->route('login');
        } else {
            $user = Auth::user();
            $departmentId = optional($user->employee)->department_id;

            // Count staffs from the same department
            $department_staff_count = DB::table('employees')
                ->where('department_id', $departmentId)
                ->count();

            // Count attendance for staffs in the same department (for today)
            $today = Carbon::today('Asia/Kolkata')->toDateString();
            $department_attendance_count = DB::table('staff_attendance')
                ->join('employees', 'staff_attendance.user_id', '=', 'employees.id')
                ->where('employees.department_id', $departmentId)
                ->where('attendance_date', $today)
                ->distinct('staff_attendance.user_id')
                ->count('staff_attendance.user_id');

            return view('dashboard.teamlead', [
                'uid'                          => session('uid'),
                'uname'                        => session('uname'),
                'department_staff_count'       => $department_staff_count,
                'department_attendance_count'  => $department_attendance_count,
            ]);
        }
    }

    // Staff Dashboard (role 4) – for individual staff, you might not require these global counts.
    public function staff(Request $request)
    {
        if (session('rid') != 4) {
            return redirect()->route('login');
        } else {
            return view('dashboard.staff', [
                'uid'   => session('uid'),
                'uname' => session('uname')
            ]);
        }
    }

    // Project Manager Dashboard (role 5)
    public function projectmanager(Request $request)
    {
        if (session('rid') != 5) {
            return redirect()->route('login');
        } else {
            $today = Carbon::today('Asia/Kolkata')->toDateString();

            $total_employees = DB::table('employees')->count();
            $total_projects  = DB::table('projects')->count();
            $total_services  = DB::table('services')->count();
            $total_clients   = DB::table('customers')->count();

            $work_from_office = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->where('mode', 'Work from office')
                ->count();

            $work_from_home = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->where('mode', 'Work from Home')
                ->count();

            $attendance_total = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->pluck('user_id')
                ->unique()
                ->count();

            $leave_count = $total_employees - $attendance_total;

            return view('dashboard.projectmanager', [
                'uid'               => session('uid'),
                'uname'             => session('uname'),
                'total_employees'   => $total_employees,
                'total_projects'    => $total_projects,
                'total_services'    => $total_services,
                'total_clients'     => $total_clients,
                'work_from_office'  => $work_from_office,
                'work_from_home'    => $work_from_home,
                'leave_count'       => $leave_count,
            ]);
        }
    }

    // Interns Dashboard (role 6)
    public function interns(Request $request)
    {
        if (session('rid') != 6) {
            return redirect()->route('login');
        } else {
            return view('dashboard.interns', [
                'uid'   => session('uid'),
                'uname' => session('uname')
            ]);
        }
    }

    // HR Dashboard (role 7)
    public function hr(Request $request)
    {
        if (session('rid') != 7) {
            return redirect()->route('login');
        } else {
            $today = Carbon::today('Asia/Kolkata')->toDateString();

            $total_employees = DB::table('employees')->count();
            $total_projects  = DB::table('projects')->count();
            $total_services  = DB::table('services')->count();
            $total_clients   = DB::table('customers')->count();

            $work_from_office = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->where('mode', 'Work from office')
                ->count();

            $work_from_home = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->where('mode', 'Work from Home')
                ->count();

            $attendance_total = DB::table('staff_attendance')
                ->where('attendance_date', $today)
                ->pluck('user_id')
                ->unique()
                ->count();

            $leave_count = $total_employees - $attendance_total;

            return view('dashboard.hr', [
                'uid'               => session('uid'),
                'uname'             => session('uname'),
                'total_employees'   => $total_employees,
                'total_projects'    => $total_projects,
                'total_services'    => $total_services,
                'total_clients'     => $total_clients,
                'work_from_office'  => $work_from_office,
                'work_from_home'    => $work_from_home,
                'leave_count'       => $leave_count,
            ]);
        }
    }
    public function nocontent(Request $request)
    {
        // Since the check above runs for every method,
        // you can safely assume that session('uid') exists.
        return view('dashboard.nocontent', [
            'uid'   => session('uid'),
            'uname' => session('uname')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
