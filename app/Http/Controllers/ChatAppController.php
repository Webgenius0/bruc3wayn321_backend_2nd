<?php

namespace App\Http\Controllers;

use App\Events\SendMessage;
use Illuminate\Http\Request;

class ChatAppController extends Controller
{
    public function chatroom(Request $request)
    {
        // dd($request->all());
        $username = $request->name;
        return view('chatroom.chat', ['username' => $username]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'user' => 'required|string',
            'message' => 'required|string',
        ]);

        $message = [
            'body' => $request->message,
            'chat_id' => 1,
        ];

        SendMessage::dispatch($message, $message['chat_id']);

        return response()->json(['status' => 'success']);
    }
}
