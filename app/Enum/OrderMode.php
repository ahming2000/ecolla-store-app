<?php

namespace App\Enum;

class OrderMode
{
    public static int $SELF_PICKUP = 0;
    public static int $DELIVERY = 1;

    public static function getLabel($orderMode, $lang): string
    {
        if ($lang == 'en') {
            return match ($orderMode) {
                self::$DELIVERY => 'Delivery',
                default => 'Pick-up',
            };
        } else {
            return match ($orderMode) {
                self::$DELIVERY => '外送',
                default => '预购取货',
            };
        }
    }
}
