<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBreaksInStaffAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->dateTime('break_start')->nullable()->after('created_at');
            $table->dateTime('break_end')->nullable()->after('break_start');
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
            $table->dropColumn(['break_start', 'break_end']);
        });
    }
}
