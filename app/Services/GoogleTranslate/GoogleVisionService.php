<?php

namespace App\Services\GoogleTranslate;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleVisionService
{
    public function detect(UploadedFile $image): array
    {
        $response = Http::timeout(120)->post(
            config('services.google_vision.url')
                . '?key='
                . config('services.google_vision.api_key'),
            [
                'requests' => [
                    [
                        'image' => [
                            'content' => base64_encode(
                                file_get_contents(
                                    $image->getRealPath()
                                )
                            ),
                        ],

                        'features' => [
                            [
                                'type' => 'DOCUMENT_TEXT_DETECTION',
                            ],
                        ],
                    ],
                ],
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Google Vision API failed: '
                    . $response->body()
            );
        }

        $result = data_get(
            $response->json(),
            'responses.0',
            []
        );

        return [
            'full_text' => data_get(
                $result,
                'fullTextAnnotation.text',
                ''
            ),

            'regions' => $this->getParagraphs(
                data_get(
                    $result,
                    'fullTextAnnotation.pages',
                    []
                )
            ),
        ];
    }


    private function getParagraphs(array $pages): array
    {
        $regions = [];

        foreach ($pages as $page) {

            foreach ($page['blocks'] ?? [] as $block) {

                foreach (
                    $block['paragraphs'] ?? []
                    as $paragraph
                ) {

                    $text = $this->paragraphText(
                        $paragraph
                    );

                    if (trim($text) === '') {
                        continue;
                    }

                    $vertices = data_get(
                        $paragraph,
                        'boundingBox.vertices',
                        []
                    );

                    $box = $this->box($vertices);

                    $regions[] = [
                        'text' => trim($text),

                        'x' => $box['x'],
                        'y' => $box['y'],

                        'width' => $box['width'],
                        'height' => $box['height'],

                        'vertices' => $vertices,
                    ];
                }
            }
        }

        return $regions;
    }


    private function paragraphText(
        array $paragraph
    ): string {
        $words = [];

        foreach (
            $paragraph['words'] ?? []
            as $word
        ) {

            $wordText = '';

            foreach (
                $word['symbols'] ?? []
                as $symbol
            ) {
                $wordText .= $symbol['text'] ?? '';
            }

            if ($wordText !== '') {
                $words[] = $wordText;
            }
        }

        return implode(' ', $words);
    }


    private function box(array $vertices): array
    {
        $xs = collect($vertices)
            ->pluck('x')
            ->map(fn($value) => (int) ($value ?? 0));

        $ys = collect($vertices)
            ->pluck('y')
            ->map(fn($value) => (int) ($value ?? 0));

        $minX = $xs->min() ?? 0;
        $maxX = $xs->max() ?? 0;

        $minY = $ys->min() ?? 0;
        $maxY = $ys->max() ?? 0;

        return [
            'x' => $minX,
            'y' => $minY,

            'width' => max(
                1,
                $maxX - $minX
            ),

            'height' => max(
                1,
                $maxY - $minY
            ),
        ];
    }
}
