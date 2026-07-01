<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiConciergeService
{
    protected string $apiKey;

    protected string $model;

    protected string $baseUrl = 'https://api.openai.com/v1';

    protected string $systemPrompt = 'You are a helpful AI travel concierge. Help users plan trips, find the best hotels, suggest adventure activities, recommend popular destinations, and provide travel tips. Be friendly, knowledgeable, and provide detailed, actionable suggestions. When users ask about destinations, include practical information like best times to visit, estimated costs, and local tips.';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Send a message in a conversation and get the AI response.
     */
    public function chat(AiConversation $conversation, string $userMessage): AiMessage
    {
        // Save the user's message
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Auto-generate title from the first message
        if (!$conversation->title) {
            $conversation->update([
                'title' => Str::limit($userMessage, 80),
            ]);
        }

        // Build messages array for OpenAI
        $messages = $this->buildMessages($conversation);

        // Call OpenAI API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->timeout(60)->post("{$this->baseUrl}/chat/completions", [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ]);

        $response->throw();

        $assistantContent = $response->json('choices.0.message.content', 'Sorry, I could not generate a response.');

        // Save the assistant's response
        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $assistantContent,
        ]);

        // Touch conversation to update the updated_at timestamp
        $conversation->touch();

        return $assistantMessage;
    }

    /**
     * Build the messages array for the OpenAI API from conversation history.
     */
    protected function buildMessages(AiConversation $conversation): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt,
            ],
        ];

        // Load conversation history
        $history = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($history as $message) {
            $messages[] = [
                'role' => $message->role,
                'content' => $message->content,
            ];
        }

        return $messages;
    }
}
