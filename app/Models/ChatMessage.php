<?php

namespace App\Models;

use App\Models\Chat;
use App\Models\ChatMessageAttachment;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        "chat_id",
        "sender_id",
        "message",
        "links",
        "type",
        "is_read",
    ];

    public function attachments()
    {
        return $this->hasMany(ChatMessageAttachment::class);
    }

    public function sender()
    {
        return $this->belongsTo(config('reverb-chat.user_model'), 'sender_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
