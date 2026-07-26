<?php

namespace App\Services;

use App\Models\Origin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OriginService
{
    /**
     * @return Collection<int, Origin>
     */
    public function getOriginsWithItemCount(bool $countUnlistedItem = false): Collection
    {
        return Origin::query()
            ->withCount(['items' => function (Builder $query) use ($countUnlistedItem): void {
                if (! $countUnlistedItem) {
                    $query->where('is_listed', '=', true);
                }
            }])
            ->get();
    }
}
