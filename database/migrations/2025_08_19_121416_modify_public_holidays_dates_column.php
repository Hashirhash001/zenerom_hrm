<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PublicHoliday;

class ModifyPublicHolidaysDatesColumn extends Migration
{
    public function up()
    {
        // Add new 'dates' JSON column
        Schema::table('public_holidays', function (Blueprint $table) {
            $table->json('dates')->nullable()->after('name');
        });

        // Migrate existing 'date' to 'dates'
        PublicHoliday::all()->each(function ($holiday) {
            if ($holiday->date) {
                $holiday->dates = json_encode([$holiday->date->format('Y-m-d')]);
                $holiday->save();
            }
        });

        // Drop old 'date' column
        Schema::table('public_holidays', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    public function down()
    {
        // Add back 'date' column
        Schema::table('public_holidays', function (Blueprint $table) {
            $table->date('date')->after('name');
        });

        // Migrate 'dates' back to 'date' (take first date)
        PublicHoliday::all()->each(function ($holiday) {
            if ($holiday->dates) {
                $dates = json_decode($holiday->dates, true);
                $holiday->date = $dates[0] ?? null;
                $holiday->save();
            }
        });

        // Drop 'dates' column
        Schema::table('public_holidays', function (Blueprint $table) {
            $table->dropColumn('dates');
        });
    }
}
