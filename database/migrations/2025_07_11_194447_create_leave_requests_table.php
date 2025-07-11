<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('team_lead_status')->default('Submitted');
            $table->unsignedBigInteger('team_lead_approved_by')->nullable();
            $table->dateTime('team_lead_approved_at')->nullable();
            $table->text('team_lead_comments')->nullable();
            $table->string('hr_status')->default('Submitted');
            $table->unsignedBigInteger('hr_approved_by')->nullable();
            $table->dateTime('hr_approved_at')->nullable();
            $table->text('hr_comments')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('team_lead_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hr_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
}
