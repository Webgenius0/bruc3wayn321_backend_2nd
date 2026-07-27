<?php

namespace App\Http\Controllers;

use App\Events\SendMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatAppController extends Controller
{
    // Renders the name entry page
    public function enterName()
    {
        return Inertia::render('chat/enter-name');
    }

    // Renders the chat room
    public function chatroom(Request $request)
    {
        $name = $request->input('name') ?? $request->session()->get('chat_name');

        if (!$name) {
            return redirect()->route('chat.enter-name');
        }

        // Save name to session to persist reloads
        $request->session()->put('chat_name', $name);

        return Inertia::render('chat/room', [
            'username' => $name,
            'chatId' => 1 // Global chat for now
        ]);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'sender'  => 'required|string|max:50',
            'message' => 'required|string|max:2000',
            'chat_id' => 'required|integer|exists:chats,id',
        ]);
        $message = [
            'chat_id' => $request->chat_id ?? 1,
            'sender_id' => auth()->id() ?? '1',
            'message' => 'Hello there, Message from chat id please visit https://impronay.xyz and http://telegram.com/',
            'links' => [
                [
                    'url' => 'https://impronay.xyz',
                    'title' => 'Portfolio',
                    'description' => null,
                    'thumbnail' => null,
                ],
                [
                    'url' => 'http://telegram.com/',
                    'title' => 'Worcester Telegram : Local News, Politics & Sports in Worcester, MA',
                    'description' => null,
                    'thumbnail' => null,
                ]
            ],
            'type' => 'text',
            'updated_at' => '2026-07-23T06:45:20.000000Z',
            'created_at' => '2026-07-23T06:45:20.000000Z',
            'id' => 53,
            'attachments' => [],
        ];

        broadcast(new SendMessage($message, $validated['chat_id'] ?? 8))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message broadcast successfully.',
            'data'    => $message,
        ]);
    }
}
