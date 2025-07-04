<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalMail extends Model
{
    protected $fillable = [
        'sender_id', 'receiver_id', 'subject', 'body', 'mail_status', 'system_ip'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function attachments()
    {
        return $this->hasMany(InternalMailAttachment::class);
    }
}
