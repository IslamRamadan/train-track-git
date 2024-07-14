<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function save_image($imageData, $folder_name)
    {
        $image = base64_decode($imageData);

        $imageName = uniqid('image_') . '.jpg';
        $imagePath = $folder_name . '/' . $imageName; // Path within the public/images folder

        Storage::disk('public')->put($imagePath, $image);
        return $imageName;
    }
}
