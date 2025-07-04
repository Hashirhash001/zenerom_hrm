<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDescriptionFile extends Model
{
    protected $table = 'project_description_files';

    protected $fillable = [
        'project_description_id',
        'file_name',
        'status',
        'entry_by',
        'entered_date',
    ];

    /**
     * Get the project description that owns this file.
     */
    public function projectDescription()
    {
        return $this->belongsTo(ProjectDescription::class);
    }
}
