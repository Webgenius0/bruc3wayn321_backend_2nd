<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PostLiked extends Notification
{
    use Queueable;

    public function __construct(
        protected Post $post,
        protected User $liker
    ) {}

    public function via(object $notifiable): array
    {
        if ($notifiable->push_notifications && $notifiable->like_notifications) {
            try {
                app(PushNotificationService::class)->sendToUser(
                    $notifiable,
                    $this->getTitle(),
                    $this->getMessage()
                );
            } catch (\Throwable $e) {
                Log::error("Push notification failed for PostLiked: " . $e->getMessage());
            }
        }
        // Log::info($this->post);
        // Log::warning("Liker" . $this->liker);

        return ['database'];
    }

    public function getTitle(): string
    {
        return 'Post Liked';
    }

    public function getMessage(): string
    {
        return "{$this->liker->first_name} liked your post.";
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'type' => 'like',
            'metadata' => [
                'post_id' => $this->post->id,
                'post_slug' => $this->post->slug,
                'liker_id' => $this->liker->id,
            ],
        ];
    }
}
