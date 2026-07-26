<?php

namespace App\Models;

use Database\Factories\OrderedItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderedItem extends Model
{
    /** @use HasFactory<OrderedItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'barcode',
        'price',
        'sale_price',
        'quantity',
    ];

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
            'sale_price' => 'float',
            'quantity' => 'integer',
        ];
    }
}
