<?php

namespace App\Services\GoogleTranslate;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageTranslationService
{
    public function __construct(
        private GoogleVisionService $visionService,
        private GoogleTranslateService $translateService,
        private TranslatedImageService $imageService,
    ) {}


    public function translate(
        UploadedFile $image,
        string $targetLanguage,
        ?string $sourceLanguage = null
    ): array {
        $uuid = Str::uuid()->toString();

        /*
        |--------------------------------------------------------------------------
        | Save original
        |--------------------------------------------------------------------------
        */

        $originalPath =
            "translations/original/{$uuid}.jpg";

        Storage::disk('public')->put(
            $originalPath,
            file_get_contents(
                $image->getRealPath()
            )
        );

        $originalAbsolutePath =
            Storage::disk('public')
            ->path($originalPath);


        /*
        |--------------------------------------------------------------------------
        | OCR
        |--------------------------------------------------------------------------
        */

        $ocr = $this->visionService
            ->detect($image);

        $regions = $ocr['regions'];

        if (empty($regions)) {
            throw new RuntimeException(
                'No text detected.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Translate
        |--------------------------------------------------------------------------
        */

        $texts = collect($regions)
            ->pluck('text')
            ->toArray();

        $translations =
            $this->translateService
            ->translateBatch(
                $texts,
                $targetLanguage,
                $sourceLanguage
            );


        /*
        |--------------------------------------------------------------------------
        | Merge translation
        |--------------------------------------------------------------------------
        */

        foreach ($regions as $index => &$region) {

            $region['translated_text'] =
                $translations[$index]['text']
                ?? $region['text'];

            $region['source_language'] =
                $translations[$index]['source_language']
                ?? null;
        }

        unset($region);


        /*
        |--------------------------------------------------------------------------
        | Generate translated image
        |--------------------------------------------------------------------------
        */

        $resultPath =
            "translations/results/{$uuid}.jpg";

        $resultAbsolutePath =
            Storage::disk('public')
            ->path($resultPath);
        $this->imageService->generate(
            $originalAbsolutePath,
            $regions,
            $resultAbsolutePath,
            $targetLanguage
        );


        return [
            'translated_image' =>
            url(
                Storage::disk('public')
                    ->url($resultPath)
            ),

            'original_text' =>
            $ocr['full_text'],

            'translated_text' =>
            collect($regions)
                ->pluck('translated_text')
                ->implode("\n"),

            'target_language' =>
            $targetLanguage,
        ];
    }
}
