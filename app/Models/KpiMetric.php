<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiMetric extends Model
{
    protected $fillable = ['user_id', 'metric_name', 'metric_value', 'measured_at'];

    protected $dates = ['measured_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
