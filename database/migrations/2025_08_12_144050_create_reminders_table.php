<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'one_time']);
            $table->time('time_of_day');
            $table->json('days_of_week')->nullable();
            $table->integer('day_of_month')->nullable();
            $table->date('specific_date')->nullable();
            $table->enum('type', ['report_submission', 'meeting', 'follow_up', 'other']);
            $table->boolean('email_notifications')->default(false);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
