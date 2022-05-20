<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Ulid\Ulid;

class ImageController extends Controller
{
    public function verify(): JsonResponse
    {
        return response()->json(['isVerified' => request()->hasFile('image')]);
    }

    public static function save($fileInput): string
    {
        $path = public_path('/storage/uploads/' . Ulid::generate() . '.jpg');
        Image::make($fileInput->getPathName())->save($path);
        return $path;
    }
}
