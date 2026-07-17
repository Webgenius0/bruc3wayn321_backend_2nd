<?php

namespace App\Http\Controllers\API\Message;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\UserBlock;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Events\SendMessage;
use App\Helpers\Helper as Helper;

class ChatApiController extends Controller
{
    public function openOrCreate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items'   => 'nullable|integer|min:1|max:100',
        ]);

        $authId = auth()->id();
        $targetUser = $request->user_id;
        $perPage = $request->items ?? 20;

        DB::beginTransaction();

        try {

            // Check existing private chat
            $chat = Chat::where('is_group', false)
                ->whereHas('users', fn($q) => $q->where('user_id', $authId))
                ->whereHas('users', fn($q) => $q->where('user_id', $targetUser))
                ->withCount('users')
                ->having('users_count', 2)
                ->first();

            // Create chat if not exists
            if (!$chat) {
                $chat = Chat::create([
                    'is_group'   => false,
                    'created_by' => $authId,
                ]);

                $chat->users()->attach([
                    $authId => ['joined_at' => now()],
                    $targetUser => ['joined_at' => now()],
                ]);
            }

            DB::commit();

            // Get messages ONLY (latest first)
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
        // Must be chat member
        if (!$chat->users()->where('user_id', auth()->id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the other user has blocked auth user or auth user blocked them
        $authId     = auth()->id();
        $otherUser  = $chat->users()->where('user_id', '!=', $authId)->first();

        if ($otherUser) {
            $isBlocked = UserBlock::where(function ($q) use ($authId, $otherUser) {
                $q->where('user_id', $authId)->where('blocked_user_id', $otherUser->id);
            })->orWhere(function ($q) use ($authId, $otherUser) {
                $q->where('user_id', $otherUser->id)->where('blocked_user_id', $authId);
            })->exists();

            if ($isBlocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot send messages to this user.',
                ], 403);
            }
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
            return Helper::successData('Cannot message yourself', 422);
        }

        // Block check — prevent sending if either side has blocked
        $isBlocked = UserBlock::where(function ($q) use ($authId, $userId) {
            $q->where('user_id', $authId)->where('blocked_user_id', $userId);
        })->orWhere(function ($q) use ($authId, $userId) {
            $q->where('user_id', $userId)->where('blocked_user_id', $authId);
        })->exists();

        if ($isBlocked) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot send messages to this user.',
            ], 403);
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
                    $authId  => ['joined_at' => now()],
                    $userId  => ['joined_at' => now()],
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
     | 🔁 Shared send logic
     ---------------------------------------------------*/
    private function sendMessage(Request $request, Chat $chat)
    {
        $validate = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'files.*' => 'file|max:20480',
        ]);


        if ($validate->fails()) {
            return Helper::validation($validate->errors());
        }

        $links = [];

        preg_match_all('/https?:\/\/[^\s]+/i', $request->message, $matches);

        foreach ($matches[0] ?? [] as $url) {

            $title = null;
            $description = null;
            $thumbnail = null;

            try {
                $html = file_get_contents($url);

                preg_match('/<title>(.*?)<\/title>/si', $html, $titleMatch);
                $title = $titleMatch[1] ?? null;

                preg_match(
                    '/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)/i',
                    $html,
                    $descMatch
                );
                $description = $descMatch[1] ?? null;

                preg_match(
                    '/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)/i',
                    $html,
                    $imageMatch
                );
                $thumbnail = $imageMatch[1] ?? null;
            } catch (\Exception $e) {
            }

            $links[] = [
                'url' => $url,
                'title' => $title,
                'description' => $description,
                'thumbnail' => $thumbnail,
            ];
        }

        // dd($request->all());

        $message = ChatMessage::create([
            'chat_id'   => $chat->id,
            'sender_id' => auth()->id(),
            'message'   => $request->message,
            'links' => !empty($links)
                ? json_encode($links)
                : null,
            'type'      => $request->hasFile('files') ? 'files' : 'text',
        ]);



        if ($request->hasFile('files')) {
            // dd($request->all());
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

        // dd($message);

        SendMessage::dispatch($message, $message->chat_id ?? $message['chat_id']);

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


        $messages = ChatMessage::where('chat_id', $chat->id)
            ->with([
                'sender',
                'attachments',
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage);



        $authUser = auth()->user();

        // Other participant (for private chat)
        $otherUser = $chat->users
            ->where('id', '!=', $authUser->id)
            ->first();

        $iBlockedThem = false;
        $theyBlockedMe = false;

        if ($otherUser) {
            $iBlockedThem = UserBlock::where([
                'user_id' => $authUser->id,
                'blocked_user_id' => $otherUser->id,
            ])->exists();

            $theyBlockedMe = UserBlock::where([
                'user_id' => $otherUser->id,
                'blocked_user_id' => $authUser->id,
            ])->exists();
        }

        $chat['i_blocked_them'] = $iBlockedThem;
        $chat['they_blocked_me'] = $theyBlockedMe;
        $chat['can_send_message'] = !($iBlockedThem || $theyBlockedMe);

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
                'users:id,first_name,last_name,avatar',

                // latest message only
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                },

                'messages.sender:id,first_name,last_name,avatar',
                'messages',
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

        // Pre-fetch all block relationships for auth user in one query
        $blockedByMe   = UserBlock::where('user_id', $authId)->pluck('blocked_user_id')->toArray();
        $blockedByOther = UserBlock::where('blocked_user_id', $authId)->pluck('user_id')->toArray();

        foreach ($chats as $chat) {

            // get other user
            $otherUser = $chat->users->firstWhere('id', '!=', $authId);

            // get last message safely
            $lastMessage = $chat->messages->first();

            // check block status
            $otherUserId      = $otherUser ? $otherUser->id : null;
            $iBlockedThem     = $otherUserId && in_array($otherUserId, $blockedByMe);
            $theyBlockedMe    = $otherUserId && in_array($otherUserId, $blockedByOther);
            $isBlocked        = $iBlockedThem || $theyBlockedMe;

            $data[] = [
                'chat_id'          => $chat->id,
                'user'             => $otherUser,
                'is_blocked'       => $isBlocked,
                'i_blocked_them'   => $iBlockedThem,
                'they_blocked_me'  => $theyBlockedMe,
                'can_send_message' => !$isBlocked,
                'last_message' => $lastMessage ? [
                    'id'         => $lastMessage->id,
                    'chat_id'    => $lastMessage->chat_id,
                    'sender_id'  => $lastMessage->sender_id,
                    'message'    => $lastMessage->message,
                    'type'       => $lastMessage->type,
                    'is_read'    => $lastMessage->is_read,
                    'created_at' => Carbon::parse($lastMessage->created_at)->format('h:i A'),
                    'updated_at' => Carbon::parse($lastMessage->updated_at)->format('h:i A'),

                    'sender' => $lastMessage->sender,
                    // 'attachments' => $lastMessage->attachments,
                ] : [
                    'id'        => 0,
                    'chat_id'   => $chat->id,
                    'sender_id' => 0,
                    'message'   => 'No message',
                    'type'      => 'text',
                    'is_read'   => 0,

                    'sender' => '{
                        "id": 0,
                        "name": "",
                        "avatar": ""
                    }',
                    // 'attachments' => [],
                ],
            ];
        }

        return Helper::successData($data, 'Chat List', 200);
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

        $authUser = auth()->user();

        // Other participant (for private chat)
        $otherUser = $chat->users
            ->where('id', '!=', $authUser->id)
            ->first();

        $iBlockedThem = false;
        $theyBlockedMe = false;

        if ($otherUser) {
            $iBlockedThem = UserBlock::where([
                'user_id' => $authUser->id,
                'blocked_user_id' => $otherUser->id,
            ])->exists();

            $theyBlockedMe = UserBlock::where([
                'user_id' => $otherUser->id,
                'blocked_user_id' => $authUser->id,
            ])->exists();
        }

        $chat['i_blocked_them'] = $iBlockedThem;
        $chat['they_blocked_me'] = $theyBlockedMe;
        $chat['can_send_message'] = !($iBlockedThem || $theyBlockedMe);

        // Get messages
        $messages = ChatMessage::where('chat_id', $chat->id)
            ->with([
                'sender',
                'attachments',
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return Helper::successData([
            'chat' => $chat,
            'messages' => $messages,
        ], 200);
    }
}
