<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = ['project_id', 'title', 'agenda', 'meeting_time', 'location'];

    protected $dates = ['meeting_time'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
