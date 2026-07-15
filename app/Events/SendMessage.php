<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $message;
    public $chat_id;

    public function __construct($message, $chatId)
    {
        $this->message = $message;
        $this->chat_id = $chatId;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->chat_id);
    }

    // public function broadcastAs()
    // {
    //     return 'new-message';
    // }

}
