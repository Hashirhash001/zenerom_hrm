<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields.
    protected $fillable = [
        'project_id',
        'service_id',
        'title',
        'description',
        'deadline',
        'status',
        'created_by',
    ];

    // Define relationship with Project.
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Define relationship with Service.
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function assignments()
    {
        return $this->hasMany(\App\Models\TaskAssigned::class, 'task_id');
    }
    
    public function staff()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'staff_id');
    }

    public function taskUsers()
    {
        return $this->hasMany(\App\Models\TaskUser::class, 'task_id');
    }
    public function creator()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'created_by');
    }

    
    



}
