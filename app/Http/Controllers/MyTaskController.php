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
        $projects = \App\Models\Project::orderBy('name')->get();
        $services = \App\Models\Service::orderBy('name')->get();
        $staffs   = \App\Models\Employee::whereNull('resignation')
                    ->where('status', 1)
                    ->orderBy('first_name')
                    ->get();

        if ($request->ajax()) {
            // For AJAX, return all tasks (for loadAllTasks)
            $tasks = Task::where(function ($query) use ($userId) {
                $query->where('created_by', $userId)
                    ->orWhereHas('taskUsers', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
            ->with(['service', 'creator', 'assignments' => function ($q) {
                $q->whereDate('date', \Carbon\Carbon::today());
            }])
            ->orderByDesc('created_at')
            ->get();
        } else {
            // For initial view, return only today's tasks (same as today method)
            $tasks = Task::whereHas('assignments', function ($q) use ($userId) {
                $q->where('staff_id', $userId)
                  ->whereDate('date', \Carbon\Carbon::today());
            })
            ->with(['service', 'creator', 'assignments' => function ($q) {
                $q->whereDate('date', \Carbon\Carbon::today());
            }])
            ->orderByDesc('created_at')
            ->get();
        }

        // Process tasks for tdtask and all_assigned_updated flags
        foreach ($tasks as $task) {
            $assignedTasks = TaskAssigned::where('task_id', $task->id)
                ->whereDate('date', \Carbon\Carbon::today())
                ->get();
            $assignedCount = $assignedTasks->count();
            $task->tdtask = $assignedCount > 0 ? 1 : 0;

            if ($task->tdtask == 1) {
                $docCount = TaskDocument::where('task_id', $task->id)
                    ->whereDate('created_at', \Carbon\Carbon::today()->toDateString())
                    ->count();
                $task->doc_count = $docCount;
                $task->all_assigned_updated = ($docCount >= $assignedCount);
            } else {
                $task->doc_count = 0;
                $task->all_assigned_updated = false;
            }
        }

        $tdtaskcnt = TaskAssigned::where('staff_id', $userId)
            ->whereDate('date', \Carbon\Carbon::today())
            ->count();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'tasks' => $tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'date' => $task->date,
                        'status' => $task->status,
                        'project' => $task->project ? ['name' => $task->project->name] : null,
                        'service' => $task->service ? ['name' => $task->service->name] : null,
                        'creator' => $task->creator ? [
                            'first_name' => $task->creator->first_name,
                            'middle_name' => $task->creator->middle_name,
                            'last_name' => $task->creator->last_name
                        ] : null,
                        'tdtask' => $task->tdtask,
                        'all_assigned_updated' => $task->all_assigned_updated
                    ];
                })
            ]);
        }

        return view('my_tasks.index', compact('tasks', 'projects', 'services', 'staffs', 'tdtaskcnt', 'userId'));
    }

     public function today(Request $request)
     {
         $userId = Auth::id();

         // Retrieve tasks assigned to the user for today
         $tasks = Task::whereHas('assignments', function ($q) use ($userId) {
             $q->where('staff_id', $userId)
               ->whereDate('date', \Carbon\Carbon::today());
         })
         ->with(['service', 'creator', 'assignments' => function ($q) {
             $q->whereDate('date', \Carbon\Carbon::today());
         }])
         ->orderByDesc('created_at')
         ->get();

         // Process tasks for tdtask and all_assigned_updated flags
         foreach ($tasks as $task) {
             $assignedTasks = TaskAssigned::where('task_id', $task->id)
                 ->whereDate('date', \Carbon\Carbon::today())
                 ->get();
             $assignedCount = $assignedTasks->count();
             $task->tdtask = $assignedCount > 0 ? 1 : 0;

             if ($task->tdtask == 1) {
                 $docCount = TaskDocument::where('task_id', $task->id)
                     ->whereDate('created_at', \Carbon\Carbon::today()->toDateString())
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
             'tasks' => $tasks->map(function ($task) {
                 return [
                     'id' => $task->id,
                     'title' => $task->title,
                     'description' => $task->description,
                     'date' => $task->date,
                     'status' => $task->status,
                     'project' => $task->project ? ['name' => $task->project->name] : null,
                     'service' => $task->service ? ['name' => $task->service->name] : null,
                     'creator' => $task->creator ? [
                         'first_name' => $task->creator->first_name,
                         'middle_name' => $task->creator->middle_name,
                         'last_name' => $task->creator->last_name
                     ] : null,
                     'tdtask' => $task->tdtask,
                     'all_assigned_updated' => $task->all_assigned_updated
                 ];
             })
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
            'frequency'   => 'required_if:assign_self,1|string|in:One-time,Daily,Once in a week,2 in a week,3 in a week,4 in a week,Monthly,2 in Month,3 in Month,4 in Month',
            'start_date'  => 'required_if:frequency,Daily,Once in a week,2 in a week,3 in a week,4 in a week,Monthly,2 in Month,3 in Month,4 in Month|nullable|date',
            'end_date'    => 'required_if:frequency,One-time|nullable|date',
            'selected_days'  => 'required_if:frequency,Once in a week,2 in a week,3 in a week,4 in a week|nullable|array',
            'selected_dates' => 'required_if:frequency,Monthly,2 in Month,3 in Month,4 in Month|nullable|array',
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
