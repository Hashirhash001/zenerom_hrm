<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicHolidaysTable extends Migration
{
    public function up()
    {
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->date('date');
            $table->year('year'); // Ensure year is an integer
            $table->json('role_ids')->nullable();
            $table->timestamps();

            $table->index(['date', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('public_holidays');
    }
}
