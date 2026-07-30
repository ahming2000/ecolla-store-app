<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'desc',
        'is_listed',
        'view_count',
        'sold_count',
        'origin_id',
    ];

    protected $appends = [
        'cover_image',
        'cover_thumbnail',
        'total_stock',
        'total_image_count',
        'all_images',
    ];

    protected static function booted(): void
    {
        static::saving(function (Item $item): void {
            if (! $item->exists || $item->isDirty(['name', 'name_en'])) {
                $item->slug = $item->generateUniqueSlug();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_listed' => 'boolean',
            'sold_count' => 'integer',
            'view_count' => 'integer',
        ];
    }

    public function getCoverImageAttribute(): ?string
    {
        return $this->resolveCoverImage()?->src;
    }

    public function getCoverThumbnailAttribute(): ?string
    {
        $image = $this->resolveCoverImage();

        if (! $image instanceof Image) {
            return null;
        }

        return $image->thumbnail instanceof Image
            ? $image->thumbnail->src
            : $image->src;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('variations')) {
            return (int) $this->variations->sum('stock');
        }

        return (int) $this->variations()->sum('stock');
    }

    public function getTotalImageCountAttribute(): int
    {
        $imageCount = $this->relationLoaded('images')
            ? $this->images->count()
            : $this->images()->count();
        $variationCount = $this->relationLoaded('variations')
            ? $this->variations->count()
            : $this->variations()->count();

        return $imageCount + $variationCount;
    }

    /**
     * @return EloquentCollection<int, Image>
     */
    public function getAllImagesAttribute(): EloquentCollection
    {
        $variationImages = $this->variations
            ->map(function (ItemVariation $variation): ?Image {
                if ($variation->image) {
                    $image = clone $variation->image;
                    $image->setAttribute('variation_id', $variation->getKey());

                    return $image;
                }

                return null;
            })
            ->filter(fn (?Image $image): bool => $image !== null);

        return $this->images->merge($variationImages);
    }

    /**
     * @return HasMany<ItemVariation, $this>
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ItemVariation::class);
    }

    /**
     * @return BelongsTo<Origin, $this>
     */
    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class);
    }

    /**
     * @return BelongsToMany<Image, $this>
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'item_images');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'item_categories');
    }

    private function generateUniqueSlug(): string
    {
        $englishName = trim((string) $this->name_en);
        $slugSource = $englishName !== '' ? $englishName : $this->name;
        $language = $englishName !== '' ? 'en' : 'zh';
        $baseSlug = Str::limit(
            Str::slug($slugSource, language: $language) ?: 'item',
            240,
            '',
        );
        $slug = $baseSlug;
        $suffix = 2;

        while (
            static::query()
                ->withTrashed()
                ->where('slug', $slug)
                ->when(
                    $this->exists,
                    fn (Builder $query): Builder => $query->where(
                        $this->getKeyName(),
                        '!=',
                        $this->getKey(),
                    ),
                )
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function resolveCoverImage(): ?Image
    {
        $image = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->with('thumbnail')->first();

        if ($image && ($image->url || $image->data_uri)) {
            return $image;
        }

        $variation = $this->relationLoaded('variations')
            ? $this->variations->first(
                fn (ItemVariation $variation): bool => $variation->image_id !== null,
            )
            : $this->variations()
                ->whereNotNull('image_id')
                ->with('image.thumbnail')
                ->first();

        return $variation?->image;
    }
}
