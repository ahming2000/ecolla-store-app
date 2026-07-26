<?php

namespace App\Http\Controllers\Image;

use App\Enums\ImageUploadOption;
use App\Http\Controllers\Controller;
use App\Http\Requests\Image\UploadImageRequest;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    public function __construct(private readonly ImageService $imageService) {}

    public function upload(UploadImageRequest $request): JsonResponse
    {
        $image = $this->imageService->upload(
            $request->file('image'),
            ImageUploadOption::from($request->validated('option')),
        );

        return response()->json($image);
    }
}
