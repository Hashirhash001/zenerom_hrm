<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalWorkSecondsInStaffAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->bigInteger('total_work_seconds')->default(0)->after('mode');
            $table->timestamp('last_timer_start')->nullable()->after('total_work_seconds');
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
            $table->dropColumn(['total_work_seconds', 'last_timer_start']);
        });
    }
}
