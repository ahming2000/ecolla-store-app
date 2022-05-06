<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'item_id',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function discount(): HasOne
    {
        return $this->hasOne(VariationDiscount::class);
    }
}
