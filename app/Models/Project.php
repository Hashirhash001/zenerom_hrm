<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'requirements',
        'status',
        'onboarded_time',
        'payment_status',
        'payment_type',
        'project_owner_id'
    ];
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
    public function milestones()
    {
        return $this->hasMany(\App\Models\ProjectMilestone::class);
    }
   public function projectServices()
    {
        return $this->hasMany(\App\Models\ProjectService::class);
    }
    public function services()
    {
        return $this->hasManyThrough(
            \App\Models\Service::class,       // Final model we want (the Service model)
            \App\Models\ProjectService::class,  // Intermediate model (the pivot table model)
            'project_id',                      // Foreign key on project_services table referencing projects.id
            'id',                              // Foreign key on services table (services.id)
            'id',                              // Local key on projects table
            'service_id'                       // Local key on project_services table referencing services.id
        );
    }
    // In App\Models\Project.php
    public function servicessub()
    {
        return $this->hasMany(\App\Models\ProjectService::class, 'project_id');
    }

    public function projectMilestones()
    {
        return $this->hasMany(\App\Models\ProjectMilestone::class);
    }
    public function projectDescriptions()
    {
        return $this->hasMany(\App\Models\ProjectDescription::class, 'project_id');
    }
    public function updates()
    {
        return $this->hasMany(ProjectUpdate::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function client()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id'); // Adjust foreign key if different.
    }

   public function assignedStaff()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'project_owner_id');
    }




    public function owner()
    {
        // If project_owner_id references an employee (and not a user),
        // adjust the related model accordingly.
        return $this->belongsTo(\App\Models\Employee::class, 'project_owner_id');
    }

    // Relationship: A project has many documents.
    public function documents()
    {
        return $this->hasMany(ProjectDocument::class);
    }


    // Relationship: A project has many status history records.
    public function statusHistory()
    {
        return $this->hasMany(ProjectStatusHistory::class);
    }
    public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class, 'project_id');
    }

    // Optional: Relationship to Customer, Project Owner, etc.
}
