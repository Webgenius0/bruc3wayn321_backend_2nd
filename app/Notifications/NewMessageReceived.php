<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NewMessageReceived extends Notification
{
    use Queueable;

    public function __construct(
        protected object $chatMessage,
        protected User $sender
    ) {}

    public function via(object $notifiable): array
    {
        if ($notifiable->push_notifications && $notifiable->message_notifications) {
            try {
                app(PushNotificationService::class)->sendToUser(
                    $notifiable,
                    $this->getTitle(),
                    $this->getMessage(),
                    [
                        'chat_id'    => $this->chatMessage->chat_id,
                        'message_id' => $this->chatMessage->id,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error("Push notification failed for NewMessageReceived: " . $e->getMessage());
            }
        }

        return ['database'];
    }

    public function getTitle(): string
    {
        return 'New Message';
    }

    public function getMessage(): string
    {
        return "{$this->sender->name} sent you a message: \"{$this->chatMessage->message}\"";
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'type' => 'message',
            'metadata' => [
                'chat_id' => $this->chatMessage->chat_id,
                'message_id' => $this->chatMessage->id,
                'sender_id' => $this->sender->id,
            ],
        ];
    }
}
