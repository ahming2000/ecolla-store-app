<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateFreeShippingRequest;
use App\Http\Requests\Setting\UpdateShippingFeeRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
    ) {}

    public function updateShippingFee(
        UpdateShippingFeeRequest $request,
    ): RedirectResponse {
        $this->settingService->updateShippingFee($request->shippingFee());

        return back();
    }

    public function updateFreeShipping(
        UpdateFreeShippingRequest $request,
    ): RedirectResponse {
        $this->settingService->updateFreeShipping(
            $request->isActivated(),
            $request->threshold(),
            $request->description(),
        );

        return back();
    }
}
