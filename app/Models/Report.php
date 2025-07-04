<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['report_name', 'report_type', 'generated_at', 'file_path', 'description'];

    protected $dates = ['generated_at'];
}
