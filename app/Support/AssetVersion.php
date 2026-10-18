<?php

namespace App\Support;

/**
 * asset() with a cache-busting ?v=<file time>. A missing file never breaks the page
 * (the old inline filemtime() threw on a server where the file was not uploaded).
 */
class AssetVersion
{
    public static function url(string $path): string
    {
        $file = public_path($path);

        return asset($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
    }
}
