<?php

namespace App\Models;

use App\Traits\FormatDateToSerialize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory, FormatDateToSerialize;

    protected $fillable = [
        'name',
        'name_en',
        'desc',
        'origin_id',
    ];

    public function getCoverImage(): string
    {
        if (sizeof($this->images) == 0) {
            foreach ($this->variations as $variation) {
                if ($variation->image) {
                    return $variation->image;
                }
            }
        } else {
            return $this->images[0]->image;
        }

        return asset('/images/ecolla.png');
    }

    public function getDisplayablePrice(): string
    {
        $min = 0.0;
        $max = 0.0;

        foreach ($this->variations as $variation) {
            $price = $variation->price;

            // TODO: Include variation discount

            if ($price > $max) {
                $max = $price;
            }

            if ($price < $min or $min == 0.0) {
                $min = $price;
            }
        }

        if ($min == $max) {
            return 'RM' . number_format($min, 2);
        } else {
            return 'RM' . number_format($min, 2) . ' - RM' . number_format($max, 2);
        }
    }

    public function getTotalStock(): int
    {
        $total = 0;

        foreach ($this->variations as $variation){
            $total += $variation->stock;
        }

        return $total;
    }

    public function getTotalImageCount(): int
    {
        $total = sizeof($this->images) ?? 0;

        foreach ($this->variations as $variation){
            if($variation->image != null){
                $total++;
            }
        }

        return $total;
    }

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ItemImage::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
