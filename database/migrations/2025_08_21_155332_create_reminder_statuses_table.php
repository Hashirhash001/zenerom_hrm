<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReminderStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('reminder_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reminder_id')->constrained()->onDelete('cascade');
            $table->date('report_date');
            $table->string('status')->default('not_completed');
            $table->timestamps();
            $table->unique(['reminder_id', 'report_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reminder_statuses');
    }
}
