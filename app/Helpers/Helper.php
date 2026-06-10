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
}
