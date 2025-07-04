<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationAttachment extends Model
{
    protected $fillable = ['conversation_id', 'file_name', 'file_path'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
