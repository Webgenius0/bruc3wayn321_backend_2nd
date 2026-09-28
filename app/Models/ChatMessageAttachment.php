<?php

namespace App\Models;

use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Model;

class ChatMessageAttachment extends Model
{
    protected $fillable = [
        'chat_message_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function getFilePathAttribute($value)
    {
        if (request()->is('api/*') && !empty($value)) {
            return asset($value);
        }

        return $value;
    }
}
