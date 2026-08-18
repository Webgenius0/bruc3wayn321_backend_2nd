<?php

namespace App\Http\Controllers\API\AiConcierge;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AiConciergeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiConciergeController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AiConciergeService $service
    ) {}

    /**
     * List all conversations for the authenticated user.
     */
    public function index(Request $request)
    {
        $conversations = AiConversation::where('user_id', $request->user()->id)
            ->with('latestMessage')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return $this->successResponse('AI Concierge conversations', $conversations);
    }

    /**
     * Show a single conversation with all messages.
     */
    public function show(Request $request, AiConversation $conversation)
    {
        // Ensure the conversation belongs to the authenticated user
        if ($conversation->user_id !== $request->user()->id) {
            return $this->errorResponse('Conversation not found', 404);
        }
        // $chat = AiMessage::where($conversation->id);
        $conversation->load('messages');
        return $this->successResponse('Conversation details', $conversation);
    }

    /**
     * Send a message to the AI concierge.
     * Creates a new conversation if conversation_id is not provided.
     */
    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|integer|exists:ai_conversations,id',
            'type' => 'required|in:ai_chat,ai_voice',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();
        $user = $request->user();
        /* Check Usage Limit */
        if (!$user->canPerformAction($validated['type'])) {
            $message = $validated['type'] === 'ai_voice'
                ? 'You have reached your monthly AI voice chat limit. Please upgrade to a paid subscription for more messages.'
                : 'You have reached your monthly AI chat limit. Please upgrade to a paid subscription for more messages.';
            return $this->errorResponse($message, 429);
        }

        /*
        |--------------------------------------------------------------------------
        | Get or Create Conversation
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['conversation_id'])) {
            $conversation = AiConversation::where('id', $validated['conversation_id'])->where('user_id', $user->id)->first();
            if (!$conversation) {
                return $this->errorResponse('Conversation not found', 404);
            }
        } else {

            $conversation = AiConversation::create([
                'user_id' => $user->id,
            ]);
        }

        try {
            $assistantMessage = $this->service->chat($conversation, $validated['message']);
            $user->incrementUsage($validated['type']);
            return $this->successResponse('Message sent', ['conversation_id' => $conversation->id, 'conversation_title' => $conversation->fresh()->title, 'message' => $assistantMessage]);
            
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get AI response: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a conversation.
     */
    public function destroy(Request $request, AiConversation $conversation)
    {
        if ($conversation->user_id !== $request->user()->id) {
            return $this->errorResponse('Conversation not found', 404);
        }

        $conversation->delete();

        return $this->successResponse('Conversation deleted');
    }

    /**
     * Send a voice message to the AI concierge.
     */
    public function voiceChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audio' => 'required|file|mimes:mp3,wav,m4a,webm,ogg,mp4,mpga,x-m4a|max:10240', // 10MB max
            'conversation_id' => 'nullable|integer|exists:ai_conversations,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $user = $request->user();
        $validated = $validator->validated();

        try {
            // 1. Transcribe the uploaded audio
            $audioFile = $request->file('audio');
            $userText = $this->service->transcribeAudio(
                $audioFile->getPathname(),
                $audioFile->getClientOriginalName()
            );

            if (empty($userText)) {
                return $this->errorResponse('Could not transcribe audio', 400);
            }

            // 2. Get or create conversation
            if (!empty($validated['conversation_id'])) {
                $conversation = AiConversation::where('id', $validated['conversation_id'])->where('user_id', $user->id)->first();
                if (!$conversation) {
                    return $this->errorResponse('Conversation not found', 404);
                }
            } else {
                $conversation = AiConversation::create([
                    'user_id' => $user->id,
                ]);
            }

            // 3. Get AI text response
            $assistantMessage = $this->service->chat($conversation, $userText);

            // 4. Convert AI text response to speech
            $audioUrl = $this->service->textToSpeech($assistantMessage->content);

            // Increment usage counts
            $user->incrementUsage('ai_voice');

            return $this->successResponse('Voice message processed', [
                'conversation_id' => $conversation->id,
                'conversation_title' => $conversation->fresh()->title,
                'user_message' => $userText,
                'ai_message' => $assistantMessage,
                'ai_audio_url' => $audioUrl,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to process voice chat: ' . $e->getMessage(),
                500
            );
        }
    }
}
