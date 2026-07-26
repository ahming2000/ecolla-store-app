<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\IndexAdminItemsRequest;
use App\Http\Requests\Item\UpdateItemListingRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Models\Category;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function __construct(private readonly ItemService $itemService) {}

    public function page(): Response
    {
        return Inertia::render('admin/item/Index');
    }

    public function index(IndexAdminItemsRequest $request): JsonResponse
    {
        $items = $this->itemService->getAdminItems(
            keyword: $request->keyword(),
            categoryIds: $request->categoryIds(),
            outOfStock: $request->outOfStock(),
            notListed: $request->notListed(),
            sortBy: $request->sortBy(),
            sortDirection: $request->sortDirection(),
            page: $request->page(),
            perPage: $request->perPage(),
        );

        return response()->json($items);
    }

    public function store(): JsonResponse
    {
        $data = request()->validate([
            'name' => 'required',
        ]);

        $item = new Item([
            'name' => $data['name'],
        ]);

        $flag = $item->save();

        if (! $flag) {
            return response()->json(['message' => 'Item failed to create.'], 500);
        }

        $item->categories()->attach(Category::DEFAULT_CATEGORY_ID);
        $item->load(['categories', 'origin', 'images', 'variations.image']);

        return response()->json($item);
    }

    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $item, $request): void {
            $item->update(Arr::except($data, ['category_ids']));
            $item->categories()->sync($request->categoryIds());
        });

        $item = $item->refresh()
            ->load(['categories', 'origin', 'images', 'variations.image']);

        return response()->json($item);
    }

    public function updateListing(
        UpdateItemListingRequest $request,
        Item $item,
    ): JsonResponse {
        $item->update($request->validated());

        return response()->json($item->only(['id', 'is_listed']));
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json([], 204);
    }

    public function resetViewCount(Item $item): JsonResponse
    {
        Gate::authorize('update', $item);

        $item->update(['view_count' => 0]);

        return response()->json(
            $item->only(['id', 'view_count', 'sold_count']),
        );
    }

    public function resetSoldCount(Item $item): JsonResponse
    {
        Gate::authorize('update', $item);

        $item->update(['sold_count' => 0]);

        return response()->json(
            $item->only(['id', 'view_count', 'sold_count']),
        );
    }

    public function enableItem(): JsonResponse
    {
        return response()->json();
    }

    public function disableItem(): JsonResponse
    {
        return response()->json();
    }
}
