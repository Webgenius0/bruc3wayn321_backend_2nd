<?php

namespace App\Models;

use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        "name",
        "image",
        "is_group",
        "created_by",
    ];

    public function creator()
    {
        return $this->belongsTo(config('reverb-chat.user_model'), 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(
            config('reverb-chat.user_model'),
            'chat_users'
        );
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }
}
