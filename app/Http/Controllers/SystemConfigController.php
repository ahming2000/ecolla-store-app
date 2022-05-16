<?php

namespace App\Http\Controllers;

use App\Models\SystemConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemConfigController extends Controller
{
    public function shippingFeeConfig(): JsonResponse
    {
        return response()->json(
            [
                'fee' => SystemConfig::getShippingFee(),
                'hasDiscount' => SystemConfig::shippingDiscountIsActivated(),
                'discountThreshold' => SystemConfig::getShippingDiscountThreshold(),
                'discountDesc' => SystemConfig::getShippingDiscountDesc(),
            ]
        );
    }
}
