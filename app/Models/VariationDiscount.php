<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariationDiscount extends Model
{
    use HasFactory, ReturnRegionDateTime;

    protected $primaryKey = 'barcode';

    public $incrementing = false;

    protected $fillable = [
        'variation_id',
        'start',
        'end',
        'rate',
    ];

    public function variation(): BelongsTo
    {
        return $this->belongsTo(Variation::class);
    }
}
