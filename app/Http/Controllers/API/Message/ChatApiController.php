<?php

namespace App\Http\Controllers\API\Message;

use App\Events\MessageCount;
use App\Events\SendMessage;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Connection;
use App\Models\FirebaseToken;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatApiController extends Controller
{

    protected $push;

    public function __construct(FCMService $push)
    {
        $this->push = $push;
    }


    public function openOrCreate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items'   => 'nullable|integer|min:1|max:100',
        ]);

        $authId     = auth()->id();
        $targetUser = $request->user_id;
        $perPage    = $request->items ?? 20;

        /*
        |--------------------------------------------------------------------------
        | Check connection from both sides
        |--------------------------------------------------------------------------
        */

        $connected = Connection::where(function ($q) use ($authId, $targetUser) {
            $q->where('user_id', $authId)
                ->where('connected_user_id', $targetUser);
        })->orWhere(function ($q) use ($authId, $targetUser) {
            $q->where('user_id', $targetUser)
                ->where('connected_user_id', $authId);
        })->exists();

        if (! $connected) {
            return Helper::error('Users are not connected.', 403);
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Check existing private chat
            |--------------------------------------------------------------------------
            */

            $chat = Chat::where('is_group', false)
                ->whereHas('users', fn($q) => $q->where('user_id', $authId))
                ->whereHas('users', fn($q) => $q->where('user_id', $targetUser))
                ->withCount('users')
                ->having('users_count', 2)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Create chat if not exists
            |--------------------------------------------------------------------------
            */

            if (!$chat) {

                $chat = Chat::create([
                    'is_group'   => false,
                    'created_by' => $authId,
                ]);

                $chat->users()->attach([
                    $authId => [
                        'joined_at' => now()
                    ],

                    $targetUser => [
                        'joined_at' => now()
                    ],
                ]);
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Get messages
            |--------------------------------------------------------------------------
            */

            $messages = ChatMessage::where('chat_id', $chat->id)
                ->with([
                    'sender',
                    'attachments',
                ])
                ->orderByDesc('created_at')
                ->paginate($perPage);

            return Helper::successData([
                'chat'     => $chat,
                'messages' => $messages,
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /* ---------------------------------------------------
     | 1️⃣ Send message using CHAT ID
     ---------------------------------------------------*/

    public function sendByChat(Request $request, Chat $chat)
    {
        // 🔐 Must be chat member
        if (!$chat->users()->where('user_id', auth()->id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->sendMessage($request, $chat);
    }

    /* ---------------------------------------------------
     | 2️⃣ Send message using USER ID (private chat)
     ---------------------------------------------------*/
    public function sendByUser(Request $request, $userId)
    {
        $authId = auth()->id();

        if ($authId == $userId) {
            return Helper::success('Cannot message yourself', 422);
        }

        // Find existing private chat
        $chat = Chat::where('is_group', false)
            ->whereHas('users', fn($q) => $q->where('user_id', $authId))
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->withCount('users')
            ->having('users_count', 2)
            ->first();

        // Create if not exists
        if (!$chat) {
            DB::beginTransaction();

            try {
                $chat = Chat::create([
                    'is_group'   => false,
                    'created_by' => $authId,
                ]);

                $chat->users()->attach([
                    $authId => ['joined_at' => now()],
                    $userId => ['joined_at' => now()],
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return $this->sendMessage($request, $chat);
    }

    /* ---------------------------------------------------
     | Shared send logic
     ---------------------------------------------------*/
    private function sendMessage(Request $request, Chat $chat)
    {
        $validate = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'files.*' => 'file|max:20480',
        ]);

        if ($validate->fails()) {
            return Helper::validation($validate->errors()->first(), $validate->errors());
        }


        // Check if this is the first message
        $isFirstMessage = !$chat->messages()->exists();

        $message = ChatMessage::create([
            'chat_id'   => $chat->id,
            'sender_id' => auth()->id(),
            'message'   => $request->message,
            'type'      => $request->hasFile('files') ? 'files' : 'text',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('chat/attachments', 'public');

                $filePath = 'storage/' . $path;

                $message->attachments()->create([
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $message->load('attachments');

        SendMessage::dispatch($message, $message->chat_id ?? $message['chat_id']);
        MessageCount::dispatch(5);

        /*
        |--------------------------------------------------------------------------
        | Send Push Notification
        |--------------------------------------------------------------------------
        */

        // Receiver user
        $receiver = $chat->users()
            ->where('user_id', '!=', auth()->id())
            ->first();

        if ($receiver) {

            // Inactive check (example: offline more than 5 minutes)
            $isInactive = !$receiver->is_online ||
                optional($receiver->last_seen_at)->lt(now()->subMinutes(5));

            if ($isFirstMessage || $isInactive) {

                $tokens = FirebaseToken::where('user_id', $receiver->id)->get();

                foreach ($tokens as $token) {

                    if (!empty($token->token)) {

                        $this->push->sendMessage(
                            $token->token,
                            auth()->user()->first_name,
                            $request->message ?? 'Sent an attachment',
                            [
                                'type'      => 'chat',
                                'chat_id'   => (string) $chat->id,
                                'message_id' => (string) $message->id,
                                'sender_id' => (string) auth()->id(),
                            ]
                        );
                    }
                }
            }
        }


        return Helper::successData($message, 'message send successfully');
    }

    public function messagelist(Request $request, $id)
    {
        $request->validate([
            'items' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = $request->items ?? 20;

        $chat = Chat::find($id);

        if (!$chat) {
            return Helper::error('Chat not found', 404);
        }


        $authId = auth()->id();

        // Mark messages as read
        $message = ChatMessage::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $authId)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);

        $messages = ChatMessage::where('chat_id', $chat->id)
            ->with([
                'sender',
                'attachments',
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return Helper::successData([
            'chat'  => $chat,
            'messages' => $messages,
        ], 200);
    }

    public function chatList()
    {
        $authId = auth()->id();

        $chats = Chat::whereHas('users', function ($q) use ($authId) {
            $q->where('user_id', $authId);
        })
            ->with([
                'users:id,name,avatar',
                // latest message only
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                },
                'messages.sender:id,name,avatar',
                'messages.attachments',
            ])
            ->withCount([
                'messages as unread_count' => function ($q) use ($authId) {
                    $q->where('sender_id', '!=', $authId)
                        ->where('is_read', 0);
                }
            ])

            // order by last message OR fallback to chat created_at
            ->orderByDesc(
                ChatMessage::select('created_at')
                    ->whereColumn('chat_messages.chat_id', 'chats.id')
                    ->latest()
                    ->limit(1)
            )
            ->orderByDesc('chats.created_at')

            ->get();

        $data = [];
        $total = 0;

        foreach ($chats as $chat) {

            // get other user
            $otherUser = $chat->users->firstWhere('id', '!=', $authId);

            // get last message safely
            $lastMessage = $chat->messages->first();

            $data[] = [
                'chat_id' => $chat->id,
                'unread_count' => $chat->unread_count,
                'user' => $otherUser,
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->id,
                    'chat_id' => $lastMessage->chat_id,
                    'sender_id' => $lastMessage->sender_id,
                    'message' => $lastMessage->message,
                    'type' => $lastMessage->type,
                    'is_read' => $lastMessage->is_read,
                    'created_at' => $lastMessage->created_at,
                    'updated_at' => $lastMessage->updated_at,

                    'sender' => $lastMessage->sender,
                    'attachments' => $lastMessage->attachments,
                ] : null,
            ];

            $total += $chat->unread_count;
        }

        $items = [
            'unread' => $total,
            'data' => $data,
        ];


        return Helper::successData($items, 'Chat List', 200);
    }

    public function usermessagelist(Request $request, $userId)
    {
        $request->validate([
            'items' => 'nullable|integer|min:1|max:100',
        ]);

        $authId = auth()->id();

        $perPage = $request->items ?? 20;

        // Find private chat between users
        $chat = Chat::where('is_group', false)
            ->whereHas('users', fn($q) => $q->where('user_id', $authId))
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->withCount('users')
            ->having('users_count', 2)
            ->first();

        if (!$chat) {
            return Helper::error('Chat not found', 404);
        }

        // Get messages
        $messages = ChatMessage::where('chat_id', $chat->id)
            ->with([
                'sender',
                'attachments',
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        // Mark messages as read
        $message = ChatMessage::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $authId)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);



        return Helper::successData([
            'chat' => $chat,
            'messages' => $messages,
        ], 200);
    }
}
