<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'message',
        'frequency',
        'time_of_day',
        'days_of_week',
        'day_of_month',
        'specific_date',
        'type',
        'email_notifications',
        'push_notifications',
        'active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'reminder_service');
    }

    public function statuses()
    {
        return $this->hasMany(ReminderStatus::class);
    }
}
