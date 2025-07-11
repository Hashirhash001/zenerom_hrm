<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalBreakSecondsToStaffAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->unsignedBigInteger('total_break_seconds')->nullable()->default(0)->after('total_work_seconds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->dropColumn('total_break_seconds');
        });
    }
}
