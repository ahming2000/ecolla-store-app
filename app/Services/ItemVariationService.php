<?php

namespace App\Services;

use App\Models\ItemVariation;
use Illuminate\Database\Eloquent\Collection;

class ItemVariationService
{
    /**
     * @param  array<int, string>  $barcode
     * @return Collection<int, ItemVariation>
     */
    public function getItemVariationsByBarcode(array $barcode): Collection
    {
        return ItemVariation::query()
            ->with([
                'image.thumbnail',
                'item.images.thumbnail',
                'item.variations.image.thumbnail',
            ])
            ->whereIn('barcode', $barcode)
            ->get();
    }
}
