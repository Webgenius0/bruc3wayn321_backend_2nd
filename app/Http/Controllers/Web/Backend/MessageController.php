<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Chat;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $chats = Chat::with([
            'users',
            'lastMessage',
        ])
            ->withMax('messages', 'created_at')
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'is_group' => $chat->is_group,

                    'users' => $chat->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                            'avatar' => $user->avatar,
                        ];
                    })->values(),

                    'last_message' => $chat->lastMessage?->message,
                    'last_message_time' => $chat->lastMessage?->created_at,
                ];
            });

        $selectedChat = null;
        $messages = [];

        if ($request->has('chat_id')) {
            $selectedChatModel = Chat::with(['users'])->find($request->chat_id);
            if ($selectedChatModel) {
                $selectedChat = [
                    'id' => $selectedChatModel->id,
                    'is_group' => $selectedChatModel->is_group,
                    'users' => $selectedChatModel->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                            'avatar' => $user->avatar,
                        ];
                    })->values(),
                ];


                $messages = $selectedChatModel->messages()
                    ->with(['sender', 'attachments'])
                    ->latest()
                    ->get();

                // dd($messages);
            }
        }

        return Inertia::render('backend/message/messages', [
            'chats' => $chats,
            'selectedChat' => $selectedChat,
            'messages' => $messages,
        ]);
    }
}
