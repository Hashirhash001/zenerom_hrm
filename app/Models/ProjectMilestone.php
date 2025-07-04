<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    protected $fillable = [
        'project_id',
        'project_service_id',
        'title',
        'description',
        'due_date',
        'status'
    ];

    // A milestone belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Optionally, if milestones are linked to a specific project service:
    public function projectService()
    {
        return $this->belongsTo(ProjectService::class);
    }
}
