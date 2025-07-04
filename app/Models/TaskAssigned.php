<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssigned extends Model
{
    use HasFactory;

    protected $table = 'task_assigned';

    protected $fillable = [
        'task_id',
        'staff_id',
        'date',
        'status',
        'created_by',
        'updated_by',
    ];

    public function staff()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'staff_id');
    }


    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_assigned_id');
    }

    public function documents()
    {
        return $this->hasMany(TaskDocument::class, 'task_assigned_id');
    }
     public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
    public function creator()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'created_by');
    }
}
