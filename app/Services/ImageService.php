<?php

namespace App\Services;

use App\Enums\ImageUploadOption;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class ImageService
{
    public function upload(
        UploadedFile $rawImage,
        ImageUploadOption $option,
    ): Image {
        $loadedImage = (new ImageManager(new Driver))->read($rawImage);
        $dimensions = $rawImage->dimensions();

        if ($dimensions === null) {
            throw new RuntimeException('Unable to read the image dimensions.');
        }

        $maxEdgeLength = max($dimensions[0], $dimensions[1]);
        $encodedImage = match ($option) {
            ImageUploadOption::WHITE_EDGE => $loadedImage
                ->pad($maxEdgeLength, $maxEdgeLength)
                ->encode(),
            ImageUploadOption::FILL => $loadedImage
                ->cover($maxEdgeLength, $maxEdgeLength)
                ->encode(),
            ImageUploadOption::STRETCH => $loadedImage
                ->resize($maxEdgeLength, $maxEdgeLength)
                ->encode(),
            ImageUploadOption::ORIGINAL => $loadedImage->encode(),
        };
        $path = Str::uuid().'.'.$rawImage->extension();

        if (! Storage::disk('public')->put($path, $encodedImage->toString())) {
            throw new RuntimeException('Unable to store the uploaded image.');
        }

        try {
            return Image::query()->create([
                'name' => $rawImage->getClientOriginalName(),
                'mime_type' => $encodedImage->mediaType(),
                'size' => $encodedImage->size(),
                'url' => "/storage/{$path}",
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }
    }

    public function deleteIfUnreferenced(Image $image): void
    {
        if ($image->items()->exists()
            || $image->variation()->exists()
            || $image->order()->exists()
        ) {
            return;
        }

        $storagePath = $this->storagePath($image);

        $image->delete();

        if ($storagePath !== null) {
            Storage::disk('public')->delete($storagePath);
        }
    }

    private function storagePath(Image $image): ?string
    {
        if (! $image->url || ! str_starts_with($image->url, '/storage/')) {
            return null;
        }

        return Str::after($image->url, '/storage/');
    }
}
