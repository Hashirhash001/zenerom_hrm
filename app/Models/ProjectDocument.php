<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDocument extends Model
{
    protected $fillable = [
        'project_id', 'document_name', 'file_path', 'description'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
