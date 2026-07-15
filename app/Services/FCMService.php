<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials_file'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendMessage($token, $title, $body, $data = [])
    {
        // Firebase MessageData requires ALL values to be strings.
        // Cast every value here so callers never need to worry about types.
        $safeData = array_map(fn($v) => (string) $v, $data);

        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => array_merge($safeData, [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]),
        ];

        $this->messaging->send($message);
    }
}
