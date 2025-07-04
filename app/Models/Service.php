<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'details',
        'department_id',
        'status'
    ];

    // Optionally, define a relationship to Department if needed:
    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }
}
