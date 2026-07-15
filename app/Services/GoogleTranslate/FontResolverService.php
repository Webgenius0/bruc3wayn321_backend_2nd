<?php

namespace App\Services\GoogleTranslate;

use RuntimeException;

class FontResolverService
{
    public function resolve(string $language): string
    {
        $language = strtolower(
            str_replace('_', '-', $language)
        );

        $font = match (true) {

            // Bengali / Assamese
            in_array($language, [
                'bn',
                'as',
            ]) =>
            'NotoSansBengali-Regular.ttf',


            // Arabic script
            in_array($language, [
                'ar',
                'fa',
                'ur',
                'ps',
                'sd',
                'ku',
            ]) =>
            'NotoSansArabic-Regular.ttf',


            // Devanagari
            in_array($language, [
                'hi',
                'mr',
                'ne',
                'sa',
            ]) =>
            'NotoSansDevanagari-Regular.ttf',


            // Thai
            $language === 'th' =>
            'NotoSansThai-Regular.ttf',


            // Japanese
            $language === 'ja' =>
            'NotoSansJP-Regular.ttf',


            // Korean
            $language === 'ko' =>
            'NotoSansKR-Regular.ttf',


            // Traditional Chinese
            in_array($language, [
                'zh-tw',
                'zh-hk',
                'zh-hant',
            ]) =>
            'NotoSansTC-Regular.ttf',


            // Simplified Chinese
            in_array($language, [
                'zh',
                'zh-cn',
                'zh-sg',
                'zh-hans',
            ]) =>
            'NotoSansSC-Regular.ttf',


            default =>
            'NotoSans-Regular.ttf',
        };

        $path = public_path(
            "fonts/{$font}"
        );

        if (! file_exists($path)) {
            throw new RuntimeException(
                "Font not found: {$path}"
            );
        }

        return $path;
    }
}
