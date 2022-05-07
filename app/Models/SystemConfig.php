<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    use HasFactory, ReturnRegionDateTime;

    public static function getShippingFee(): float
    {
        return floatval(self::query()->where('name', '=', 'shipping_fee')->first()->value);
    }

    public static function shippingDiscountIsActivated(): bool
    {
        return boolval(self::query()->where('name', '=', 'shipping_discount_is_activated')->first()->value);
    }

    public static function getShippingDiscountThreshold(): float
    {
        return floatval(self::query()->where('name', '=', 'shipping_discount_threshold')->first()->value);
    }

    public static function getShippingDiscountDesc(): string
    {
        return self::query()->where('name', '=', 'shipping_discount_desc')->first()->value;
    }
}
