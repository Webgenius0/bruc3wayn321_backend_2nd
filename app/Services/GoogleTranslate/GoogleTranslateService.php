<?php

namespace App\Services\GoogleTranslate;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleTranslateService
{
    public function translateBatch(
        array $texts,
        string $targetLanguage,
        ?string $sourceLanguage = null
    ): array {
        if (empty($texts)) {
            return [];
        }

        // dd(config('services.google_translate.api_key'));

        $payload = [
            'q' => array_values($texts),
            'target' => $targetLanguage,
            'format' => 'text',
        ];

        if ($sourceLanguage) {
            $payload['source'] = $sourceLanguage;
        }

        $response = Http::timeout(120)->post(
            config('services.google_translate.url')
                . '?key='
                . config('services.google_translate.api_key'),
            $payload
        );
        dd(
            $response->status(),
            $response->body(),
            $response->json()
        );

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('error.message')
                    ?? 'Google Translate failed.'
            );
        }

        return collect(
            data_get(
                $response->json(),
                'data.translations',
                []
            )
        )
            ->map(fn($item) => [
                'text' => html_entity_decode(
                    $item['translatedText'] ?? '',
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                ),

                'source_language' =>
                $item['detectedSourceLanguage']
                    ?? $sourceLanguage,
            ])
            ->values()
            ->toArray();
    }

    public function languages(string $displayLanguage = 'en'): array
    {
        $response = Http::timeout(60)->get(
            config('services.google_translate.url')
                . '/languages',
            [
                'key' => config('services.google_translate.api_key'),
                'target' => $displayLanguage,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('error.message')
                    ?? 'Unable to fetch supported languages.'
            );
        }

        return $response->json('data.languages', []);
    }
}
