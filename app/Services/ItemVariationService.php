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
            ->with('item')
            ->whereIn('barcode', $barcode)
            ->get();
    }
}
