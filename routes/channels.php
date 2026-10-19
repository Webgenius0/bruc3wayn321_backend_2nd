<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return Chat::where('id', $chatId)
        ->whereHas('users', fn($q) => $q->where('users.id', $user->id))
        ->exists();
});
