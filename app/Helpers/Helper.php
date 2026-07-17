<?php

namespace App\Helpers;

use App\Models\Post;
use Illuminate\Support\Str;

class Helper
{
    /**
     * Create a new class instance.
     */
    public static function generateUniqueSlug(
        string $content,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug(
            Str::words($content, 10, '')
        );

        $slug = $baseSlug;
        $counter = 1;

        while (
            Post::where('slug', $slug)
            ->when(
                $ignoreId,
                fn($query) => $query->where('id', '!=', $ignoreId)
            )
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public static function uploadFiles(array $files, string $folder): array
    {
        return collect($files)->map(function ($file) use ($folder) {
            $name = uniqid() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs(
                "post/{$folder}",
                $name,
                'public'
            );

            return 'storage/' . $path;
        })->toArray();
    }

    public static function successData($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error($message = 'Error', $code = 400)
    {
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
        ], $code);
    }

    public static function validation($errors, $message = 'Validation failed')
    {
        return response()->json([
            'success' => false,
            'code' => 422,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
}
