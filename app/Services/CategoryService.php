<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * @return Collection<int, Category>
     */
    public function getCategoriesWithItemCount(bool $countUnlistedItem = false): Collection
    {
        return Category::query()
            ->withCount(['items' => function (Builder $query) use ($countUnlistedItem): void {
                if (! $countUnlistedItem) {
                    $query->where('is_listed', '=', true);
                }
            }])
            ->get();
    }
}
