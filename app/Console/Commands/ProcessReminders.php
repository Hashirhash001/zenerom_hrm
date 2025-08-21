<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessReminders extends Command
{
    protected $signature = 'reminders:process';
    protected $description = 'Process active reminders and generate notifications based on their frequency';

    public function handle()
    {
        $today = Carbon::now('Asia/Kolkata');
        $currentTime = $today->format('H:i'); // Format without seconds
        // Map Carbon's dayOfWeek (0=Sunday, 1=Monday, ..., 6=Saturday) to 1=Monday, ..., 7=Sunday
        $currentDayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek;
        $currentDayOfMonth = $today->day;
        $lastDayOfMonth = $today->endOfMonth()->day;

        Log::info('Starting reminder processing', [
            'current_time' => $today->toDateTimeString(),
            'formatted_time' => $currentTime,
            'day_of_week' => $currentDayOfWeek,
            'day_of_month' => $currentDayOfMonth,
            'last_day_of_month' => $lastDayOfMonth,
            'timezone' => 'Asia/Kolkata',
        ]);

        $reminders = Reminder::where('active', true)->with(['project', 'service'])->get();

        Log::info('Fetched active reminders', [
            'count' => $reminders->count(),
        ]);

        if ($reminders->isEmpty()) {
            Log::info('No active reminders found to process');
            $this->info('No active reminders to process.');
            return;
        }

        foreach ($reminders as $reminder) {
            Log::debug('Checking reminder', [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'frequency' => $reminder->frequency,
                'time_of_day' => $reminder->time_of_day,
                'project_id' => $reminder->project_id,
                'service_id' => $reminder->service_id ?? 'N/A',
                'user_id' => $reminder->user_id,
                'days_of_week' => $reminder->days_of_week ?? 'N/A',
                'day_of_month' => $reminder->day_of_month ?? 'N/A',
                'specific_date' => $reminder->specific_date ?? 'N/A',
                'type' => $reminder->type,
            ]);

            // Normalize reminder time to exclude seconds for comparison
            $reminderTime = Carbon::createFromFormat('H:i:s', $reminder->time_of_day, 'Asia/Kolkata')->format('H:i');

            // Check if the current time matches the reminder's time_of_day
            if ($reminderTime !== $currentTime) {
                Log::debug('Skipped reminder due to time mismatch', [
                    'id' => $reminder->id,
                    'expected_time' => $reminderTime,
                    'current_time' => $currentTime,
                    'raw_reminder_time' => $reminder->time_of_day,
                ]);
                continue;
            }

            Log::debug('Time match confirmed, checking frequency', [
                'id' => $reminder->id,
                'frequency' => $reminder->frequency,
            ]);

            $shouldTrigger = false;

            // Determine if the reminder should trigger based on frequency
            if ($reminder->frequency === 'daily') {
                $shouldTrigger = true;
                Log::debug('Daily reminder triggered', ['id' => $reminder->id]);
            } elseif ($reminder->frequency === 'weekly') {
                if ($reminder->days_of_week === null || empty($reminder->days_of_week)) {
                    Log::warning('Weekly reminder missing days_of_week', ['id' => $reminder->id]);
                } elseif (in_array($currentDayOfWeek, $reminder->days_of_week)) {
                    $shouldTrigger = true;
                    Log::debug('Weekly reminder triggered', [
                        'id' => $reminder->id,
                        'days_of_week' => $reminder->days_of_week,
                        'current_day_of_week' => $currentDayOfWeek,
                    ]);
                } else {
                    Log::debug('Weekly reminder not triggered', [
                        'id' => $reminder->id,
                        'days_of_week' => $reminder->days_of_week,
                        'current_day_of_week' => $currentDayOfWeek,
                    ]);
                }
            } elseif ($reminder->frequency === 'monthly') {
                if ($reminder->day_of_month === null) {
                    Log::warning('Monthly reminder missing day_of_month', ['id' => $reminder->id]);
                } else {
                    // Trigger on the specified day or the last day of the month if the specified day is invalid
                    $targetDay = min($reminder->day_of_month, $lastDayOfMonth);
                    if ($currentDayOfMonth == $targetDay) {
                        $shouldTrigger = true;
                        Log::debug('Monthly reminder triggered', [
                            'id' => $reminder->id,
                            'day_of_month' => $reminder->day_of_month,
                            'target_day' => $targetDay,
                            'current_day_of_month' => $currentDayOfMonth,
                        ]);
                    } else {
                        Log::debug('Monthly reminder not triggered', [
                            'id' => $reminder->id,
                            'target_day' => $targetDay,
                            'current_day_of_month' => $currentDayOfMonth,
                        ]);
                    }
                    if ($targetDay != $reminder->day_of_month) {
                        Log::warning('Adjusted monthly reminder day due to month length', [
                            'id' => $reminder->id,
                            'original_day' => $reminder->day_of_month,
                            'adjusted_day' => $targetDay,
                            'last_day_of_month' => $lastDayOfMonth,
                        ]);
                    }
                }
            } elseif ($reminder->frequency === 'one_time') {
                if ($reminder->specific_date === null) {
                    Log::warning('One-time reminder missing specific_date', ['id' => $reminder->id]);
                } else {
                    try {
                        $specificDate = Carbon::parse($reminder->specific_date, 'Asia/Kolkata');
                        if ($specificDate->isToday()) {
                            $shouldTrigger = true;
                            Log::debug('One-time reminder triggered', [
                                'id' => $reminder->id,
                                'specific_date' => $reminder->specific_date,
                            ]);
                            $reminder->update(['active' => false]);
                            Log::info('Deactivated one-time reminder', ['id' => $reminder->id]);
                        } else {
                            Log::debug('One-time reminder not triggered', [
                                'id' => $reminder->id,
                                'specific_date' => $reminder->specific_date,
                                'today' => $today->toDateString(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Invalid specific_date for one-time reminder', [
                            'id' => $reminder->id,
                            'specific_date' => $reminder->specific_date,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } else {
                Log::warning('Invalid frequency for reminder', [
                    'id' => $reminder->id,
                    'frequency' => $reminder->frequency,
                ]);
            }

            if ($shouldTrigger) {
                if (!$reminder->project) {
                    Log::warning('Reminder missing project relationship', ['id' => $reminder->id]);
                    continue;
                }

                Log::info('Triggering reminder notification', ['id' => $reminder->id]);

                $project = $reminder->project;
                $service = $reminder->service;
                $message = $reminder->message ?: "Reminder: {$reminder->type} for {$project->name}" . ($service ? " ({$service->name})" : '');

                try {
                    $notification = Notification::create([
                        'user_id' => $reminder->user_id,
                        'title' => $reminder->title,
                        'message' => $message,
                        'type' => 'reminder',
                        'project_id' => $reminder->project_id,
                        'service_id' => $reminder->service_id,
                    ]);

                    Log::info('Notification created', [
                        'notification_id' => $notification->id,
                        'reminder_id' => $reminder->id,
                        'title' => $reminder->title,
                        'message' => $message,
                        'user_id' => $reminder->user_id,
                    ]);

                    $this->info("Notification created for reminder ID {$reminder->id}: {$reminder->title}");
                } catch (\Exception $e) {
                    Log::error('Failed to create notification for reminder', [
                        'id' => $reminder->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::debug('Reminder not triggered after frequency check', ['id' => $reminder->id]);
            }
        }

        Log::info('Reminder processing completed', [
            'processed_count' => $reminders->count(),
            'end_time' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
        ]);

        $this->info('Reminders processed successfully.');
    }
}
