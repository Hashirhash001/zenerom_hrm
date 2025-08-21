<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Reminder;

class AddTypeAndDaysOfWeekToRemindersTable extends Migration
{
    public function up()
    {
        Schema::table('reminders', function (Blueprint $table) {
            // Add type column as ENUM
            $table->enum('type', ['report_submission', 'meeting', 'follow_up', 'other'])->default('other')->after('message');

            // Add days_of_week JSON column and drop day_of_week
            $table->json('days_of_week')->nullable()->after('time_of_day');
            $table->dropColumn('day_of_week');
        });

        // Migrate existing day_of_week data to days_of_week
        $reminders = Reminder::where('frequency', 'weekly')->whereNotNull('day_of_week')->get();
        foreach ($reminders as $reminder) {
            $reminder->days_of_week = [$reminder->day_of_week];
            $reminder->save();
        }
    }

    public function down()
    {
        Schema::table('reminders', function (Blueprint $table) {
            // Revert: Add day_of_week integer column and drop days_of_week
            $table->integer('day_of_week')->nullable()->after('time_of_day');
            $table->dropColumn('days_of_week');
            $table->dropColumn('type');
        });

        // Revert data: Take first day from days_of_week for weekly reminders
        $reminders = Reminder::where('frequency', 'weekly')->whereNotNull('days_of_week')->get();
        foreach ($reminders as $reminder) {
            $days = json_decode($reminder->days_of_week, true);
            $reminder->day_of_week = !empty($days) ? $days[0] : null;
            $reminder->save();
        }
    }
}
