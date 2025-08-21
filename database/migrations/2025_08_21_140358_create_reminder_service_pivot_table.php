<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Reminder;
use Illuminate\Support\Facades\DB;

class CreateReminderServicePivotTable extends Migration
{
    public function up(): void
    {
        // Create the reminder_service pivot table
        Schema::create('reminder_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reminder_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Drop the service_id column from reminders table
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
    }

    public function down(): void
    {
        // Recreate the service_id column
        Schema::table('reminders', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null')->after('project_id');
        });

        // Drop the reminder_service table
        Schema::dropIfExists('reminder_service');
    }
};
