<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUpdate extends Model
{
    protected $fillable = [
        'project_id',
        'project_service_id',
        'title',
        'note',
        'entry_by',
        'assigned_to',
        'date',
        'status',
        'received_status'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectService()
    {
        return $this->belongsTo(ProjectService::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

}
