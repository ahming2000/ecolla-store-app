<?php

namespace App\Http\Controllers;

use App\Models\SystemConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function shippingFeeConfig(): JsonResponse
    {
        return response()->json(
            [
                'shippingFee' => SystemConfig::getShippingFee(),
                'freeShippingIsActivated' => SystemConfig::freeShippingIsActivated(),
                'freeShippingThreshold' => SystemConfig::getFreeShippingThreshold(),
                'freeShippingDesc' => SystemConfig::getFreeShippingDesc(),
            ]
        );
    }


    public function updateShippingFee(): JsonResponse
    {
        $fee = request('shipping_fee');

        if (SystemConfig::query()
            ->where('name', '=', 'shipping_fee')
            ->update(['value' => $fee])
        ) {
            return response()->json(['isUpdated' => true]);
        } else {
            return response()->json(['isUpdated' => false]);
        }
    }

    public function updateShippingDiscount(): JsonResponse
    {
        if (request()->has('freeShipping_isActivated')) {
            $isActivated = request('freeShipping_isActivated');

            if (SystemConfig::query()
                ->where('name', '=', 'freeShipping_isActivated')
                ->update(['value' => $isActivated])
            ) {
                return response()->json(['isUpdated' => true]);
            } else {
                return response()->json(['isUpdated' => false]);
            }
        } else {
            $threshold = request('freeShipping_threshold');
            $desc = request('freeShipping_desc');

            $thresholdIsUpdated = SystemConfig::query()
                ->where('name', '=', 'freeShipping_threshold')
                ->update(['value' => $threshold]);

            $descIsUpdated = SystemConfig::query()
                ->where('name', '=', 'freeShipping_desc')
                ->update(['value' => $desc]);

            if ($thresholdIsUpdated && $descIsUpdated) {
                return response()->json(['isUpdated' => true]);
            } else {
                return response()->json(['isUpdated' => false]);
            }
        }
    }
}
