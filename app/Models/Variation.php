<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

class Variation extends Model
{
    use HasFactory, ReturnRegionDateTime;

    protected $primaryKey = 'barcode';

    public $incrementing = false;

    protected $fillable = [
        'barcode',
        'name',
        'name_en',
        'price',
        'weight',
        'image',
        'stock',
        'discount_start',
        'discount_end',
        'discount_rate',
        'item_id',
    ];

    public function getDiscountRate(): float
    {
        if ($this->discount_start == null) return 1.0;

        $today = Date::now();

        if ($today->gte(Date::parse($this->discount_start))) {
            if ($this->discount_end == null) return $this->discount_rate;

            if ($today->lte(Date::parse($this->discount_end)->addDay())) {
                return $this->discount_rate;
            } else {
                return 1.0;
            }
        } else {
            return 1.0;
        }
    }

    public function getDiscountLabel(): string
    {
        $percentage = number_format((1 - $this->getDiscountRate()) * 100, 0);
        return $percentage . '% OFF';
    }

    public function getPrice(): string
    {
        return number_format($this->price * $this->getDiscountRate(), 2, '.', '');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
