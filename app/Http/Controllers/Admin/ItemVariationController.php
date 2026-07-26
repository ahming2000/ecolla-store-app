<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Variation\CreateVariationRequest;
use App\Http\Requests\Variation\UpdateVariationRequest;
use App\Models\Item;
use App\Models\ItemVariation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ItemVariationController extends Controller
{
    public function store(CreateVariationRequest $request, Item $item): JsonResponse
    {
        $variation = $item->variations()->create($request->validated());
        $variation->load('image');

        return response()->json($variation, Response::HTTP_CREATED);
    }

    public function update(UpdateVariationRequest $request, Item $item, ItemVariation $variation): JsonResponse
    {
        $variation->update($request->validated());
        $variation->refresh()->load('image');

        return response()->json($variation);
    }

    public function destroy(Item $item, ItemVariation $variation): JsonResponse
    {
        DB::transaction(function () use ($item, $variation): void {
            $variation->delete();

            if (! $item->variations()->exists()) {
                $item->update(['is_listed' => false]);
            }
        });

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
