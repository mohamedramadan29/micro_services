<?php

namespace App\Http\Traits;
trait Upload_Images
{
    function saveImage($file, $path)
    {
        $file_extension = $file->getClientOriginalExtension();
        $file_name = time() . uniqid() . '.' . $file_extension;
        $file->move($path, $file_name); // Call move() on $file, not $file_name
        return $file_name;
    }
}
