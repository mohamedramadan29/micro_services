<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Square profile photos at the sizes the site shows them (client file
 * «تعديلات», item 12): a centred square crop saved at 128, 256 and 512 px,
 * never enlarged, so cards and pages stay sharp instead of stretching one
 * small file. Uses GD.
 */
class ImageVariants
{
    public const SIZES = [128, 256, 512];

    /** Store an upload under public/$directory; returns the base file name (the 512 px version). */
    /** Phone photos are often stored sideways with an EXIF «orientation» flag: turn the pixels upright. */
    private static function orient(\GdImage $image, string $path): \GdImage
    {
        $orientation = function_exists('exif_read_data') ? (int) (@exif_read_data($path)['Orientation'] ?? 1) : 1;
        $angle = [3 => 180, 6 => 270, 8 => 90][$orientation] ?? 0;
        if (! $angle) {
            return $image;
        }
        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            return $image;
        }
        imagedestroy($image);

        return $rotated;
    }

    public static function storeSquare(UploadedFile $file, string $directory): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $source) {
            throw new RuntimeException('تعذّر قراءة الصورة. استخدم صورة jpg أو png أو webp.');
        }

        $source = self::orient($source, $file->getRealPath());
        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);
        $x = intdiv($width - $side, 2);
        $y = intdiv($height - $side, 2);

        $path = public_path($directory);
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $base = bin2hex(random_bytes(12));

        foreach (self::SIZES as $size) {
            $target = min($size, $side); // never upscale
            $canvas = imagecreatetruecolor($target, $target);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255)); // transparent PNGs on white
            imagecopyresampled($canvas, $source, 0, 0, $x, $y, $target, $target, $side, $side);
            imagejpeg($canvas, $path . '/' . $base . '-' . $size . '.jpg', 88);
            imagedestroy($canvas);
        }
        imagedestroy($source);

        return $base;
    }

    /** Relative path of the smallest stored version at least $size wide. */
    public static function url(string $directory, string $base, int $size): string
    {
        foreach (self::SIZES as $candidate) {
            if ($candidate >= $size) {
                return $directory . '/' . $base . '-' . $candidate . '.jpg';
            }
        }

        return $directory . '/' . $base . '-' . max(self::SIZES) . '.jpg';
    }

    public static function delete(string $directory, ?string $base): void
    {
        if (! $base) {
            return;
        }
        foreach (self::SIZES as $size) {
            @unlink(public_path($directory . '/' . $base . '-' . $size . '.jpg'));
        }
    }
}
