<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateFreeShippingRequest;
use App\Http\Requests\Setting\UpdateShippingFeeRequest;
use App\Services\CategoryService;
use App\Services\OriginService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(
        private readonly OriginService $originService,
        private readonly CategoryService $categoryService,
        private readonly SettingService $settingService,
    ) {}

    public function settingPage(): Response
    {
        $origins = $this->originService->getOriginsWithItemCount(true);
        $categories = $this->categoryService->getCategoriesWithItemCount(true);
        $shipping = $this->settingService->getShippingSettings();

        return Inertia::render(
            'admin/setting/SettingPage',
            compact('origins', 'categories', 'shipping'),
        );
    }

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
