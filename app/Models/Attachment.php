<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['attachable_id', 'attachable_type', 'file_name', 'file_path'];

    public function attachable()
    {
        return $this->morphTo();
    }
}
