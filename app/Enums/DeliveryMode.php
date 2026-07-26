<?php

namespace App\Enums;

use App\Traits\Enum\CaseValues;

enum DeliveryMode: string
{
    use CaseValues;

    case SELF_PICKUP = '预购取货';
    case DELIVERY = '外送';

    public static function getLabel(
        self $deliveryMode,
        Language $language,
    ): string {
        if ($language == Language::ZH) {
            return $deliveryMode->value;
        } else {
            return match ($deliveryMode) {
                self::SELF_PICKUP => 'Self Pickup',
                self::DELIVERY => 'Delivery',
            };
        }
    }
}
