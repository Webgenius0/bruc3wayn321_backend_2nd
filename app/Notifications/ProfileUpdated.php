<?php

namespace App\Notifications;

use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProfileUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {}

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
                \Log::error("Push notification failed for ProfileUpdated: " . $e->getMessage());
            }
        }

        return ['database'];
    }

    public function getTitle(): string
    {
        return 'Profile Updated';
    }

    public function getMessage(): string
    {
        return "Your profile details have been updated successfully. You can review your changes in your account settings.";
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'type' => 'profile_update',
            'metadata' => [],
        ];
    }
}
