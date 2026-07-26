<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\IndexShopItemsRequest;
use App\Models\Category;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function __construct(private readonly ItemService $itemService) {}

    public function page(): Response
    {
        return Inertia::render('shop/item-list/Index');
    }

    public function index(IndexShopItemsRequest $request): JsonResponse
    {
        $barcodes = $request->barcodes();

        $items = $this->itemService->getItems(
            callback: function (Builder $query) use ($barcodes): Builder {
                return $query->when($barcodes !== [], function (Builder $query) use ($barcodes): void {
                    $query->whereHas('variations', function (Builder $query) use ($barcodes): void {
                        $query->whereIn('barcode', $barcodes);
                    });
                });
            },
        );

        return response()->json($items);
    }

    public function show(Item $item): Response
    {
        if (! $item->is_listed) {
            abort(404);
        }

        $item = $this->itemService->getItem($item);
        $this->itemService->itemViewed($item);

        $categoryIds = $item->categories
            ->map(fn (Category $category): int => (int) $category->getKey())
            ->values()
            ->all();
        $similarItems = $this->itemService->getSameCategoryItems($categoryIds);
        $similarItemIds = $similarItems
            ->map(fn (Item $similarItem): int => (int) $similarItem->getKey())
            ->values()
            ->all();
        $randomItems = $this->itemService->getRandomItems($similarItemIds);

        return Inertia::render(
            'shop/item/Index',
            compact('item', 'similarItems', 'randomItems'),
        );
    }
}
