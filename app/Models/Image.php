<?php

namespace App\Models;

use Database\Factories\ImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Image extends Model
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'mime_type',
        'size',
        'url',
        'data_uri',
    ];

    protected $appends = [
        'src',
    ];

    public function getSrcAttribute(): ?string
    {
        return $this->url ?? $this->data_uri;
    }

    /**
     * @return BelongsToMany<Item, $this>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_images');
    }

    /**
     * @return HasOne<ItemVariation, $this>
     */
    public function variation(): HasOne
    {
        return $this->hasOne(ItemVariation::class, 'image_id');
    }

    /**
     * @return HasOne<Order, $this>
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'receipt_image_id');
    }
}
