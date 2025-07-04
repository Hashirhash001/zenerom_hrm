<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatusHistory extends Model
{
    protected $fillable = [
        'project_id',
        'old_status',
        'new_status',
        'changed_by'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
