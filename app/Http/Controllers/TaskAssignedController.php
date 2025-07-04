<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Project;
use App\Models\Service;
use App\Models\TaskUser;
use App\Models\TaskComment;
use App\Models\Notification;
use App\Models\TaskAssigned;
use App\Models\TaskDocument;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TaskAssignedController extends Controller
{
    /**
     * Assign staff to a task based on frequency and selected days/dates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $task
     * @return \Illuminate\Http\JsonResponse
     */
public function assignStaff(Request $request, $task)
{
    $validated = $request->validate([
        'frequency'      => 'required|string|in:One-time,Daily,Once in a week,2 in a week,3 in a week,4 in a week,Monthly,2 in Month,3 in Month,4 in Month',
        'staff_ids'      => 'required|array',
        'start_date'     => 'nullable|date',
        'end_date'       => 'nullable|date',
        'selected_days'  => 'nullable|array',  // for weekly frequencies
        'selected_dates' => 'nullable|array',  // for monthly frequencies
    ]);

    $frequency = $validated['frequency'];
    $staffIds  = $validated['staff_ids'];

    // Retrieve the task record.
    $taskModel = \App\Models\Task::findOrFail($task);

    // Retrieve project and service details.
    $project = \App\Models\Project::find($taskModel->project_id);
    $projectName = $project ? $project->name : '';

    $service = \App\Models\Service::find($taskModel->service_id);
    $serviceName = $service ? $service->name : '';

    // Use provided start_date or default to today.
    $start = (!empty($validated['start_date']))
                ? \Carbon\Carbon::parse($validated['start_date'])
                : \Carbon\Carbon::today();

    // Determine the deadline: use task's deadline if valid, else start + 30 days.
    if ($taskModel->deadline && \Carbon\Carbon::parse($taskModel->deadline)->gte($start)) {
        $taskDeadline = \Carbon\Carbon::parse($taskModel->deadline);
    } else {
        $taskDeadline = $start->copy()->addDays(30);
    }

    $assignmentDates = [];

    if ($frequency === 'One-time') {
        if (!empty($validated['end_date'])) {
            $assignmentDates[] = \Carbon\Carbon::parse($validated['end_date'])->toDateString();
        }
    } elseif ($frequency === 'Daily') {
        $current = $start->copy();
        while ($current->lte($taskDeadline)) {
            $assignmentDates[] = $current->toDateString();
            $current->addDay();
        }
    } elseif (str_contains($frequency, 'week')) {
        // Ensure that selected_days is provided.
        if (!empty($validated['selected_days']) && is_array($validated['selected_days'])) {
            $current = $start->copy();
            while ($current->lte($taskDeadline)) {
                if (in_array($current->format('l'), $validated['selected_days'])) {
                    $assignmentDates[] = $current->toDateString();
                }
                $current->addDay();
            }
        }
    } elseif (str_contains($frequency, 'Month')) {
        // For monthly frequencies.
        if (!empty($validated['selected_dates']) && is_array($validated['selected_dates'])) {
            $current = $start->copy();
            while ($current->lte($taskDeadline)) {
                foreach ($validated['selected_dates'] as $day) {
                    try {
                        $candidate = \Carbon\Carbon::createFromDate($current->year, $current->month, $day);
                        if ($candidate->between($start, $taskDeadline)) {
                            $assignmentDates[] = $candidate->toDateString();
                        }
                    } catch (\Exception $e) {
                        // Skip invalid dates (e.g., Feb 30)
                    }
                }
                $current->addMonth();
            }
        }
    }

    Log::info('Assignment dates generated', [
        'frequency' => $frequency,
        'start'     => $start->toDateString(),
        'deadline'  => $taskDeadline->toDateString(),
        'dates'     => $assignmentDates
    ]);

    // Insert records into task_assigned for each staff and each date.
    foreach ($staffIds as $staffId) {
        foreach ($assignmentDates as $date) {
            \App\Models\TaskAssigned::create([
                'task_id'    => $taskModel->id,
                'staff_id'   => $staffId,
                'date'       => $date,
                'status'     => 0, // default status; adjust if needed
                'created_by' => auth()->id(),
            ]);
        }
        // Insert a record into task_user for tracking.
        \App\Models\TaskUser::create([
            'task_id'     => $taskModel->id,
            'user_id'     => $staffId,
            'type'        => $frequency,
            'assigned_at' => now(),
        ]);
    }

    // Create notifications for each assigned staff.
    if (!empty($staffIds)) {
        $taskName = $taskModel->title ?? 'Task';

        foreach ($staffIds as $staffId) {
            // Retrieve staff details.
            $employee = \App\Models\Employee::find($staffId);
            $staffName = $employee ? ($employee->first_name . ' ' . $employee->last_name) : 'Staff';

            // Build notification message using current date/time.
            $notificationMessage = "{$staffName} has updated the task document, please verify and update task status: '{$taskName}'.\n\nAssigned Date: " . \Carbon\Carbon::now('Asia/Kolkata')->format('d M Y H:i') . "\n\n({$projectName} | {$serviceName})";

            // Determine recipients:
            // Recipient 1: Task creator.
            $creatorId = $taskModel->created_by;
            // Recipient 2: All users with role 2 or 3 in the same department as this assigned staff.
            $assignedStaffDept = optional($employee)->department_id;
            $deptUsers = \App\Models\User::whereHas('employee', function($q) use ($assignedStaffDept) {
                $q->where('department_id', $assignedStaffDept);
            })->whereIn('role_id', [2, 3])->pluck('id')->toArray();

            // Combine recipients, filtering out nulls.
            $recipientIds = array_filter(array_unique(array_merge([$creatorId], $deptUsers)), function($id) {
                return !is_null($id);
            });

            foreach ($recipientIds as $recipientId) {
                \App\Models\Notification::create([
                    'user_id' => $recipientId,
                    'title'   => 'Task Assignment: ' . $taskName,
                    'message' => $notificationMessage,
                    'type'    => 'task_assignment',
                ]);
            }
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Staff assigned successfully and notified.',
    ]);
}



    public function details($task)
    {
        // Load the task along with its assignments, each with comments, documents, and staff.
        $task = Task::with(['assignments.comments', 'assignments.documents', 'assignments.staff', 'assignments.creator'])
                    ->findOrFail($task);

        return view('tasks.details', compact('task'));
    }
   public function detailssub($task)
    {
        $userId = Auth::id();
        $task = Task::with(['assignments.comments', 'assignments.documents', 'assignments.staff'])
                    ->findOrFail($task);

        // Filter assignments to include only those for the current user, then sort them by date (or created_at)
        $task->assignments = $task->assignments
            ->filter(function($assignment) use ($userId) {
                return $assignment->staff_id == $userId;
            })
            ->sortBy('date'); // Replace 'date' with 'created_at' if necessary

        return view('my_tasks.details', compact('task'));
    }

    // Return the edit assignment form.
    public function editAssignment($id)
    {
        $assignment = \App\Models\TaskAssigned::findOrFail($id);
        $staffs = \App\Models\Employee::all(); // Retrieve all staffs (or apply any needed filtering)
        return view('assignments.edit', compact('assignment', 'staffs'));
    }

    /**
     * Update an assignment (date and status).
     */
    public function updateAssignment(Request $request, $id)
{
    $validated = $request->validate([
        'date'     => 'required|date',
        'status'   => 'required|in:0,1',
        'staff_id' => 'required|integer|exists:employees,id',
    ]);

    $assignment = \App\Models\TaskAssigned::findOrFail($id);
    $assignment->date = $validated['date'];
    $assignment->status = $validated['status'];
    $assignment->staff_id = $validated['staff_id'];
    $assignment->save();

    // Retrieve the related task using the relationship (ensure TaskAssigned has a task() method)
    $task = $assignment->task;
    $taskTitle = $task ? $task->title : 'Task';

    // Build the notification message using the updated assignment date.
    $notificationMessage = "You have been assigned to the task: '{$taskTitle}' on " .
                           \Carbon\Carbon::parse($assignment->date)->format('d M Y') . ".";

    // Retrieve the employee record to obtain the associated user id.

        \App\Models\Notification::create([
            'user_id' => $validated['staff_id'],
            'title'   => 'Task Assignment: ' . $taskTitle,
            'message' => $notificationMessage,
            'type'    => 'task_assignment',
        ]);


    return response()->json([
        'success'    => true,
        'message'    => 'Assignment updated successfully.',
        'assignment' => $assignment,
    ]);
}


    /**
     * Return the document upload form.
     */
    public function getUploadDocumentForm($assignment)
    {
        $assignment = TaskAssigned::findOrFail($assignment);
        return view('assignments.upload_document', compact('assignment'));
    }

    /**
     * Handle document upload.
     *
     * Stores task_id and user_id.
     */
    public function uploadDocument(Request $request, $assignment)
    {
        $assignmentModel = \App\Models\TaskAssigned::findOrFail($assignment);
        $validated = $request->validate([
            'document_name'   => 'required|string|max:255',
            'task_assigned_id'=> 'required|int',
            'task_id'         => 'required|int',
            'description'     => 'nullable|string',
            'file_path'       => 'nullable|file|mimes:pdf,jpg,png,doc,docx'
        ]);

        // Handle file upload if provided.
        $filePath = null;
        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $filename = 'proj_' . time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('Uploads/task_documents'), $filename);
            $filePath = 'Uploads/task_documents/' . $filename;
        }

        // Create the TaskDocument record.
        $document = \App\Models\TaskDocument::create([
            'task_id'           => $validated['task_id'],
            'task_assigned_id'  => $validated['task_assigned_id'],
            'document_name'     => $validated['document_name'],
            'file_path'         => $filePath,
            'description'       => $validated['description'] ?? '',
            'user_id'           => auth()->id(),
        ]);

        // Retrieve the related task and its details.
        $task = $assignmentModel->task; // Ensure TaskAssigned model has a task() relationship.
        $taskName = $task->title ?? 'Task';
        $projectName = optional($task->project)->name ?? 'N/A';
        $serviceName = optional($task->service)->name ?? 'N/A';

        // Get assigned staff details.
        $staff = $assignmentModel->staff; // Ensure TaskAssigned model has a staff() relationship.
        $staffName = trim(optional($staff)->first_name . ' ' . optional($staff)->last_name);

        // Get current date/time in Asia/Kolkata timezone.
        $currentDateTime = \Carbon\Carbon::now('Asia/Kolkata')->format('d M Y H:i');

        // Build the notification message.
        $notificationMessage = "{$staffName} has updated the task document, please verify and update task status: '{$taskName}'.\n\nAssigned Date: {$currentDateTime}\n\n({$projectName} | {$serviceName})";

        // Determine recipients:
        // Recipient 1: The creator of the task.
        $creatorId = $task->created_by;
        // Recipient 2: All users with role=3 in the same department as the assigned staff.
        $assignedStaffDept = optional($staff)->department_id;
        $deptUsers = \App\Models\User::whereHas('employee', function($q) use ($assignedStaffDept) {
            $q->where('department_id', $assignedStaffDept);
        })->whereIn('role_id', [2, 3])->pluck('id')->toArray();

        // Merge recipients and filter out null values.
        $recipientIds = array_filter(array_unique(array_merge([$creatorId], $deptUsers)), function($id) {
            return !is_null($id);
        });

        // Create notification for each recipient.
        foreach ($recipientIds as $recipientId) {
            \App\Models\Notification::create([
                'user_id' => $recipientId,
                'title'   => 'Task Assignment: ' . $taskName,
                'message' => $notificationMessage,
                'type'    => 'task_assignment',
            ]);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Document uploaded successfully and notification sent.',
            'document' => $document
        ]);
    }


    /**
     * Return the add comment form.
     */
    public function getAddCommentForm($assignment)
    {
        $assignment = TaskAssigned::findOrFail($assignment);
        return view('assignments.add_comment', compact('assignment'));
    }

    /**
     * Add a comment.
     *
     * Saves task_id and user_id.
     */
    public function addComment(Request $request, $assignment)
    {
        $assignmentModel = TaskAssigned::findOrFail($assignment);
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);
        $comment = TaskComment::create([
            'task_assigned_id' => $assignmentModel->id,
            'task_id'          => $assignmentModel->task_id,
            'user_id'          => auth()->id(),
            'comment'          => $validated['comment'],
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'comment' => $comment
        ]);
    }
    public function destroy($assignment)
    {
        $assignmentModel = TaskAssigned::findOrFail($assignment);
        $assignmentModel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment deleted successfully.'
        ]);
    }

}
