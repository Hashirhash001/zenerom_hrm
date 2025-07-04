<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'activity', 'activity_date', 'ip_address'];

    protected $dates = ['activity_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
