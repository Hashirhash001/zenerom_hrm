<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TaskAssigned;
use App\Models\Employee;
class TeamTaskController extends Controller
{
    /**
     * Display a report of today's team tasks for the Tech Head's department.
     */
    public function report(Request $request)
    {
        // Get the logged-in user and their department id.
        $user = Auth::user();
        $departmentId = optional($user->employee)->department_id;

        // Set default dates (today) if not provided.
        $startDate = $request->input('start_date', Carbon::today('Asia/Kolkata')->toDateString());
        $endDate   = $request->input('end_date', Carbon::today('Asia/Kolkata')->toDateString());
        $staffId   = $request->input('staff_id', '');

        // Build the query: get TaskAssigned records for today (or in date range)
        // where the assigned staff belongs to the current department.
        $query = TaskAssigned::whereBetween('date', [$startDate, $endDate])
            ->whereIn('staff_id', function($q) use ($departmentId) {
                $q->select('id')
                  ->from('employees')
                  ->where('department_id', $departmentId);
            })
            ->with([
                'task.project',   // Task's project details.
                'task.service',   // Task's service details.
                'staff',          // The assigned staff.
                'documents',      // Any uploaded documents.
                'comments.user'   // Comments along with commenter details.
            ])
            ->orderBy('date', 'desc');

        // If a specific staff filter is set, apply it.
        if (!empty($staffId)) {
            $query->where('staff_id', $staffId);
        }

        $assignments = $query->get();

        // For the staff filter dropdown, retrieve only employees from the logged-in user's department.
        $staffs = Employee::where('department_id', $departmentId)->get();

        return view('team_tasks.report', compact('assignments', 'startDate', 'endDate', 'staffId', 'staffs'));
    }
}
