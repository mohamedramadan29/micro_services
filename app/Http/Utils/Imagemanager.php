<?php

namespace App\Http\Utils;

use Illuminate\Support\Str;
use \Illuminate\Support\Facades\File;


class Imagemanager
{

    public function UploadImages($images, $model, $disk)
    {
        foreach ($images as $image) {
            $filename = $this->GenerateImageName($image);
            $this->StoreImageLocale($image, '/', $filename, $disk);
            $model->images()->create([
                'file_name' => $filename,
            ]);
        }
    }
    public function UploadSingleImage($path, $image, $disk)
    {
        $file_name = $this->GenerateImageName($image);
        self::StoreImageLocale($image, $path, $file_name, $disk);
        return $file_name;
    }

    public function GenerateImageName($image)
    {
        $file_name = Str::uuid() . time() . $image->getClientOriginalExtension();
        return $file_name;
    }

    private function StoreImageLocale($image, $path, $file_name, $disk)
    {
        $image->storeAs($path, $file_name, ['disk' => $disk]);
    }

    ######### Delete Image

    public function deleteImageFromLocal($image_path)
    {
        if (File::exists(public_path($image_path))) {
            File::delete(public_path($image_path));
        }
    }
}
