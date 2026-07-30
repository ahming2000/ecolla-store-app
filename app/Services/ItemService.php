<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemService
{
    /**
     * @param  (callable(Builder<Item>): Builder<Item>)|null  $callback
     * @return Collection<int, Item>
     */
    public function getItems(bool $withUnlisted = false, ?callable $callback = null): Collection
    {
        $query = $this->itemQuery($withUnlisted);

        if ($callback) {
            $query = $callback($query);
        }

        return $query->get();
    }

    /**
     * @param  list<int>  $categoryIds
     * @return LengthAwarePaginator<int, Item>
     */
    public function getAdminItems(
        ?string $keyword,
        array $categoryIds,
        bool $outOfStock,
        bool $notListed,
        string $sortBy,
        string $sortDirection,
        int $page,
        int $perPage,
    ): LengthAwarePaginator {
        $query = $this->itemQuery(true);

        if ($keyword !== null) {
            $query->where(function (Builder $query) use ($keyword): void {
                $query
                    ->whereLike('name', "%{$keyword}%")
                    ->orWhereLike('name_en', "%{$keyword}%")
                    ->orWhereLike('desc', "%{$keyword}%")
                    ->orWhereHas('origin', function (Builder $query) use ($keyword): void {
                        $query
                            ->whereLike('name', "%{$keyword}%")
                            ->orWhereLike('name_en', "%{$keyword}%");
                    })
                    ->orWhereHas('variations', function (Builder $query) use ($keyword): void {
                        $query
                            ->whereLike('barcode', "%{$keyword}%")
                            ->orWhereLike('name', "%{$keyword}%")
                            ->orWhereLike('name_en', "%{$keyword}%");
                    });
            });
        }

        if ($categoryIds !== []) {
            $query->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->whereIn(
                    'categories.id',
                    $categoryIds,
                ),
            );
        }

        if ($outOfStock) {
            $query->whereDoesntHave(
                'variations',
                fn (Builder $query): Builder => $query->where('stock', '>', 0),
            );
        }

        if ($notListed) {
            $query->where('is_listed', false);
        }

        $sortColumn = match ($sortBy) {
            'sold_count' => 'sold_count',
            'view_count' => 'view_count',
            'name' => 'name',
            default => 'created_at',
        };
        $direction = $sortDirection === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy($sortColumn, $direction)
            ->orderBy('id', $direction)
            ->paginate(
                perPage: $perPage,
                page: $page,
            )
            ->withQueryString();
    }

    public function getItem(Item $item): Item
    {
        return $item->load([
            'variations.image.thumbnail',
            'origin',
            'images.thumbnail',
            'categories',
        ]);
    }

    /**
     * @return Collection<int, Item>
     */
    public function getHighestViewCountItems(int $count = 10): Collection
    {
        return $this->getItems(callback: function (Builder $query) use ($count): Builder {
            return $query->orderByDesc('view_count')
                ->limit($count);
        });
    }

    /**
     * @return Collection<int, Item>
     */
    public function getHighestSoldCountItems(int $count = 10): Collection
    {
        return $this->getItems(callback: function (Builder $query) use ($count): Builder {
            return $query->orderByDesc('sold_count')
                ->limit($count);
        });
    }

    /**
     * @param  array<int, int>  $categoryIds
     * @return Collection<int, Item>
     */
    public function getSameCategoryItems(array $categoryIds = [], int $maxCount = 15, int $randomMultiplier = 2): Collection
    {
        $items = $this->getItems(callback: function (Builder $query) use ($randomMultiplier, $categoryIds, $maxCount): Builder {
            $query->whereHas('categories', function (Builder $query) use ($categoryIds): void {
                $query->whereIn('category_id', $categoryIds);
            });

            $query->limit($maxCount * $randomMultiplier);

            return $query;
        });

        return $items->random(min($maxCount, $items->count()));
    }

    /**
     * @param  array<int, int>  $excludedIds
     * @return Collection<int, Item>
     */
    public function getRandomItems(array $excludedIds = [], int $maxCount = 15, int $randomMultiplier = 2): Collection
    {
        $items = $this->getItems(
            callback: fn (Builder $query): Builder => $query
                ->whereNotIn('id', $excludedIds)
                ->limit($maxCount * $randomMultiplier),
        );

        return $items->random(min($maxCount, $items->count()));
    }

    public function itemViewed(Item $item): int
    {
        return $item->increment('view_count');
    }

    /**
     * @return Builder<Item>
     */
    private function itemQuery(bool $withUnlisted): Builder
    {
        $query = Item::query()->with([
            'variations.image.thumbnail',
            'origin',
            'images.thumbnail',
            'categories',
        ]);

        if (! $withUnlisted) {
            $query->where('is_listed', true);
        }

        return $query;
    }
}
