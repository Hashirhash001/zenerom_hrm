<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Project;
use App\Models\Service;
use App\Models\Employee;
use App\Models\TaskAssigned;
use App\Models\TaskDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MyTaskController extends Controller
{
    /**
     * Display the tasks assigned to the logged in employee.
     */

    public function index(Request $request)
    {
        $userId = Auth::id();
        $projects = Project::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $staffs = Employee::whereNull('resignation')
            ->where('status', 1)
            ->orderBy('first_name')
            ->get();

        // Determine if the user is an admin or authorized (role_id in [1, 2, 3, 7])
        $isAdminOrAuthorized = Auth::check() && in_array(Auth::user()->role_id ?? 0, [1, 2, 3, 7]);

        // Get filter parameters
        $projectId = $request->input('project_id');
        $serviceId = $request->input('service_id');
        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = $request->input('length', 10);
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validate sort column to prevent SQL injection
        $validColumns = ['id', 'title', 'description', 'deadline', 'status', 'created_at'];
        $sortColumn = in_array($sortColumn, $validColumns) ? $sortColumn : 'created_at';
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        if ($request->ajax()) {
            // For AJAX, return all tasks with filters, search, pagination, and sorting
            $query = Task::where(function ($query) use ($userId) {
                $query->where('created_by', $userId)
                    ->orWhereHas('taskUsers', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
                ->with(['service', 'creator', 'project', 'assignments' => function ($q) {
                    $q->whereDate('date', Carbon::today());
                }]);

            // Apply filters
            if ($projectId) {
                $query->where('project_id', $projectId);
            }
            if ($serviceId) {
                $query->where('service_id', $serviceId);
            }
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            }

            // Apply sorting with special handling for deadline
            if ($sortColumn === 'deadline') {
                $query->orderByRaw('ISNULL(deadline) ' . $sortDirection . ', deadline ' . $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }

            // Paginate results
            $tasks = $query->paginate($perPage);

            // Process tasks for tdtask and all_assigned_updated flags
            foreach ($tasks as $task) {
                $assignedTasks = TaskAssigned::where('task_id', $task->id)
                    ->whereDate('date', Carbon::today())
                    ->get();
                $assignedCount = $assignedTasks->count();
                $task->tdtask = $assignedCount > 0 ? 1 : 0;

                if ($task->tdtask == 1) {
                    $docCount = TaskDocument::where('task_id', $task->id)
                        ->whereDate('created_at', Carbon::today()->toDateString())
                        ->count();
                    $task->doc_count = $docCount;
                    $task->all_assigned_updated = ($docCount >= $assignedCount);
                } else {
                    $task->doc_count = 0;
                    $task->all_assigned_updated = false;
                }
            }

            return response()->json([
                'success' => true,
                'tasks' => $tasks->items(),
                'pagination' => [
                    'total' => $tasks->total(),
                    'per_page' => $tasks->perPage(),
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'from' => $tasks->firstItem(),
                    'to' => $tasks->lastItem(),
                ],
                'isAdminOrAuthorized' => $isAdminOrAuthorized
            ]);
        }

        // For initial view, return today's tasks with filters, pagination, and sorting
        $query = Task::whereHas('assignments', function ($q) use ($userId) {
            $q->where('staff_id', $userId)
                ->whereDate('date', Carbon::today());
        })
            ->with(['service', 'creator', 'project', 'assignments' => function ($q) {
                $q->whereDate('date', Carbon::today());
            }]);

        // Apply filters
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting with special handling for deadline
        if ($sortColumn === 'deadline') {
            $query->orderByRaw('ISNULL(deadline) ' . $sortDirection . ', deadline ' . $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        // Paginate results
        $tasks = $query->paginate($perPage);

        // Process tasks for tdtask and all_assigned_updated flags
        foreach ($tasks as $task) {
            $assignedTasks = TaskAssigned::where('task_id', $task->id)
                ->whereDate('date', Carbon::today())
                ->get();
            $assignedCount = $assignedTasks->count();
            $task->tdtask = $assignedCount > 0 ? 1 : 0;

            if ($task->tdtask == 1) {
                $docCount = TaskDocument::where('task_id', $task->id)
                    ->whereDate('created_at', Carbon::today()->toDateString())
                    ->count();
                $task->doc_count = $docCount;
                $task->all_assigned_updated = ($docCount >= $assignedCount);
            } else {
                $task->doc_count = 0;
                $task->all_assigned_updated = false;
            }
        }

        $tdtaskcnt = TaskAssigned::where('staff_id', $userId)
            ->whereDate('date', Carbon::today())
            ->count();

        return view('my_tasks.index', compact('tasks', 'projects', 'services', 'staffs', 'tdtaskcnt', 'userId', 'isAdminOrAuthorized', 'projectId', 'serviceId', 'status', 'search', 'sortColumn', 'sortDirection'));
    }

    public function today(Request $request)
    {
        $userId = Auth::id();

        // Get filter parameters
        $projectId = $request->input('project_id');
        $serviceId = $request->input('service_id');
        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = $request->input('length', 10);
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validate sort column to prevent SQL injection
        $validColumns = ['id', 'title', 'description', 'deadline', 'status', 'created_at'];
        $sortColumn = in_array($sortColumn, $validColumns) ? $sortColumn : 'created_at';
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        // Retrieve tasks assigned to the user for today with filters, pagination, and sorting
        $query = Task::whereHas('assignments', function ($q) use ($userId) {
            $q->where('staff_id', $userId)
                ->whereDate('date', Carbon::today());
        })
            ->with(['service', 'creator', 'project', 'assignments' => function ($q) {
                $q->whereDate('date', Carbon::today());
            }]);

        // Apply filters
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Apply sorting with special handling for deadline
        if ($sortColumn === 'deadline') {
            $query->orderByRaw('ISNULL(deadline) ' . $sortDirection . ', deadline ' . $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        // Paginate results
        $tasks = $query->paginate($perPage);

        // Process tasks for tdtask and all_assigned_updated flags
        foreach ($tasks as $task) {
            $assignedTasks = TaskAssigned::where('task_id', $task->id)
                ->whereDate('date', Carbon::today())
                ->get();
            $assignedCount = $assignedTasks->count();
            $task->tdtask = $assignedCount > 0 ? 1 : 0;

            if ($task->tdtask == 1) {
                $docCount = TaskDocument::where('task_id', $task->id)
                    ->whereDate('created_at', Carbon::today()->toDateString())
                    ->count();
                $task->doc_count = $docCount;
                $task->all_assigned_updated = ($docCount >= $assignedCount);
            } else {
                $task->doc_count = 0;
                $task->all_assigned_updated = false;
            }
        }

        return response()->json([
            'success' => true,
            'tasks' => $tasks->items(),
            'pagination' => [
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'from' => $tasks->firstItem(),
                'to' => $tasks->lastItem(),
            ]
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $userId = Auth::id();
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,hold',
        ]);

        // Find the task and ensure the user is assigned to it
        $task = Task::where('id', $id)
            ->whereHas('assignments', function ($q) use ($userId) {
                $q->where('staff_id', $userId)
                    ->whereDate('date', \Carbon\Carbon::today());
            })
            ->firstOrFail();

        // Update the task status
        $task->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully!'
        ]);
    }

    /**
     * Store a newly created task.
     * (You may adapt this code from your existing Task creation logic.)
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        Log::info('Store Request Data:', $request->all());
        $validated = $request->validate([
            'project_id'  => 'required|integer|exists:projects,id',
            'service_id'  => 'nullable|integer|exists:services,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
            'status'      => 'required|in:pending,in_progress,completed,hold',
            'assign_self' => 'nullable|boolean',
            'frequency'   => 'nullable|string|in:One-time,Daily,Once in a week,2 in a week,3 in a week,4 in a week,Monthly,2 in Month,3 in Month,4 in Month',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|required_if:frequency,One-time',
            'selected_days'  => 'nullable|array|required_if:frequency,Once in a week,2 in a week,3 in a week,4 in a week',
            'selected_dates' => 'nullable|array|required_if:frequency,Monthly,2 in Month,3 in Month,4 in Month',
        ]);
        Log::info('Validated Data:', $validated);

        // Add created_by field using the current user id.
        $validated['created_by'] = $userId;

        $task = Task::create($validated);

        // Handle self-assignment
        if (isset($validated['assign_self']) && $validated['assign_self']) {
            $frequency = $validated['frequency'] ?? 'One-time';
            $start = !empty($validated['start_date'])
                ? \Carbon\Carbon::parse($validated['start_date'])
                : \Carbon\Carbon::today();

            $taskDeadline = $task->deadline && \Carbon\Carbon::parse($task->deadline)->gte($start)
                ? \Carbon\Carbon::parse($task->deadline)
                : $start->copy()->addDays(30);

            $assignmentDates = [];

            if ($frequency === 'One-time') {
                $assignmentDates[] = !empty($validated['end_date'])
                    ? \Carbon\Carbon::parse($validated['end_date'])->toDateString()
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
                                $candidate = \Carbon\Carbon::createFromDate($current->year, $current->month, $day);
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

            // Assign task to current user
            foreach ($assignmentDates as $date) {
                \App\Models\TaskAssigned::create([
                    'task_id'    => $task->id,
                    'staff_id'   => $userId,
                    'date'       => $date,
                    'status'     => 0,
                    'created_by' => $userId,
                ]);
            }

            \App\Models\TaskUser::create([
                'task_id'     => $task->id,
                'user_id'     => $userId,
                'type'        => $frequency,
                'assigned_at' => now(),
            ]);

            // Create notification
            $project = \App\Models\Project::find($task->project_id);
            $service = \App\Models\Service::find($task->service_id);
            $employee = \App\Models\Employee::find($userId);
            $taskName = $task->title ?? 'Task';
            $staffName = $employee ? ($employee->first_name . ' ' . $employee->last_name) : 'Staff';
            $projectName = $project ? $project->name : '';
            $serviceName = $service ? $service->name : '';

            $notificationMessage = "{$staffName} has assigned themselves to task: '{$taskName}'.\n\nAssigned Date: " . \Carbon\Carbon::now('Asia/Kolkata')->format('d M Y H:i') . "\n\n({$projectName} | {$serviceName})";

            \App\Models\Notification::create([
                'user_id' => $userId,
                'title'   => 'Task Assignment: ' . $taskName,
                'message' => $notificationMessage,
                'type'    => 'task_assignment',
            ]);
        }

        // Render the new task's HTML
        $taskHtml = view('tasks.task_item', compact('task'))->render();

        return response()->json([
            'success'  => true,
            'message'  => 'Task created successfully!',
            'taskHtml' => $taskHtml,
            'task'     => $task,
        ]);
    }

    public function report(Request $request)
    {
        $userId = Auth::id();

        // Get filter values; default to today if not provided.
        $startDate = $request->input('start_date', Carbon::today('Asia/Kolkata')->toDateString());
        $endDate   = $request->input('end_date', Carbon::today('Asia/Kolkata')->toDateString());
        $staffId   = $request->input('staff_id', '');

        // Retrieve assignments for the logged in user within the date range.
        // Make sure the TaskAssigned model has relationships: task(), documents(), comments(), and staff()
        $assignments = TaskAssigned::where('staff_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with([
                'task.project',  // Task's project relationship
                'task.service',  // Task's service relationship
                'staff',         // Assigned staff
                'documents',     // Uploaded documents
                'comments.user'  // Comments with user details
            ])
            ->orderBy('date', 'desc')
            ->get();

        // For filter dropdown, retrieve all employees.
        $employees = Employee::all();

        return view('my_tasks.report', compact('assignments', 'startDate', 'endDate', 'staffId', 'employees'));
    }
}
