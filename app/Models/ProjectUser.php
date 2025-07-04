<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    // Specify the table if it doesn't follow Laravel's naming convention
    protected $table = 'project_users';

    // Define fillable attributes to allow mass assignment
    protected $fillable = [
        'project_id',
        'project_service_id',
        'user_id',
        'status',
        'date_time'
    ];

    // Optionally, define relationships if needed

    /**
     * A project user record belongs to a project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * A project user record belongs to a user (staff/employee).
     */
    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }
    
    /**
     * Optionally, if the record is related to a task.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
