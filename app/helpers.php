<?php

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

function sendResponse($result)
{
    return response()->json($result);
}

function sendError($error, $code = 404)
{
    $response = [
        'success' => false,
        'message' => $error,
    ];
    return response()->json($response, $code);
}

function save_file($pdf_64, $path, $fileType = "jpg")
{
    $arr = explode(' ', Carbon::now());
    $string = str_replace(':', '', $arr[1]);
    $pdfName = $string . '.' . $fileType;
    Storage::disk($path)->put($pdfName, base64_decode($pdf_64));
    $autoload['helper'] = array('url');
    return $pdfName;
}

function save_image($image)
{

    $path = base_path('categoriesImages/');
    !is_dir($path) &&
    mkdir($path, 0777, true);

    $imageName = time() . '.' . $image->extension();
    $image->move($path, $imageName);
//    dd($imageName);
    return $imageName;
}

function requests_limit()
{
    return Setting::find(1)->requests_limit ?? 0;
}
