<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    use HasFactory;

    protected $table = 'task_user';

    protected $fillable = [
        'task_id',
        'user_id',
        'type', // or 'assignment_type' if you renamed it
        'assigned_at'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function staff()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'user_id');
    }

}
