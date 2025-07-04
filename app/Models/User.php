<?php

namespace App\Models;

use App\Models\LeaveRequest;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'id','user_id','name', 'email', 'password', 'role_id', 'department_id','status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function accessPrivileges()
    {
        return $this->hasMany(\App\Models\UserAccessPrivilege::class, 'user_id');
    }
    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function projectsOwned()
    {
        return $this->hasMany(Project::class, 'project_owner_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }
}
