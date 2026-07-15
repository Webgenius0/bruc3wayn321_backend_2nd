<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewMessageReceived;
use Illuminate\Support\Facades\Notification;
use Twopoint0\ReverbChat\Events\SendMessage;

class MessageSentListener
{
    public function handle(SendMessage $event): void
    {
        $message = $event->message;
        if (!$message) {
            return;
        }

        $chat = $message->chat;
        if (!$chat) {
            return;
        }

        // Get sender
        $sender = User::find($message->sender_id);
        if (!$sender) {
            return;
        }

        // Get recipients (all chat users except sender)
        $recipients = $chat->users()->where('users.id', '!=', $sender->id)->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new NewMessageReceived($message, $sender));
        }
    }
}
