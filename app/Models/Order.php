<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, ReturnRegionDateTime;

    public $incrementing = false;

    protected $fillable = [
        'mode',
        'tracking_id',
        'shipping_fee',
        'payment_method',
        'status',
        'receipt_image',
        'note',
    ];

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
