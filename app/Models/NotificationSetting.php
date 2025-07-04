<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = ['user_id', 'email_notifications', 'sms_notifications', 'push_notifications'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
