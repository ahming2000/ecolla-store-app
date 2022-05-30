<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    use HasFactory, ReturnRegionDateTime;

    protected $primaryKey = 'name';

    public static function getShippingFee(): float
    {
        return floatval(self::query()->where('name', '=', 'shipping_fee')->first()->value);
    }

    public static function freeShippingIsActivated(): bool
    {
        return boolval(self::query()->where('name', '=', 'freeShipping_isActivated')->first()->value);
    }

    public static function getFreeShippingThreshold(): float
    {
        return floatval(self::query()->where('name', '=', 'freeShipping_threshold')->first()->value);
    }

    public static function getFreeShippingDesc(): string
    {
        return self::query()->where('name', '=', 'freeShipping_desc')->first()->value;
    }
}
