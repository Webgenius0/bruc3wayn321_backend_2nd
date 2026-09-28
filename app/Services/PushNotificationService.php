<?php

namespace App\Services;

use App\Models\User;
use App\Models\FirebaseToken;
use App\Services\FCMService;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function __construct(
        protected FCMService $fcmService
    ) {}

    /**
     * Send push notification to a user's registered devices.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        // Respect the user's push notification settings preference
        if (!$user->push_notifications) {
            return;
        }

        $tokens = FirebaseToken::where('user_id', $user->id)
            ->pluck('token');

        // dd($user->id);

        foreach ($tokens as $token) {
            try {
                $this->fcmService->sendMessage(
                    $token,
                    $title,
                    $body,
                    $data
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to send push notification to user {$user->id} token {$token}: {$e->getMessage()}");
            }
        }
    }
}
