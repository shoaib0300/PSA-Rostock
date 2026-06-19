<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

final class PsaSeoAssetGenerator
{
    private const BLUE_DARK = [0, 87, 164];

    private const BLUE_LIGHT = [0, 163, 217];

    private const WHITE = [255, 255, 255];

    private const INK = [34, 47, 48];

    private const BONE = [247, 247, 245];

    private const LIME = [206, 247, 158];

    public static function generateAll(string $targetDir): void
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('The GD extension is required to generate SEO image assets.');
        }

        self::writeManifest($targetDir);
        self::writePng($targetDir.'/favicon-16x16.png', 16);
        self::writePng($targetDir.'/favicon-32x32.png', 32);
        self::writePng($targetDir.'/apple-touch-icon.png', 180);
        self::writePng($targetDir.'/icon-192.png', 192);
        self::writePng($targetDir.'/icon-512.png', 512);
        copy($targetDir.'/favicon-32x32.png', $targetDir.'/favicon.ico');

        if (!is_file($targetDir.'/og-image.png')) {
            self::writeOgImage($targetDir.'/og-image.png');
        }
    }

    private static function writeManifest(string $targetDir): void
    {
        $manifest = [
            'name' => PsaSeo::SITE_NAME,
            'short_name' => 'PSA',
            'description' => PsaSeo::TAGLINE,
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#f7f7f5',
            'theme_color' => PsaSeo::THEME_COLOR,
            'icons' => [
                [
                    'src' => '/files/favicons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/files/favicons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ];

        file_put_contents(
            $targetDir.'/site.webmanifest',
            json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    private static function writePng(string $path, int $size): void
    {
        $image = imagecreatetruecolor($size, $size);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        self::drawIcon($image, (int) ($size / 2), (int) ($size / 2), (int) round($size * 0.46));

        imagepng($image, $path, 9);
        imagedestroy($image);
    }

    private static function writeOgImage(string $path): void
    {
        $width = 1200;
        $height = 630;
        $image = imagecreatetruecolor($width, $height);
        $bone = self::allocate($image, self::BONE);
        $ink = self::allocate($image, self::INK);
        $lime = self::allocate($image, self::LIME);
        $blueDark = self::allocate($image, self::BLUE_DARK);

        imagefilledrectangle($image, 0, 0, $width, $height, $bone);
        imagefilledrectangle($image, 0, 0, $width, 12, $lime);
        imagefilledrectangle($image, 0, $height - 12, $width, $height, $lime);

        self::drawIcon($image, 220, 315, 150);

        imagestring($image, 5, 430, 250, 'PSA ROSTOCK', $blueDark);
        imagestring($image, 4, 430, 310, 'Polizeisportabteilung Rostock', $ink);
        imagestring($image, 3, 430, 360, 'Sport . Gemeinschaft . Events', $ink);

        imagepng($image, $path, 8);
        imagedestroy($image);
    }

    /**
     * @param resource|\GdImage $image
     */
    private static function drawIcon($image, int $cx, int $cy, int $radius): void
    {
        $dark = self::allocate($image, self::BLUE_DARK);
        $light = self::allocate($image, self::BLUE_LIGHT);
        $white = self::allocate($image, self::WHITE);

        imagefilledellipse($image, $cx, $cy, $radius * 2, $radius * 2, $dark);
        imagefilledarc($image, $cx, $cy, $radius * 2, $radius * 2, 300, 120, $light, IMG_ARC_PIE);

        $scale = $radius / 55.0;
        $waveY = (int) round($cy + 20 * $scale);
        imagesetthickness($image, max(1, (int) round(6 * $scale)));
        imagearc($image, $cx, $waveY, (int) round(110 * $scale), (int) round(40 * $scale), 200, 340, $white);

        $northY = (int) round($cy - 35 * $scale);
        $southY = (int) round($cy + 30 * $scale);
        $eastX = (int) round($cx + 30 * $scale);

        imagefilledpolygon(
            $image,
            [
                $cx,
                $northY,
                $eastX,
                $southY,
                $cx,
                $southY,
            ],
            $white
        );

        imagesetthickness($image, max(1, (int) round(4 * $scale)));
        imageline($image, $cx, $northY, $cx, (int) round($cy + 40 * $scale), $white);
        imagesetthickness($image, 1);
    }

    /**
     * @param resource|\GdImage $image
     */
    private static function allocate($image, array $rgb): int
    {
        return imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
    }
}
