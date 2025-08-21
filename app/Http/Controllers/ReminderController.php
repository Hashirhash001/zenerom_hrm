<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\ReminderStatus;
use App\Models\Project;
use App\Models\Service;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Reminder::query()->with(['project', 'services'])->where('user_id', $user->id);

        if ($project_id = $request->project_id) {
            $query->where('project_id', $project_id);
        }
        if ($service_ids = $request->service_ids) {
            $query->whereHas('services', function ($q) use ($service_ids) {
                $q->whereIn('services.id', $service_ids);
            });
        }
        if ($frequency = $request->frequency) {
            $query->where('frequency', $frequency);
        }
        if ($search = $request->search) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($sortBy = $request->sort_by) {
            $query->orderBy($sortBy, $request->sort_direction ?? 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $reminders = $query->paginate(10);
        $projects = Project::orderBy('name')->get();
        $services = Service::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'reminders' => $reminders->items(),
                'pagination' => [
                    'current_page' => $reminders->currentPage(),
                    'last_page' => $reminders->lastPage(),
                    'total' => $reminders->total(),
                    'per_page' => $reminders->perPage(),
                ],
            ]);
        }

        return view('reminders.index', compact('reminders', 'projects', 'services'));
    }

    public function upcoming(Request $request)
    {
        $user = Auth::user();
        $query = Reminder::query()->where('active', true)->with(['project', 'services', 'statuses'])->where('user_id', $user->id);

        if ($project_id = $request->project_id) {
            $query->where('project_id', $project_id);
        }
        if ($service_ids = $request->service_ids) {
            $query->whereHas('services', function ($q) use ($service_ids) {
                $q->whereIn('services.id', $service_ids);
            });
        }
        if ($frequency = $request->frequency) {
            $query->where('frequency', $frequency);
        }
        if ($search = $request->search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $reminders = $query->get();

        $today = Carbon::today('Asia/Kolkata');
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $thisWeek = [];
        $thisMonth = [];
        $dailyReminders = [];
        $processedDates = []; // Track processed reminder_id + date combinations

        foreach ($reminders as $reminder) {
            $reminderDates = $this->getReminderDates($reminder, $weekStart, $weekEnd, $monthStart, $monthEnd);
            $services = $reminder->services->isEmpty() ? 'N/A' : $reminder->services->pluck('name')->implode(', ');
            $timeOfDay = Carbon::createFromFormat('H:i:s', $reminder->time_of_day, 'Asia/Kolkata')->format('h:i A');

            $reminder->load('statuses');
            Log::info('Reminder statuses loaded', [
                'reminder_id' => $reminder->id,
                'statuses_count' => $reminder->statuses->count(),
                'statuses' => $reminder->statuses->map(function ($status) {
                    return [
                        'report_date' => $status->report_date->format('Y-m-d'),
                        'status' => $status->status,
                    ];
                })->toArray(),
            ]);

            if ($reminder->frequency === 'daily') {
                $current = $weekStart->copy();
                while ($current <= $weekEnd) {
                    $date = $current->format('Y-m-d');
                    $key = $reminder->id . '-' . $date;
                    if (isset($processedDates[$key])) {
                        $current->addDay();
                        continue; // Skip duplicates
                    }
                    $processedDates[$key] = true;

                    $statusRecord = $reminder->statuses->first(function ($status) use ($date) {
                        return $status->report_date->format('Y-m-d') === $date;
                    });
                    $status = $statusRecord ? $statusRecord->status : 'not_completed';
                    Log::info('Reminder status check', [
                        'reminder_id' => $reminder->id,
                        'report_date' => $date,
                        'status' => $status,
                        'status_record' => $statusRecord ? [
                            'id' => $statusRecord->id,
                            'report_date' => $statusRecord->report_date->format('Y-m-d'),
                            'status' => $statusRecord->status,
                        ] : null,
                    ]);
                    $isToday = Carbon::parse($date, 'Asia/Kolkata')->isToday();
                    $isOverdue = $status === 'not_completed' && Carbon::parse($date, 'Asia/Kolkata')->isBefore($today);

                    // Include overdue tasks for every day from report_date until today or completed
                    if ($status === 'not_completed' && Carbon::parse($date, 'Asia/Kolkata')->isBefore($today)) {
                        $overdueDate = Carbon::parse($date, 'Asia/Kolkata');
                        $currentOverdue = $overdueDate->copy();
                        while ($currentOverdue <= $today && $status === 'not_completed') {
                            $overdueKey = $reminder->id . '-' . $currentOverdue->format('Y-m-d');
                            if (isset($processedDates[$overdueKey])) {
                                $currentOverdue->addDay();
                                continue; // Skip duplicates
                            }
                            $processedDates[$overdueKey] = true;

                            if ($currentOverdue >= $weekStart && $currentOverdue <= $weekEnd) {
                                $thisWeek[] = [
                                    'id' => $reminder->id,
                                    'title' => $reminder->title,
                                    'project' => $reminder->project ? $reminder->project->name : 'N/A',
                                    'services' => $services,
                                    'type' => ucfirst(str_replace('_', ' ', $reminder->type)),
                                    'date' => $currentOverdue->format('Y-m-d'),
                                    'display_date' => $this->formatDisplayDate($currentOverdue->format('Y-m-d'), $today),
                                    'time_of_day' => $timeOfDay,
                                    'frequency' => $reminder->frequency,
                                    'frequency_display' => "Daily at $timeOfDay",
                                    'status' => $status,
                                    'is_today' => $currentOverdue->isToday(),
                                    'is_overdue' => true,
                                ];
                            }
                            $currentOverdue->addDay();
                        }
                    } else if ($current >= $today) {
                        $thisWeek[] = [
                            'id' => $reminder->id,
                            'title' => $reminder->title,
                            'project' => $reminder->project ? $reminder->project->name : 'N/A',
                            'services' => $services,
                            'type' => ucfirst(str_replace('_', ' ', $reminder->type)),
                            'date' => $date,
                            'display_date' => $this->formatDisplayDate($date, $today),
                            'time_of_day' => $timeOfDay,
                            'frequency' => $reminder->frequency,
                            'frequency_display' => "Daily at $timeOfDay",
                            'status' => $status,
                            'is_today' => $isToday,
                            'is_overdue' => $isOverdue,
                        ];
                    }
                    $current->addDay();
                }

                if (!isset($dailyReminders[$reminder->id])) {
                    $date = $today->format('Y-m-d');
                    $key = $reminder->id . '-' . $date;
                    if (!isset($processedDates[$key])) {
                        $processedDates[$key] = true;
                        $statusRecord = $reminder->statuses->first(function ($status) use ($date) {
                            return $status->report_date->format('Y-m-d') === $date;
                        });
                        $status = $statusRecord ? $statusRecord->status : 'not_completed';
                        Log::info('Daily reminder status check for this_month', [
                            'reminder_id' => $reminder->id,
                            'report_date' => $date,
                            'status' => $status,
                            'status_record' => $statusRecord ? [
                                'id' => $statusRecord->id,
                                'report_date' => $statusRecord->report_date->format('Y-m-d'),
                                'status' => $statusRecord->status,
                            ] : null,
                        ]);
                        $isToday = true;
                        $isOverdue = $status === 'not_completed' && Carbon::parse($date, 'Asia/Kolkata')->isBefore($today);

                        $thisMonth[] = [
                            'id' => $reminder->id,
                            'title' => $reminder->title,
                            'project' => $reminder->project ? $reminder->project->name : 'N/A',
                            'services' => $services,
                            'type' => ucfirst(str_replace('_', ' ', $reminder->type)),
                            'date' => $date,
                            'display_date' => 'Today',
                            'time_of_day' => $timeOfDay,
                            'frequency' => $reminder->frequency,
                            'frequency_display' => "Daily at $timeOfDay",
                            'status' => $status,
                            'is_today' => $isToday,
                            'is_overdue' => $isOverdue,
                        ];
                        $dailyReminders[$reminder->id] = true;
                    }
                }
            } else {
                foreach ($reminderDates['week'] as $date) {
                    $key = $reminder->id . '-' . $date;
                    if (isset($processedDates[$key])) {
                        continue; // Skip duplicates
                    }
                    $processedDates[$key] = true;

                    $statusRecord = $reminder->statuses->first(function ($status) use ($date) {
                        return $status->report_date->format('Y-m-d') === $date;
                    });
                    $status = $statusRecord ? $statusRecord->status : 'not_completed';
                    Log::info('Reminder status check', [
                        'reminder_id' => $reminder->id,
                        'report_date' => $date,
                        'status' => $status,
                        'status_record' => $statusRecord ? [
                            'id' => $statusRecord->id,
                            'report_date' => $statusRecord->report_date->format('Y-m-d'),
                            'status' => $statusRecord->status,
                        ] : null,
                    ]);
                    $isToday = Carbon::parse($date, 'Asia/Kolkata')->isToday();
                    $isOverdue = $status === 'not_completed' && Carbon::parse($date, 'Asia/Kolkata')->isBefore($today);

                    // Include overdue tasks for every day from report_date until today or completed
                    if ($status === 'not_completed' && Carbon::parse($date, 'Asia/Kolkata')->isBefore($today)) {
                        $overdueDate = Carbon::parse($date, 'Asia/Kolkata');
                        $currentOverdue = $overdueDate->copy();
                        while ($currentOverdue <= $today && $status === 'not_completed') {
                            $overdueKey = $reminder->id . '-' . $currentOverdue->format('Y-m-d');
                            if (isset($processedDates[$overdueKey])) {
                                $currentOverdue->addDay();
                                continue; // Skip duplicates
                            }
                            $processedDates[$overdueKey] = true;

                            if ($currentOverdue >= $weekStart && $currentOverdue <= $weekEnd) {
                                $thisWeek[] = [
                                    'id' => $reminder->id,
                                    'title' => $reminder->title,
                                    'project' => $reminder->project ? $reminder->project->name : 'N/A',
                                    'services' => $services,
                                    'type' => ucfirst(str_replace('_', ' ', $reminder->type)),
                                    'date' => $currentOverdue->format('Y-m-d'),
                                    'display_date' => $this->formatDisplayDate($currentOverdue->format('Y-m-d'), $today),
                                    'time_of_day' => $timeOfDay,
                                    'frequency' => $reminder->frequency,
                                    'frequency_display' => $reminder->frequency === 'weekly'
                                        ? "Weekly on " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->format('l') . " at $timeOfDay"
                                        : ($reminder->frequency === 'monthly'
                                            ? "Monthly on day " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->day . " at $timeOfDay"
                                            : "One-time on " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->format('d M Y') . " at $timeOfDay"),
                                    'status' => $status,
                                    'is_today' => $currentOverdue->isToday(),
                                    'is_overdue' => true,
                                ];
                            }
                            if ($currentOverdue >= $monthStart && $currentOverdue <= $monthEnd && !isset($dailyReminders[$reminder->id . $date])) {
                                $thisMonth[] = [
                                    'id' => $reminder->id,
                                    'title' => $reminder->title,
                                    'project' => $reminder->project ? $reminder->project->name : 'N/A',
                                    'services' => $services,
                                    'type' => ucfirst(str_replace('_', ' ', $reminder->type)),
                                    'date' => $currentOverdue->format('Y-m-d'),
                                    'display_date' => $this->formatDisplayDate($currentOverdue->format('Y-m-d'), $today),
                                    'time_of_day' => $timeOfDay,
                                    'frequency' => $reminder->frequency,
                                    'frequency_display' => $reminder->frequency === 'weekly'
                                        ? "Weekly on " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->format('l') . " at $timeOfDay"
                                        : ($reminder->frequency === 'monthly'
                                            ? "Monthly on day " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->day . " at $timeOfDay"
                                            : "One-time on " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->format('d M Y') . " at $timeOfDay"),
                                    'status' => $status,
                                    'is_today' => $currentOverdue->isToday(),
                                    'is_overdue' => true,
                                ];
                                $dailyReminders[$reminder->id . $date] = true;
                            }
                            $currentOverdue->addDay();
                        }
                    } else {
                        $thisWeek[] = [
                            'id' => $reminder->id,
                            'title' => $reminder->title,
                            'project' => $reminder->project ? $reminder->project->name : 'N/A',
                            'services' => $services,
                            'type' => ucfirst(str_replace('_', ' ', $reminder->type)),
                            'date' => $date,
                            'display_date' => $this->formatDisplayDate($date, $today),
                            'time_of_day' => $timeOfDay,
                            'frequency' => $reminder->frequency,
                            'frequency_display' => $reminder->frequency === 'weekly'
                                ? "Weekly on " . Carbon::parse($date, 'Asia/Kolkata')->format('l') . " at $timeOfDay"
                                : ($reminder->frequency === 'monthly'
                                    ? "Monthly on day " . Carbon::parse($date, 'Asia/Kolkata')->day . " at $timeOfDay"
                                    : "One-time on " . Carbon::parse($date, 'Asia/Kolkata')->format('d M Y') . " at $timeOfDay"),
                            'status' => $status,
                            'is_today' => $isToday,
                            'is_overdue' => $isOverdue,
                        ];
                    }
                }

                foreach ($reminderDates['month'] as $date) {
                    $key = $reminder->id . '-' . $date;
                    if (isset($processedDates[$key])) {
                        continue; // Skip duplicates
                    }
                    $processedDates[$key] = true;

                    $statusRecord = $reminder->statuses->first(function ($status) use ($date) {
                        return $status->report_date->format('Y-m-d') === $date;
                    });
                    $status = $statusRecord ? $statusRecord->status : 'not_completed';
                    Log::info('Reminder status check', [
                        'reminder_id' => $reminder->id,
                        'report_date' => $date,
                        'status' => $status,
                        'status_record' => $statusRecord ? [
                            'id' => $statusRecord->id,
                            'report_date' => $statusRecord->report_date->format('Y-m-d'),
                            'status' => $statusRecord->status,
                        ] : null,
                    ]);
                    $isToday = Carbon::parse($date, 'Asia/Kolkata')->isToday();
                    $isOverdue = $status === 'not_completed' && Carbon::parse($date, 'Asia/Kolkata')->isBefore($today);

                    if ($status === 'not_completed' && Carbon::parse($date, 'Asia/Kolkata')->isBefore($today)) {
                        $overdueDate = Carbon::parse($date, 'Asia/Kolkata');
                        $currentOverdue = $overdueDate->copy();
                        while ($currentOverdue <= $today && $status === 'not_completed') {
                            $overdueKey = $reminder->id . '-' . $currentOverdue->format('Y-m-d');
                            if (isset($processedDates[$overdueKey])) {
                                $currentOverdue->addDay();
                                continue; // Skip duplicates
                            }
                            $processedDates[$overdueKey] = true;

                            if ($currentOverdue >= $monthStart && $currentOverdue <= $monthEnd) {
                                $thisMonth[] = [
                                    'id' => $reminder->id,
                                    'title' => $reminder->title,
                                    'project' => $reminder->project ? $reminder->project->name : 'N/A',
                                    'services' => $services,
                                    'type' => ucfirst(str_replace('_', ' ', $reminder->type)),
                                    'date' => $currentOverdue->format('Y-m-d'),
                                    'display_date' => $this->formatDisplayDate($currentOverdue->format('Y-m-d'), $today),
                                    'time_of_day' => $timeOfDay,
                                    'frequency' => $reminder->frequency,
                                    'frequency_display' => $reminder->frequency === 'weekly'
                                        ? "Weekly on " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->format('l') . " at $timeOfDay"
                                        : ($reminder->frequency === 'monthly'
                                            ? "Monthly on day " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->day . " at $timeOfDay"
                                            : "One-time on " . Carbon::parse($currentOverdue, 'Asia/Kolkata')->format('d M Y') . " at $timeOfDay"),
                                    'status' => $status,
                                    'is_today' => $currentOverdue->isToday(),
                                    'is_overdue' => true,
                                ];
                            }
                            $currentOverdue->addDay();
                        }
                    } else {
                        $thisMonth[] = [
                            'id' => $reminder->id,
                            'title' => $reminder->title,
                            'project' => $reminder->project ? $reminder->project->name : 'N/A',
                            'services' => $services,
                            'type' => ucfirst(str_replace('_', ' ', $reminder->type)),
                            'date' => $date,
                            'display_date' => $this->formatDisplayDate($date, $today),
                            'time_of_day' => $timeOfDay,
                            'frequency' => $reminder->frequency,
                            'frequency_display' => $reminder->frequency === 'weekly'
                                ? "Weekly on " . Carbon::parse($date, 'Asia/Kolkata')->format('l') . " at $timeOfDay"
                                : ($reminder->frequency === 'monthly'
                                    ? "Monthly on day " . Carbon::parse($date, 'Asia/Kolkata')->day . " at $timeOfDay"
                                    : "One-time on " . Carbon::parse($date, 'Asia/Kolkata')->format('d M Y') . " at $timeOfDay"),
                            'status' => $status,
                            'is_today' => $isToday,
                            'is_overdue' => $isOverdue,
                        ];
                    }
                }
            }
        }

        // Sort: is_overdue first, then is_today, then by date ascending
        $sortPriority = function ($a, $b) use ($today) {
            // Prioritize is_overdue
            if ($a['is_overdue'] !== $b['is_overdue']) {
                return $b['is_overdue'] <=> $a['is_overdue'];
            }
            // Then is_today
            if ($a['is_today'] !== $b['is_today']) {
                return $b['is_today'] <=> $a['is_today'];
            }
            // Then by date ascending
            return strcmp($a['date'], $b['date']);
        };
        usort($thisWeek, $sortPriority);
        usort($thisMonth, $sortPriority);

        $response = [
            'success' => true,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'message' => empty($thisWeek) && empty($thisMonth) ? 'No upcoming reminders found.' : null,
        ];
        Log::info('Upcoming reminders response', $response);
        return response()->json($response);
    }

    protected function formatDisplayDate($date, $today)
    {
        $carbonDate = Carbon::parse($date, 'Asia/Kolkata');
        if ($carbonDate->isToday()) {
            return 'Today';
        } elseif ($carbonDate->isTomorrow()) {
            return 'Tomorrow';
        }
        return $carbonDate->format('l, d F');
    }

    protected function getReminderDates($reminder, $weekStart, $weekEnd, $monthStart, $monthEnd)
    {
        $dates = ['week' => [], 'month' => []];
        $today = Carbon::today('Asia/Kolkata');

        if ($reminder->frequency === 'daily') {
            $current = $weekStart->copy();
            while ($current <= $weekEnd) {
                if ($current >= $today) {
                    $dates['week'][] = $current->format('Y-m-d');
                }
                $current->addDay();
            }
            if ($today >= $monthStart && $today <= $monthEnd) {
                $dates['month'][] = $today->format('Y-m-d');
            }
        } elseif ($reminder->frequency === 'weekly') {
            $daysOfWeek = $reminder->days_of_week ?? [];
            $current = $weekStart->copy();
            while ($current <= $weekEnd) {
                $dow = $current->dayOfWeek === 0 ? 7 : $current->dayOfWeek;
                if (in_array($dow, $daysOfWeek) && $current >= $today) {
                    $dates['week'][] = $current->format('Y-m-d');
                }
                $current->addDay();
            }
            $current = $monthStart->copy();
            while ($current <= $monthEnd) {
                $dow = $current->dayOfWeek === 0 ? 7 : $current->dayOfWeek;
                if (in_array($dow, $daysOfWeek) && $current >= $today) {
                    $dates['month'][] = $current->format('Y-m-d');
                }
                $current->addDay();
            }
        } elseif ($reminder->frequency === 'monthly') {
            $dayOfMonth = min($reminder->day_of_month ?? $monthEnd->day, $monthEnd->day);
            $targetDate = $monthStart->copy()->day($dayOfMonth);
            if ($targetDate >= $today && $targetDate <= $monthEnd) {
                $formattedDate = $targetDate->format('Y-m-d');
                $dates['month'][] = $formattedDate;
                if ($targetDate <= $weekEnd && $targetDate >= $weekStart) {
                    $dates['week'][] = $formattedDate;
                }
            }
        } elseif ($reminder->frequency === 'one_time') {
            $specificDate = Carbon::parse($reminder->specific_date, 'Asia/Kolkata');
            if ($specificDate >= $today && $specificDate <= $monthEnd) {
                $formattedDate = $specificDate->format('Y-m-d');
                $dates['month'][] = $formattedDate;
                if ($specificDate <= $weekEnd && $specificDate >= $weekStart) {
                    $dates['week'][] = $formattedDate;
                }
            }
        }

        // Deduplicate dates
        $dates['week'] = array_unique($dates['week']);
        $dates['month'] = array_unique($dates['month']);
        return $dates;
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        return view('reminders.create_modal', compact('projects', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'integer|exists:services,id',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,one_time',
            'time_of_day' => 'required|date_format:H:i',
            'days_of_week' => 'required_if:frequency,weekly|array',
            'days_of_week.*' => 'integer|min:1|max:7|distinct',
            'day_of_month' => 'required_if:frequency,monthly|nullable|integer|min:1|max:31',
            'specific_date' => 'required_if:frequency,one_time|nullable|date',
            'type' => 'required|in:report_submission,meeting,follow_up,other',
            'email_notifications' => 'required|in:0,1',
            'push_notifications' => 'required|in:0,1',
            'active' => 'required|in:0,1',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['time_of_day'] = $validated['time_of_day'] . ':00';

        $reminder = Reminder::create($validated);
        if ($request->service_ids) {
            $reminder->services()->sync($request->service_ids);
        }

        if ($validated['frequency'] === 'one_time' && Carbon::parse($validated['specific_date'], 'Asia/Kolkata')->isToday()) {
            $this->createNotification($reminder);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reminder created successfully!',
            'reminder' => $reminder->load('services'),
        ]);
    }

    public function edit(Reminder $reminder)
    {
        $projects = Project::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        return view('reminders.edit_modal', compact('reminder', 'projects', 'services'));
    }

    public function update(Request $request, Reminder $reminder)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'integer|exists:services,id',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,one_time',
            'time_of_day' => 'required|date_format:H:i',
            'days_of_week' => 'required_if:frequency,weekly|array',
            'days_of_week.*' => 'integer|min:1|max:7|distinct',
            'day_of_month' => 'required_if:frequency,monthly|nullable|integer|min:1|max:31',
            'specific_date' => 'required_if:frequency,one_time|nullable|date',
            'type' => 'required|in:report_submission,meeting,follow_up,other',
            'email_notifications' => 'required|in:0,1',
            'push_notifications' => 'required|in:0,1',
            'active' => 'required|in:0,1',
        ]);

        $validated['time_of_day'] = $validated['time_of_day'] . ':00';

        $reminder->update($validated);
        if ($request->service_ids) {
            $reminder->services()->sync($request->service_ids);
        } else {
            $reminder->services()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Reminder updated successfully!',
            'reminder' => $reminder->load('services'),
        ]);
    }

    public function destroy(Reminder $reminder)
    {
        try {
            $reminder->services()->detach();
            $reminder->statuses()->delete();
            $reminder->delete();
            return response()->json([
                'success' => true,
                'message' => 'Reminder deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reminder: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $reminder = Reminder::with(['project', 'services'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $reminder,
        ]);
    }

    public function updateStatus(Request $request, Reminder $reminder)
    {
        $today = Carbon::today('Asia/Kolkata');
        $validated = $request->validate([
            'report_date' => 'required|date',
            'status' => 'required|in:completed,not_completed',
        ]);

        $reportDate = Carbon::parse($validated['report_date'], 'Asia/Kolkata')->format('Y-m-d');

        // Allow updates for any report_date displayed today (including overdue tasks)
        $isValidDate = false;
        if ($reminder->frequency === 'daily') {
            $isValidDate = Carbon::parse($reportDate, 'Asia/Kolkata')->lte($today); // Allow any date up to today
        } elseif ($reminder->frequency === 'weekly') {
            $dow = Carbon::parse($reportDate, 'Asia/Kolkata')->dayOfWeek === 0 ? 7 : Carbon::parse($reportDate, 'Asia/Kolkata')->dayOfWeek;
            $isValidDate = in_array($dow, $reminder->days_of_week ?? []) && Carbon::parse($reportDate, 'Asia/Kolkata')->lte($today);
        } elseif ($reminder->frequency === 'monthly') {
            $dayOfMonth = min($reminder->day_of_month ?? Carbon::parse($reportDate, 'Asia/Kolkata')->endOfMonth()->day, Carbon::parse($reportDate, 'Asia/Kolkata')->endOfMonth()->day);
            $isValidDate = Carbon::parse($reportDate, 'Asia/Kolkata')->day == $dayOfMonth && Carbon::parse($reportDate, 'Asia/Kolkata')->lte($today);
        } elseif ($reminder->frequency === 'one_time') {
            $specificDate = Carbon::parse($reminder->specific_date, 'Asia/Kolkata');
            $isValidDate = $specificDate->isSameDay(Carbon::parse($reportDate, 'Asia/Kolkata')) && Carbon::parse($reportDate, 'Asia/Kolkata')->lte($today);
        }

        if (!$isValidDate) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid report date for this reminder.',
            ], 400);
        }

        // Create or update status record
        $status = ReminderStatus::updateOrCreate(
            ['reminder_id' => $reminder->id, 'report_date' => $reportDate],
            ['status' => $validated['status']]
        );

        Log::info('Status updated', [
            'reminder_id' => $reminder->id,
            'report_date' => $reportDate,
            'status' => $status->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reminder status updated successfully!',
            'status' => $status->status,
        ]);
    }

    protected function createNotification(Reminder $reminder)
    {
        $project = $reminder->project;
        $services = $reminder->services->isEmpty() ? '' : ' (' . $reminder->services->pluck('name')->implode(', ') . ')';
        $message = $reminder->message ?: "Reminder: {$reminder->type} for {$project->name}{$services}";

        Notification::create([
            'user_id' => $reminder->user_id,
            'title' => $reminder->title,
            'message' => $message,
            'type' => 'reminder',
            'project_id' => $reminder->project_id,
        ]);
    }
}
