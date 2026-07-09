<?php

namespace App\Services\GoogleTranslate;

use RuntimeException;

class TranslatedImageService
{
    private string $font;


    public function __construct(
        private FontResolverService $fontResolver
    ) {}


    public function generate(
        string $sourcePath,
        array $regions,
        string $outputPath,
        string $targetLanguage
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Resolve font
        |--------------------------------------------------------------------------
        */

        $this->font = $this->fontResolver
            ->resolve($targetLanguage);


        /*
        |--------------------------------------------------------------------------
        | Open image
        |--------------------------------------------------------------------------
        */

        $image = $this->openImage(
            $sourcePath
        );


        /*
        |--------------------------------------------------------------------------
        | Remove original text
        |--------------------------------------------------------------------------
        */

        foreach ($regions as $region) {

            $this->removeOriginalText(
                $image,
                $region
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Draw translated text
        |--------------------------------------------------------------------------
        */

        foreach ($regions as $region) {

            $text = trim(
                $region['translated_text']
                    ?? ''
            );


            if ($text === '') {
                continue;
            }


            $this->drawText(
                $image,
                $text,
                $region
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Save image
        |--------------------------------------------------------------------------
        */

        $directory = dirname(
            $outputPath
        );


        if (! is_dir($directory)) {

            mkdir(
                $directory,
                0755,
                true
            );
        }


        imagejpeg(
            $image,
            $outputPath,
            92
        );


        imagedestroy($image);


        return $outputPath;
    }


    private function drawText(
        $image,
        string $text,
        array $region
    ): void {
        $x = (int) $region['x'];
        $y = (int) $region['y'];

        $width = max(
            20,
            (int) $region['width']
        );

        $height = max(
            15,
            (int) $region['height']
        );

        $fontSize = $this->findFontSize(
            $text,
            $width,
            $height
        );

        $lines = $this->wrapText(
            $text,
            $fontSize,
            $width
        );

        $color = imagecolorallocate(
            $image,
            20,
            20,
            20
        );

        $lineHeight = (int) ceil(
            $fontSize * 1.35
        );

        $currentY =
            $y + $fontSize;

        foreach ($lines as $line) {

            imagettftext(
                $image,
                $fontSize,
                0,
                $x,
                $currentY,
                $color,
                $this->font,
                $line
            );

            $currentY += $lineHeight;

            if ($currentY > $y + $height) {
                break;
            }
        }
    }


    private function findFontSize(
        string $text,
        int $width,
        int $height
    ): int {
        $maxSize = min(
            60,
            max(10, (int) ($height * 0.8))
        );

        for ($size = $maxSize; $size >= 6; $size--) {

            $lines = $this->wrapText(
                $text,
                $size,
                $width
            );

            $lineHeight = (int) ceil(
                $size * 1.35
            );

            $requiredHeight =
                count($lines) * $lineHeight;

            $fitsWidth = true;

            foreach ($lines as $line) {

                if (
                    $this->textWidth(
                        $line,
                        $size
                    ) > $width
                ) {
                    $fitsWidth = false;
                    break;
                }
            }

            if (
                $fitsWidth
                && $requiredHeight <= $height
            ) {
                return $size;
            }
        }

        return 6;
    }


    private function wrapText(
        string $text,
        int $fontSize,
        int $maxWidth
    ): array {
        $words = preg_split(
            '/\s+/u',
            trim($text),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $lines = [];
        $line = '';

        foreach ($words as $word) {

            $testLine = $line === ''
                ? $word
                : $line . ' ' . $word;

            if (
                $this->textWidth(
                    $testLine,
                    $fontSize
                ) <= $maxWidth
            ) {
                $line = $testLine;
                continue;
            }

            if ($line !== '') {
                $lines[] = $line;
            }

            // Never break Bengali/Unicode words
            $line = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }


    private function splitLongWord(
        string $word,
        int $fontSize,
        int $maxWidth
    ): array {
        preg_match_all(
            '/\X/u',
            $word,
            $matches
        );

        $clusters = $matches[0];

        $lines = [];
        $current = '';

        foreach ($clusters as $cluster) {
            $test = $current . $cluster;

            if (
                $current !== ''
                && $this->textWidth(
                    $test,
                    $fontSize
                ) > $maxWidth
            ) {
                $lines[] = $current;
                $current = $cluster;
            } else {
                $current = $test;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }


    private function textWidth(
        string $text,
        int $fontSize
    ): int {
        $box = imagettfbbox(
            $fontSize,
            0,
            $this->font,
            $text
        );

        return abs(
            $box[2] - $box[0]
        );
    }


    private function removeOriginalText(
        $image,
        array $region
    ): void {

        $padding = 4;


        $imageWidth =
            imagesx($image);

        $imageHeight =
            imagesy($image);


        $x1 = max(
            0,
            (int) $region['x']
                - $padding
        );


        $y1 = max(
            0,
            (int) $region['y']
                - $padding
        );


        $x2 = min(
            $imageWidth - 1,

            (int) $region['x']
                + (int) $region['width']
                + $padding
        );


        $y2 = min(
            $imageHeight - 1,

            (int) $region['y']
                + (int) $region['height']
                + $padding
        );


        $background =
            $this->sampleColor(
                $image,
                $x1,
                $y1,
                $x2,
                $y2
            );


        $color = imagecolorallocate(
            $image,
            $background['r'],
            $background['g'],
            $background['b']
        );


        imagefilledrectangle(
            $image,
            $x1,
            $y1,
            $x2,
            $y2,
            $color
        );
    }


    private function sampleColor(
        $image,
        int $x1,
        int $y1,
        int $x2,
        int $y2
    ): array {

        $width =
            imagesx($image);

        $height =
            imagesy($image);


        $points = [

            [$x1 - 3, $y1],

            [$x2 + 3, $y1],

            [$x1, $y1 - 3],

            [$x1, $y2 + 3],

            [$x1 - 3, $y1 - 3],

            [$x2 + 3, $y2 + 3],
        ];


        $colors = [];


        foreach (
            $points
            as [$x, $y]
        ) {

            $x = max(
                0,
                min(
                    $x,
                    $width - 1
                )
            );


            $y = max(
                0,
                min(
                    $y,
                    $height - 1
                )
            );


            $rgb = imagecolorat(
                $image,
                $x,
                $y
            );


            $colors[] = [

                'r' => ($rgb >> 16) & 255,

                'g' => ($rgb >> 8) & 255,

                'b' =>
                $rgb & 255,
            ];
        }


        return [

            'r' =>
            (int) collect(
                $colors
            )->avg('r'),

            'g' =>
            (int) collect(
                $colors
            )->avg('g'),

            'b' =>
            (int) collect(
                $colors
            )->avg('b'),
        ];
    }


    private function openImage(
        string $path
    ) {

        $mime =
            mime_content_type($path);


        return match ($mime) {

            'image/jpeg' =>
            imagecreatefromjpeg(
                $path
            ),

            'image/png' =>
            imagecreatefrompng(
                $path
            ),

            'image/webp' =>
            imagecreatefromwebp(
                $path
            ),

            default =>
            throw new RuntimeException(
                "Unsupported image: {$mime}"
            ),
        };
    }
}
