<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Files of digital products: kept in the private uploads folder (`public/uploads/private/digital-products`,
 * closed by its .htaccess) and given only to buyers who paid (ProductOrderController::downloadProduct)
 * or to admins. Older files lived in the public `assets/uploads/digital_products`; they are moved on first
 * use or all at once with `php artisan products:secure-files`.
 */
class DigitalFiles
{
    public static function dir(): string
    {
        return public_path('uploads/private/digital-products');
    }

    public static function legacyDir(): string
    {
        return public_path('assets/uploads/digital_products');
    }

    /** Absolute path of a stored file (moving an old public one first), or null when missing. */
    public static function path(?string $name): ?string
    {
        $name = basename((string) $name);
        if ($name === '' || $name === '.' || $name === '..') {
            return null;
        }
        $private = self::dir() . DIRECTORY_SEPARATOR . $name;
        if (is_file($private)) {
            return $private;
        }

        return self::moveLegacy($name) ? $private : null;
    }

    public static function delete(?string $name): void
    {
        $name = basename((string) $name);
        if ($name === '') {
            return;
        }
        foreach ([self::dir(), self::legacyDir()] as $dir) {
            File::delete($dir . DIRECTORY_SEPARATOR . $name);
        }
    }

    /** Download name: product name + the file's extension. */
    public static function downloadName(string $productName, string $file): string
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext === '' && preg_match('/(pdf|zip|rar|7z|mp3|mp4|exe)$/i', $file, $m)) {
            $ext = $m[1]; // older names were saved without the dot
        }
        $base = trim(preg_replace('/[\\\\\/:*?"<>|]+/u', ' ', $productName)) ?: 'file';

        return $ext !== '' ? $base . '.' . strtolower($ext) : $base;
    }

    /** Moves every old public file to the private folder; returns how many moved. */
    public static function moveAllLegacy(): int
    {
        if (! is_dir(self::legacyDir())) {
            return 0;
        }
        $moved = 0;
        foreach (File::files(self::legacyDir()) as $file) {
            if (in_array($file->getFilename(), ['index.html', '.htaccess', '.gitignore'], true)) {
                continue;
            }
            $moved += self::moveLegacy($file->getFilename()) ? 1 : 0;
        }

        return $moved;
    }

    private static function moveLegacy(string $name): bool
    {
        $old = self::legacyDir() . DIRECTORY_SEPARATOR . $name;
        if (! is_file($old)) {
            return false;
        }
        File::ensureDirectoryExists(self::dir());

        return File::move($old, self::dir() . DIRECTORY_SEPARATOR . $name);
    }
}
