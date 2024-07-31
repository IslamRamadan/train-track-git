<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function save_image($imageData, $folder_name)
    {
        $image = base64_decode($imageData);

        $imageName = uniqid('image_') . '.jpg';
        $imagePath = $folder_name . '/' . $imageName; // Path within the public/images folder

//        Storage::disk('s3')->put($imagePath, $image);
        Storage::disk('public')->put($imagePath, $image);
        return $imageName;
    }

    public function generate_image_link($image_path)
    {
        return Storage::disk('s3')->temporaryUrl($image_path, Carbon::now()->addMinutes(60));
    }

    public function delete_image(mixed $image_title, $folder_name)
    {
        Storage::disk('public')->delete($folder_name . '/' . $image_title);
    }
}
