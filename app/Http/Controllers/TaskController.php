<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Project;
use App\Models\Service;
use App\Models\Employee;
use App\Models\TaskUser;
use App\Models\Notification;
use App\Models\TaskAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class TaskController extends Controller
{
    // Display tasks index page.

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = \App\Models\Task::query();

        // If user role is 3, restrict tasks to those created by them or with a service
        // in their department.
        if ($user->role_id == 3) {
            $departmentId = optional($user->employee)->department_id;
            $query->where(function($q) use ($user, $departmentId) {
                $q->where('created_by', $user->id)
                ->orWhereHas('service', function($q2) use ($departmentId) {
                    $q2->where('department_id', $departmentId);
                });
            });
        }

        // Filter by created date range (optional)
        if ($request->filled('created_start') && $request->filled('created_end')) {
            $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($request->input('created_start'))->startOfDay(),
                \Carbon\Carbon::parse($request->input('created_end'))->endOfDay()
            ]);
        }

        // Filter by deadline range (optional)
        if ($request->filled('deadline_start') && $request->filled('deadline_end')) {
            $query->whereBetween('deadline', [
                \Carbon\Carbon::parse($request->input('deadline_start'))->startOfDay(),
                \Carbon\Carbon::parse($request->input('deadline_end'))->endOfDay()
            ]);
        }

        // Filter by assigned by (task creator)
        if ($request->filled('assigned_by')) {
            $query->where('created_by', $request->input('assigned_by'));
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->input('service_id'));
        }

        // New Filter: Filter by assigned staff (based on TaskAssigned records)
        if ($request->filled('assigned_staff')) {
            $query->whereHas('assignments', function($q) use ($request) {
                $q->where('staff_id', $request->input('assigned_staff'));
            });
        }

        // Eager load the creator, assignments with staff, and service.
        $tasks = $query->with(['creator', 'assignments.staff', 'service'])
                    ->orderByDesc('created_at')
                    ->get();

        $projects = \App\Models\Project::orderBy('name')->get();
        $services = \App\Models\Service::orderBy('name')->get();
        $staffs   = \App\Models\Employee::whereNull('resignation')
                    ->where('status', 1)
                    ->orderBy('first_name')
                    ->get();

        return view('tasks.index', compact('tasks', 'projects', 'services', 'staffs'));
    }

    // Store a new task.
    public function store(Request $request)
    {
        Log::emergency('Store method called', ['url' => $request->url(), 'method' => $request->method()]);
        try {
            Log::info('Store Request Data:', $request->all());
            $userId = Auth::id();
            if (!$userId) {
                Log::error('No authenticated user found');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }
            Log::info('Authenticated user', ['user_id' => $userId]);

            $validated = $request->validate([
                'project_id'  => 'required|integer|exists:projects,id',
                'service_id'  => 'nullable|integer|exists:services,id',
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'deadline'    => 'nullable|date',
                'status'      => 'required|in:pending,in_progress,completed,hold',
                'staff_ids'   => 'nullable|array',
                'staff_ids.*' => 'integer|exists:employees,id',
                'frequency'   => 'required_if:staff_ids,!=,|string|in:One-time,Daily,Once in a week,2 in a week,3 in a week,4 in a week,Monthly,2 in Month,3 in Month,4 in Month',
                'start_date'  => 'required_if:frequency,Daily,Once in a week,2 in a week,3 in a week,4 in a week,Monthly,2 in Month,3 in Month,4 in Month|nullable|date',
                'end_date'    => 'required_if:frequency,One-time|nullable|date',
                'selected_days'  => 'required_if:frequency,Once in a week,2 in a week,3 in a week,4 in a week|nullable|array',
                'selected_dates' => 'required_if:frequency,Monthly,2 in Month,3 in Month,4 in Month|nullable|array',
            ]);
            Log::info('Validated Data:', $validated);

            $validated['created_by'] = $userId;
            $task = Task::create($validated);
            Log::info('Task created', ['task_id' => $task->id]);

            if (!empty($validated['staff_ids'])) {
                Log::info('Processing staff assignment', ['task_id' => $task->id, 'staff_ids' => $validated['staff_ids']]);
                $frequency = $validated['frequency'] ?? 'One-time';
                $start = !empty($validated['start_date'])
                    ? Carbon::parse($validated['start_date'])
                    : Carbon::today();

                $taskDeadline = $task->deadline && Carbon::parse($task->deadline)->gte($start)
                    ? Carbon::parse($task->deadline)
                    : $start->copy()->addDays(30);

                $assignmentDates = [];

                if ($frequency === 'One-time') {
                    $assignmentDates[] = !empty($validated['end_date'])
                        ? Carbon::parse($validated['end_date'])->toDateString()
                        : $start->toDateString();
                } elseif ($frequency === 'Daily') {
                    $current = $start->copy();
                    while ($current->lte($taskDeadline)) {
                        $assignmentDates[] = $current->toDateString();
                        $current->addDay();
                    }
                } elseif (str_contains($frequency, 'week')) {
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
                    if (!empty($validated['selected_dates']) && is_array($validated['selected_dates'])) {
                        $current = $start->copy();
                        while ($current->lte($taskDeadline)) {
                            foreach ($validated['selected_dates'] as $day) {
                                try {
                                    $candidate = Carbon::createFromDate($current->year, $current->month, $day);
                                    if ($candidate->between($start, $taskDeadline)) {
                                        $assignmentDates[] = $candidate->toDateString();
                                    }
                                } catch (\Exception $e) {
                                    Log::warning("Invalid date skipped: {$current->year}-{$current->month}-{$day}");
                                }
                            }
                            $current->addMonth();
                        }
                    }
                }

                Log::info('Assignment Dates:', ['dates' => $assignmentDates]);

                if (empty($assignmentDates)) {
                    Log::warning('No assignment dates generated', ['frequency' => $frequency, 'validated' => $validated]);
                    return response()->json([
                        'success' => false,
                        'message' => 'No assignment dates generated for the selected frequency.',
                    ], 422);
                }

                foreach ($validated['staff_ids'] as $staffId) {
                    foreach ($assignmentDates as $date) {
                        TaskAssigned::create([
                            'task_id'    => $task->id,
                            'staff_id'   => $staffId,
                            'date'       => $date,
                            'status'     => 0,
                            'created_by' => $userId,
                        ]);
                    }

                    TaskUser::create([
                        'task_id'     => $task->id,
                        'user_id'     => $staffId,
                        'type'        => $frequency,
                        'assigned_at' => now(),
                    ]);

                    $project = Project::find($task->project_id);
                    $service = Service::find($task->service_id);
                    $employee = Employee::find($staffId);
                    $taskName = $task->title ?? 'Task';
                    $staffName = $employee ? ($employee->first_name . ' ' . $employee->last_name) : 'Staff';
                    $projectName = $project ? $project->name : '';
                    $serviceName = $service ? $service->name : '';

                    $notificationMessage = "{$staffName} has been assigned to task: '{$taskName}'.\n\nAssigned Date: " . Carbon::now('Asia/Kolkata')->format('d M Y H:i') . "\n\n({$projectName} | {$serviceName})";

                    Notification::create([
                        'user_id' => $staffId,
                        'title'   => 'Task Assignment: ' . $taskName,
                        'message' => $notificationMessage,
                        'type'    => 'task_assignment',
                    ]);
                }
            }

            $taskHtml = view('tasks.task_item', compact('task'))->render();

            return response()->json([
                'success'  => true,
                'message'  => 'Task created successfully!',
                'taskHtml' => $taskHtml,
                'task'     => $task,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in store method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating task: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Return the edit form for a task.
    public function edit($id)
    {
        $task = Task::findOrFail($id);
        // Get projects and services for the dropdowns.
        $projects = Project::all();
        $services = Service::all();
        return view('tasks.edit_task_form', compact('task', 'projects', 'services'));
    }

    // Update an existing task.
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'project_id'  => 'required|integer|exists:projects,id',
            'service_id'  => 'nullable|integer|exists:services,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
            'status'      => 'required|in:pending,in_progress,completed,hold',
        ]);

        $task->update($validated);

        // Render updated task HTML.
        $taskHtml = view('tasks.task_item', compact('task'))->render();

        return response()->json([
            'success'  => true,
            'message'  => 'Task updated successfully!',
            'taskHtml' => $taskHtml,
            'task'     => $task,
        ]);
    }

    // Delete a task.
   public function destroy($id)
{
    $task = Task::findOrFail($id);

    // Check if there are any subtasks (TaskAssigned records) for this task.
    $assignedCount = \App\Models\TaskAssigned::where('task_id', $id)->count();

    if ($assignedCount > 0) {
        return response()->json([
            'success' => false,
            'message' => 'Please verify and remove all subtasks before proceeding to delete this task.'
        ], 422);
    }

    $task->delete();

    return response()->json([
        'success' => true,
        'message' => 'Task deleted successfully!'
    ]);
}


    // Optional: Live search for tasks.
    public function search(Request $request)
    {
        $query = $request->query('query');

        // Search tasks by title or description.
        $tasks = Task::where('title', 'like', "%{$query}%")
                     ->orWhere('description', 'like', "%{$query}%")
                     ->get();

        // Render the HTML using the _list partial.
        $html = view('tasks._list', compact('tasks'))->render();

        return response()->json(['html' => $html]);
    }
    public function show($id)
    {
        // Optionally, retrieve and display a single task.
        $task = Task::findOrFail($id);
        return view('tasks.show', compact('task'));
    }


}
