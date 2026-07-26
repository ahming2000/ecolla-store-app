<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Variation\StoreVariationImageRequest;
use App\Models\Image;
use App\Models\Item;
use App\Models\ItemVariation;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ItemVariationImageController extends Controller
{
    public function __construct(private readonly ImageService $imageService) {}

    public function store(
        StoreVariationImageRequest $request,
        Item $item,
        ItemVariation $variation,
    ): JsonResponse {
        $image = Image::query()->findOrFail(
            $request->safe()->integer('image_id'),
        );
        $previousImage = $variation->image;

        $variation->image()->associate($image);
        $variation->save();

        if ($previousImage && ! $previousImage->is($image)) {
            $this->imageService->deleteIfUnreferenced($previousImage);
        }

        return response()->json($variation->refresh()->load('image'));
    }

    public function destroy(
        Item $item,
        ItemVariation $variation,
    ): JsonResponse {
        Gate::authorize('update', $item);

        $image = $variation->image;

        $variation->image()->dissociate();
        $variation->save();

        if ($image) {
            $this->imageService->deleteIfUnreferenced($image);
        }

        return response()->json($variation->refresh()->load('image'));
    }
}
