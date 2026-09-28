<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PostCommented extends Notification
{
    use Queueable;

    public function __construct(
        protected Post $post,
        protected User $commenter,
        protected string $commentText
    ) {}

    public function via(object $notifiable): array
    {
        if ($notifiable->push_notifications) {
            try {
                app(PushNotificationService::class)->sendToUser(
                    $notifiable,
                    $this->getTitle(),
                    $this->getMessage()
                );
            } catch (\Throwable $e) {
                Log::error("Push notification failed for PostCommented: " . $e->getMessage());
            }
        }

        return ['database'];
    }

    public function getTitle(): string
    {
        return 'New Comment';
    }

    public function getMessage(): string
    {
        return "{$this->commenter->name} commented: \"{$this->commentText}\"";
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'type' => 'comment',
            'metadata' => [
                'post_id' => $this->post->id,
                'post_slug' => $this->post->slug,
                'commenter_id' => $this->commenter->id,
            ],
        ];
    }
}
