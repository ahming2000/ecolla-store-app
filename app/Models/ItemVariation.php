<?php

namespace App\Models;

use Database\Factories\ItemVariationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemVariation extends Model
{
    /** @use HasFactory<ItemVariationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barcode',
        'name',
        'name_en',
        'price',
        'sale_price',
        'weight',
        'stock',
    ];

    protected $appends = [
        'price_text',
        'sale_price_text',
        'final_price',
        'final_price_text',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'sale_price' => 'float',
            'weight' => 'float',
            'stock' => 'integer',
        ];
    }

    public function getPriceTextAttribute(): string
    {
        return 'RM '.number_format($this->price, 2, '.', '');
    }

    public function getSalePriceTextAttribute(): string
    {
        if ($this->sale_price === null) {
            return '';
        }

        return 'RM '.number_format($this->sale_price, 2, '.', '');
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->sale_price === null ? $this->price : $this->sale_price;
    }

    public function getFinalPriceTextAttribute(): string
    {
        return 'RM '.number_format($this->getFinalPriceAttribute(), 2, '.', '');
    }

    public function getWeightTextAttribute(): string
    {
        return number_format($this->weight, 3, '.', '').' kg';
    }

    /**
     * @return BelongsTo<Image, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
