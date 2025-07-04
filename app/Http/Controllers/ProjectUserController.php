<?php

namespace App\Http\Controllers;

use App\Models\ProjectUser;
use App\Models\ProjectService;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectUserController extends Controller
{
    /**
     * Store a new staff assignment for a project service.
     */
  

    public function store(Request $request)
{
    $validated = $request->validate([
        'project_id'         => 'required|integer|exists:projects,id',
        'project_service_id' => 'required|integer|exists:project_services,id',
        'user_id'        => 'required|integer|exists:employees,id',
        'status'             => 'required|in:active,inactive',
    ]);

    $assignmentId = DB::table('project_users')->insertGetId([
        'project_id'         => $validated['project_id'],
        'project_service_id' => $validated['project_service_id'],
        'user_id'            => $validated['user_id'],
        'status'             => $validated['status'],
        'date_time'          => now(),
        'created_at'         => now(),
        'updated_at'         => now(),
    ]);

    // Retrieve the employee data (if needed for the response)
    $employee = \App\Models\Employee::find($validated['user_id']);
    // Manually attach pivot data for response purposes:
    $employee->pivot = (object)[
        'project_service_id' => $validated['project_service_id'],
        'user_id'            => $validated['user_id'],
        'status'             => $validated['status'],
        'date_time'          => now(),
        'created_at'         => now(),
        'updated_at'         => now(),
    ];

    return response()->json([
        'success'       => true,
        'message'       => 'Staff assigned successfully!',
        'assignment_id' => $assignmentId,
        'staff'         => $employee,
    ]);
}


    /**
     * Toggle the status of a staff assignment.
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'project_service_id' => 'required|integer|exists:project_services,id',
            'user_id'            => 'required|integer|exists:employees,id',
        ]);

        // Find the record in project_users table
        $assignment = DB::table('project_users')
            ->where('project_service_id', $validated['project_service_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found.'
            ], 404);
        }

        $newStatus = $assignment->status === 'active' ? 'inactive' : 'active';

        DB::table('project_users')
            ->where('id', $assignment->id)
            ->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff assignment status updated successfully!',
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Delete a staff assignment.
     */
    public function destroy($id)
    {
        $deleted = DB::table('project_users')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Staff assignment deleted successfully!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting assignment.'
            ], 422);
        }
    }
}
