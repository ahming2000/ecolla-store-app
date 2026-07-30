<?php

namespace App\Services;

use App\Enums\ImageUploadOption;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Throwable;

class ImageService
{
    private const THUMBNAIL_MAX_EDGE_LENGTH = 480;

    private const THUMBNAIL_QUALITY = 80;

    public function upload(
        UploadedFile $rawImage,
        ImageUploadOption $option,
        bool $withThumbnail = true,
    ): Image {
        $loadedImage = $this->manager()->read($rawImage);
        $dimensions = $rawImage->dimensions();

        if ($dimensions === null) {
            throw new RuntimeException('Unable to read the image dimensions.');
        }

        $maxEdgeLength = max($dimensions[0], $dimensions[1]);
        $processedImage = match ($option) {
            ImageUploadOption::WHITE_EDGE => $loadedImage
                ->pad($maxEdgeLength, $maxEdgeLength),
            ImageUploadOption::FILL => $loadedImage
                ->cover($maxEdgeLength, $maxEdgeLength),
            ImageUploadOption::STRETCH => $loadedImage
                ->resize($maxEdgeLength, $maxEdgeLength),
            ImageUploadOption::ORIGINAL => $loadedImage,
        };
        $encodedImage = $processedImage->encodeByMediaType(
            $rawImage->getMimeType(),
        );
        $path = Str::uuid().'.'.$rawImage->extension();

        if (! Storage::disk('public')->put($path, $encodedImage->toString())) {
            throw new RuntimeException('Unable to store the uploaded image.');
        }

        try {
            $image = Image::query()->create([
                'name' => $rawImage->getClientOriginalName(),
                'mime_type' => $encodedImage->mediaType(),
                'size' => $encodedImage->size(),
                'url' => "/storage/{$path}",
            ]);

            if ($withThumbnail) {
                $this->generateThumbnail($image);
            }

            return $image->load('thumbnail');
        } catch (Throwable $exception) {
            if (isset($image)) {
                $this->deleteIfUnreferenced($image);
            }

            Storage::disk('public')->delete($path);

            throw $exception;
        }
    }

    public function generateThumbnail(Image $image): Image
    {
        $image->loadMissing('thumbnail');

        if ($image->thumbnail) {
            return $image->thumbnail;
        }

        if ($image->originalImage()->exists()) {
            throw new RuntimeException(
                'A thumbnail image cannot have its own thumbnail.',
            );
        }

        $encodedThumbnail = $this->manager()
            ->read($this->readableImageSource($image))
            ->removeAnimation()
            ->scaleDown(
                width: self::THUMBNAIL_MAX_EDGE_LENGTH,
                height: self::THUMBNAIL_MAX_EDGE_LENGTH,
            )
            ->toWebp(quality: self::THUMBNAIL_QUALITY);
        $thumbnailPath = $this->thumbnailStoragePath($image);

        if (! Storage::disk('public')->put(
            $thumbnailPath,
            $encodedThumbnail->toString(),
        )) {
            throw new RuntimeException('Unable to store the image thumbnail.');
        }

        try {
            return DB::transaction(function () use (
                $encodedThumbnail,
                $image,
                $thumbnailPath,
            ): Image {
                $thumbnail = Image::query()->create([
                    'name' => $this->thumbnailName($image),
                    'mime_type' => $encodedThumbnail->mediaType(),
                    'size' => $encodedThumbnail->size(),
                    'url' => "/storage/{$thumbnailPath}",
                ]);

                $image->update(['thumbnail_id' => $thumbnail->getKey()]);
                $image->setRelation('thumbnail', $thumbnail);

                return $thumbnail;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($thumbnailPath);

            throw $exception;
        }
    }

    public function deleteIfUnreferenced(Image $image): void
    {
        if ($image->items()->exists()
            || $image->variation()->exists()
            || $image->order()->exists()
            || $image->originalImage()->exists()
        ) {
            return;
        }

        $storagePath = $this->storagePath($image);
        $thumbnail = $image->thumbnail;

        $image->delete();

        if ($storagePath !== null) {
            Storage::disk('public')->delete($storagePath);
        }

        if ($thumbnail) {
            $this->deleteIfUnreferenced($thumbnail);
        }
    }

    public function storagePath(Image $image): ?string
    {
        if (! $image->url || ! str_starts_with($image->url, '/storage/')) {
            return null;
        }

        return Str::after($image->url, '/storage/');
    }

    private function manager(): ImageManager
    {
        return new ImageManager($this->imageDriver());
    }

    private function imageDriver(): DriverInterface
    {
        if (extension_loaded('imagick')) {
            return new ImagickDriver;
        }

        return new GdDriver;
    }

    private function readableImageSource(Image $image): string
    {
        $storagePath = $this->storagePath($image);

        if ($storagePath !== null) {
            $path = Storage::disk('public')->path($storagePath);
        } elseif ($image->url && Str::startsWith($image->url, '/')) {
            $path = public_path(Str::after($image->url, '/'));
        } elseif ($image->data_uri) {
            return $image->data_uri;
        } else {
            throw new RuntimeException(
                "Image {$image->getKey()} does not have a readable local source.",
            );
        }

        if (! File::isFile($path)) {
            throw new RuntimeException(
                "The image source does not exist: {$path}",
            );
        }

        return $path;
    }

    private function thumbnailName(Image $image): string
    {
        $baseName = pathinfo(
            $image->name ?: "image-{$image->getKey()}",
            PATHINFO_FILENAME,
        );

        return "{$baseName}-thumbnail.webp";
    }

    private function thumbnailStoragePath(Image $image): string
    {
        $sourceIdentifier = $image->url
            ?? hash('sha256', (string) $image->data_uri);
        $uuid = Uuid::uuid5(
            Uuid::NAMESPACE_URL,
            'https://ecolla.local/image-thumbnail/'.$sourceIdentifier,
        );

        return "thumbnails/{$uuid->toString()}.webp";
    }
}
