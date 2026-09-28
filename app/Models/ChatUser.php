<?php

namespace App\Models;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Model;

class ChatUser extends Model
{
    protected $fillable = [
        "chat_id",
        "user_id",
        "is_admin",
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(config('reverb-chat.user_model'));
    }
}
