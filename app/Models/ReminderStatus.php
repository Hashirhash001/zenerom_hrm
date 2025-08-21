<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderStatus extends Model
{
    protected $fillable = ['reminder_id', 'report_date', 'status'];

    protected $casts = [
        'report_date' => 'date:Y-m-d',
        'status' => 'string',
    ];

    public function reminder()
    {
        return $this->belongsTo(Reminder::class);
    }
}
