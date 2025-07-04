<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalMailAttachment extends Model
{
    protected $fillable = ['internal_mail_id', 'file_name', 'file_path'];

    public function internalMail()
    {
        return $this->belongsTo(InternalMail::class);
    }
}
