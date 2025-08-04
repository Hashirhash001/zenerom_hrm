<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StaffAttendance;
use App\Models\Employee;

class AutoCheckout extends Command
{
    protected $signature = 'attendance:auto-checkout';
    protected $description = 'Automatically check out users who forgot to log out';

    public function handle()
    {
        $today = Carbon::today('Asia/Kolkata')->toDateString();
        $now = Carbon::now('Asia/Kolkata');
        $cutoffTime = Carbon::today('Asia/Kolkata')->setTime(23, 59, 59); // Midnight IST

        // Find all attendance records for today without logout
        $attendances = StaffAttendance::where('attendance_date', $today)
            ->whereNull('logout')
            ->get();

        foreach ($attendances as $attendance) {
            $employee = Employee::where('user_id', $attendance->user_id)->first();

            // Get required work seconds from employee schedule or default to 8 hours
            $requiredWorkSeconds = ($employee && $employee->work_start_time && $employee->work_end_time)
                ? Carbon::parse($employee->work_end_time, 'Asia/Kolkata')->diffInSeconds(Carbon::parse($employee->work_start_time, 'Asia/Kolkata')) - 3600 // Subtract lunch and buffer
                : 8 * 3600; // Default 8 hours
            $halfDaySeconds = $requiredWorkSeconds / 2;

            // Calculate total break seconds
            $totalBreakSeconds = 0;
            $breaks = DB::table('staff_breaks')
                ->where('attendance_id', $attendance->id)
                ->get();

            foreach ($breaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $totalBreakSeconds += Carbon::parse($break->break_end)->diffInSeconds(Carbon::parse($break->break_start));
                } elseif ($break->break_start && !$break->break_end) {
                    // Close active break at current time or cutoff
                    $breakEnd = $now->lessThanOrEqualTo($cutoffTime) ? $now : $cutoffTime;
                    $breakSeconds = $breakEnd->diffInSeconds(Carbon::parse($break->break_start));
                    $totalBreakSeconds += $breakSeconds;
                    DB::table('staff_breaks')
                        ->where('id', $break->id)
                        ->update([
                            'break_end' => $breakEnd,
                            'updated_at' => $now
                        ]);
                }
            }

            // Calculate total work seconds from check-in to cutoff or required hours
            $checkInTime = Carbon::parse($attendance->created_at, 'Asia/Kolkata');
            $workEndTime = $checkInTime->copy()->addSeconds($requiredWorkSeconds + $totalBreakSeconds);
            $effectiveEndTime = $workEndTime->lessThanOrEqualTo($cutoffTime) ? $workEndTime : $cutoffTime;

            $totalWorkSeconds = $effectiveEndTime->diffInSeconds($checkInTime) - $totalBreakSeconds;

            // Cap work hours to required work seconds if exceeded
            if ($totalWorkSeconds >= $requiredWorkSeconds) {
                $totalWorkSeconds = $requiredWorkSeconds;
                $effectiveEndTime = $checkInTime->copy()->addSeconds($requiredWorkSeconds + $totalBreakSeconds);
            }

            // Determine mode based on capped work hours
            $mode = $totalWorkSeconds >= $requiredWorkSeconds ? 'Work from office' : ($totalWorkSeconds >= $halfDaySeconds ? 'Half Day' : 'Leave');

            // Update attendance record
            StaffAttendance::where('id', $attendance->id)->update([
                'logout' => $effectiveEndTime,
                'total_work_seconds' => $totalWorkSeconds,
                'total_break_seconds' => $totalBreakSeconds,
                'mode' => $mode,
                'last_timer_start' => null,
                'updated_at' => $now,
                'notes' => 'Auto-checked out by system'
            ]);

            Log::info('Auto-checkout completed', [
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'total_work_seconds' => $totalWorkSeconds,
                'total_break_seconds' => $totalBreakSeconds,
                'mode' => $mode,
                'check_in' => $checkInTime->toDateTimeString(),
                'logout' => $effectiveEndTime->toDateTimeString()
            ]);
        }

        $this->info('Auto-checkout process completed.');
    }
}
