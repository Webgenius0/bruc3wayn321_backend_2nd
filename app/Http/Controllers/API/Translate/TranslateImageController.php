<?php

namespace App\Http\Controllers\API\Translate;

use App\Http\Controllers\Controller;
use App\Services\GoogleTranslate\GoogleTranslateService;
use App\Services\GoogleTranslate\ImageTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TranslateImageController extends Controller
{
    public function translate(
        Request $request,
        ImageTranslationService $service
    ): JsonResponse {
        $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'target_language' => [
                'required',
                'string',
                'max:10',
            ],

            'source_language' => [
                'nullable',
                'string',
                'max:10',
            ],
        ]);

        try {

            $result = $service->translate(
                $request->file('image'),
                $request->target_language,
                $request->source_language
            );

            return response()->json([
                'success' => true,
                'message' =>
                'Image translated successfully.',
                'data' => $result,
            ]);
        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function languages(
        Request $request,
        GoogleTranslateService $translateService
    ) {
        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'display_language' => [
                'nullable',
                'string',
                'max:10',
            ],
        ]);

        $languages = $translateService->languages(
            $request->input('display_language', 'en')
        );

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $languages = collect($languages)
                ->filter(
                    fn($language) =>
                    str_contains(
                        mb_strtolower($language['name']),
                        mb_strtolower($search)
                    )
                )
                ->values()
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => $languages,
        ]);
    }
}
