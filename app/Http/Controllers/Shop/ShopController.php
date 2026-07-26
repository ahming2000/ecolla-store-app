<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\ItemService;
use App\Services\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function landingPage(): Response
    {
        $highestViewCountItems = $this->itemService->getHighestViewCountItems();
        $highestSoldCountItems = $this->itemService->getHighestSoldCountItems();

        return Inertia::render(
            'shop/landing/Index',
            compact('highestViewCountItems', 'highestSoldCountItems'),
        );
    }

    public function paymentMethodPage(): Response
    {
        $paymentMethods = $this->paymentMethodService->getPaymentMethods();

        return Inertia::render(
            'shop/payment-method/Index',
            compact('paymentMethods'),
        );
    }

    public function getAllPaymentMethods(): JsonResponse
    {
        $paymentMethods = $this->paymentMethodService->getPaymentMethods();

        return response()->json($paymentMethods);
    }
}
