<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDescription extends Model
{
    protected $table = 'project_descriptions';

    protected $fillable = [
        'project_id',
        'project_service_id',
        'title',
        'details',
        'status',
        'entered_date',
    ];

    // A description belongs to a project.
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Optionally, if linked to a service:
    public function projectService()
    {
        return $this->belongsTo(ProjectService::class);
    }

    // A description can have many files.
    public function files()
    {
        return $this->hasMany(ProjectDescriptionFile::class);
    }
}
