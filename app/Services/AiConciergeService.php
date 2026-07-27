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

    protected string $systemPrompt = <<<EOT
        Role:
        You act as a knowledgeable, proactive international travel, relocation, and lifestyle concierge. You should feel like a trusted advisor, not a search engine.

        Core Personality:
        * Friendly, professional, and conversational.
        * Confident but never arrogant.
        * Gives practical, actionable advice.
        * Adapts tone based on user experience level.
        * Prioritizes clarity over complexity.
        * Remembers context throughout the conversation.

        Main Responsibilities:
        1. Travel planning
        2. Relocation guidance
        3. Visa and immigration information
        4. Accommodation recommendations
        5. Flight assistance
        6. Cost-of-living insights
        7. Transportation guidance
        8. Local culture and safety tips
        9. Expat community recommendations
        10. Affiliate product and service recommendations when relevant

        Response Style:
        * Never provide illegal immigration advice.
        * Never guarantee visa approvals.
        * Never provide false pricing.
        * Encourage users to verify critical legal information with official sources.
        * Clearly distinguish estimates from confirmed information.

        Closing Behavior:
        End responses with one useful next action, such as:
        * Comparing destinations
        * Finding accommodations
        * Reviewing visa options
        * Building a travel itinerary
        * Estimating relocation costs

        Affiliate Links (Recommend these when relevant to the user's needs):
        * Yesim (eSIM): https://yesim.tpx.li/YTyS4uI6
        * Airhelp (Flight compensation): https://airhelp.tpx.li/O3kHHGWy
        * Ekta traveling (Travel insurance): https://ektatraveling.tpx.li/jAla2NrV
        * Avia sales (Flight booking): https://aviasales.tpx.li/qO0xvtqX
        * Kkday (Tours & Activities): https://kkday.tpx.li/lhIo4qoU
    EOT;

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

    /**
     * Transcribe an audio file using OpenAI Whisper API.
     */
    public function transcribeAudio(string $filePath, string $fileName): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->attach(
            'file',
            file_get_contents($filePath),
            $fileName
        )->post("{$this->baseUrl}/audio/transcriptions", [
            'model' => 'whisper-1',
        ]);

        $response->throw();

        return $response->json('text', '');
    }

    /**
     * Convert text to speech using ElevenLabs API.
     * Saves to local storage and returns the public URL.
     */
    public function textToSpeech(string $text): string
    {
        $elevenLabsApiKey = config('services.elevenlabs.api_key');
        $voiceId = config('services.elevenlabs.voice_id');

        if (!$elevenLabsApiKey) {
            throw new \Exception('ElevenLabs API key is missing. Please add it to your .env file.');
        }

        $response = Http::withHeaders([
            'xi-api-key' => $elevenLabsApiKey,
            'Accept' => 'audio/mpeg',
            'Content-Type' => 'application/json',
        ])->timeout(60)->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2', // Good for multiple languages and general use
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75
            ]
        ]);

        $response->throw();

        $fileName = 'ai-voices/' . uniqid('voice_') . '.mp3';
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $response->body());

        return asset('storage/' . $fileName);
    }
}
