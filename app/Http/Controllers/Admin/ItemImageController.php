<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Item;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ItemImageController extends Controller
{
    public function __construct(private readonly ImageService $imageService) {}

    public function store(Item $item, Image $image): JsonResponse
    {
        Gate::authorize('update', $item);

        $item->images()->syncWithoutDetaching([$image->id]);

        return response()->json($this->loadItemRelations($item));
    }

    public function destroy(Item $item, Image $image): JsonResponse
    {
        Gate::authorize('update', $item);
        abort_unless(
            $item->images()->whereKey($image->getKey())->exists(),
            404,
        );

        $item->images()->detach($image->id);
        $this->imageService->deleteIfUnreferenced($image);

        return response()->json($this->loadItemRelations($item));
    }

    private function loadItemRelations(Item $item): Item
    {
        return $item->refresh()
            ->load([
                'categories',
                'origin',
                'images.thumbnail',
                'variations.image.thumbnail',
            ]);
    }
}
