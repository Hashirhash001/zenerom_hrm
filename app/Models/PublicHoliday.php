<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    protected $fillable = ['name', 'dates', 'year', 'role_ids'];
    protected $casts = [
        'dates' => 'array',
        'year' => 'integer',
        'role_ids' => 'array',
    ];

    public function getRoleIdsAttribute($value)
    {
        return is_string($value) ? (json_decode($value, true) ?? []) : ($value ?? []);
    }

    public function getDatesAttribute($value)
    {
        return is_string($value) ? (json_decode($value, true) ?? []) : ($value ?? []);
    }
}
