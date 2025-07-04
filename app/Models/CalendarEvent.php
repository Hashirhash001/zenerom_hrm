<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'event_title', 'event_description', 'event_start', 'event_end', 'event_type', 'reference_id'
    ];

    protected $dates = ['event_start', 'event_end'];
}
