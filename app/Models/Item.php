<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory, ReturnRegionDateTime;

    protected $fillable = [
        'name',
        'name_en',
        'desc',
        'origin_id',
    ];

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class);
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
