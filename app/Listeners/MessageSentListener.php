<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewMessageReceived;

class MessageSentListener
{
    /**
     * Handle the event.
     *
     * The chat package (twopoint0/reverb-chat) was removed.
     * This listener is preserved but inactive until a chat package is re-integrated.
     * To re-enable, re-add the package and register this listener in AppServiceProvider.
     */
    public function handle(object $event): void
    {
        if (!isset($event->message) || !$event->message) {
            return;
        }

        $message = $event->message;
        $chat = $message->chat ?? null;
        if (!$chat) {
            return;
        }

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
