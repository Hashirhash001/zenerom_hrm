<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Carbon\Carbon;

class StaffTaskReportController extends Controller
{
    public function index(Request $request)
    {
        // Ensure that only users with roles 1 and 2 can access this report.
        $user = Auth::user();
        if (!in_array($user->role_id, [1, 2])) {
            abort(403, 'Unauthorized action.');
        }

        // Get filter parameters; default start and end date is today.
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate   = $request->input('end_date', Carbon::today()->toDateString());
        $staffId   = $request->input('staff_id', null);

        // Build the query that combines data from task_assigned, tasks, task_documents, and employees.
        // Assuming:
        // - task_assigned table has: task_id, staff_id, assigned_by, date, status.
        // - tasks table has: id, title, description, deadline.
        // - task_documents table has: task_id, document_name, description, created_at.
        // - employees table holds staff details (for both staff and assigned_by).
        $query = DB::table('task_assigned')
    ->join('tasks', 'task_assigned.task_id', '=', 'tasks.id')
    // Left join documents (if a task has multiple documents, rows will be duplicated; adjust as needed)
    ->leftJoin('task_documents', 'tasks.id', '=', 'task_documents.task_id')
    // Join staff details for the assigned staff.
    ->join('employees as staff', 'task_assigned.staff_id', '=', 'staff.id')
    // Left join for "assigned by" details using tasks.created_by instead of task_assigned.assigned_by.
    ->leftJoin('employees as assigned_by', 'tasks.created_by', '=', 'assigned_by.id')
    ->select(
        'task_assigned.date as assignment_date',
        'tasks.title as task_title',
        'tasks.description as task_description',
        'tasks.deadline as task_deadline',
        'task_assigned.status as assignment_status',
        'task_documents.document_name as document_name',
        'task_documents.description as document_description',
        'staff.id as staff_id',
        'staff.first_name as staff_first_name',
        'staff.middle_name as staff_middle_name',
        'staff.last_name as staff_last_name',
        'staff.employee_id as staff_employee_id',
        'assigned_by.first_name as assigned_by_first_name',
        'assigned_by.middle_name as assigned_by_middle_name',
        'assigned_by.last_name as assigned_by_last_name'
    )
    ->whereBetween('task_assigned.date', [$startDate, $endDate]);


        if ($staffId) {
            $query->where('task_assigned.staff_id', $staffId);
        }

        $reports = $query->orderBy('task_assigned.date', 'desc')->get();

        // Retrieve all staff for the filter dropdown.
        $staffs = Employee::all();

        return view('staff_task_report.index', compact('reports', 'startDate', 'endDate', 'staffs', 'staffId'));
    }
}
