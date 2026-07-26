<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\ItemVariation;
use App\Models\Order;
use App\Services\Common\Cart\Cart;
use App\Services\Common\Cart\CartItem;
use App\Services\ItemVariationService;
use App\Services\OrderService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly ItemVariationService $itemVariationService,
        private readonly OrderService $orderService,
        private readonly SettingService $settingService,
    ) {}

    public function cartPage(): Response
    {
        return Inertia::render('shop/cart/Index', [
            'shipping' => $this->settingService->getShippingSettings(),
        ]);
    }

    public function checkoutPage(): Response
    {
        return Inertia::render('shop/checkout/Index', [
            'shipping' => $this->settingService->getShippingSettings(),
        ]);
    }

    public function checkoutSuccessfulPage(Order $order, Request $request): Response
    {
        $checkoutOrderIds = $request->session()->get('checkout_order_ids', []);

        abort_unless(
            is_array($checkoutOrderIds)
            && in_array($order->getKey(), $checkoutOrderIds, true),
            404,
        );

        return Inertia::render('shop/checkout/Successful', [
            'order' => OrderResource::make($order)->resolve($request),
        ]);
    }

    public function verifyCart(): JsonResponse
    {
        $cart = Cart::from(
            deliveryMode: request('deliveryMode'),
            cartItems: request('items') ?? [],
        );

        $barcodeFromCart = $cart->cartItems
            ->pluck('variation.barcode')
            ->toArray();

        $variations = $this->itemVariationService
            ->getItemVariationsByBarcode($barcodeFromCart)
            ->keyBy('barcode');

        $cart->cartItems = $cart->cartItems
            ->map(function (CartItem $cartItem) use ($variations) {
                $variation = $variations->get($cartItem->variation->barcode);

                if (! $variation instanceof ItemVariation || $variation->stock <= 0) {
                    return null;
                }

                if ($cartItem->quantity > $variation->stock) {
                    $cartItem->quantity = $variation->stock;
                }

                $cartItem->variation = clone $variation;
                $cartItem->item = clone $variation->item;

                return $cartItem;
            })
            ->filter(function (?CartItem $cartItem) {
                return $cartItem !== null;
            })
            ->values();

        return response()->json($cart->toArray());
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $data = $request->validated();

        $cart = Cart::from(
            deliveryMode: $data['cart']['deliveryMode'],
            cartItems: $data['cart']['items'],
        );

        $order = DB::transaction(function () use ($data, $cart) {
            $variations = ItemVariation::query()
                ->whereIn(
                    'barcode',
                    $cart->cartItems->pluck('variation.barcode')->all(),
                )
                ->lockForUpdate()
                ->get()
                ->keyBy('barcode');
            $subtotal = 0.0;

            $orderedItems = $cart->cartItems->map(function (CartItem $cartItem) use ($variations, &$subtotal) {
                $variation = $variations->get($cartItem->variation->barcode);

                if (
                    ! $variation instanceof ItemVariation
                    || $variation->getKey() !== $cartItem->variation->getKey()
                    || $variation->item_id !== $cartItem->item->getKey()
                    || $variation->stock < $cartItem->quantity
                ) {
                    throw ValidationException::withMessages([
                        'cart.items' => 'One or more cart items are no longer available.',
                    ]);
                }

                $subtotal += $variation->final_price * $cartItem->quantity;
                $variation->update([
                    'stock' => $variation->stock - $cartItem->quantity,
                ]);

                return [
                    'name' => $variation->name,
                    'name_en' => $variation->name_en,
                    'barcode' => $variation->barcode,
                    'price' => $variation->price,
                    'sale_price' => $variation->sale_price,
                    'quantity' => $cartItem->quantity,
                ];
            });

            $cart->shippingFee = $this->settingService->calculateShippingFee(
                $cart->deliveryMode,
                $subtotal,
            );

            return $this->orderService->createOrder([
                'delivery_mode' => $cart->deliveryMode,
                'shipping_fee' => $cart->shippingFee,
                'payment_method_id' => $data['checkoutForm']['payment_method']['id'],
                'receipt_image_id' => $data['checkoutForm']['receipt_image']['id'],
                'cus_name' => $data['checkoutForm']['cus_name'],
                'cus_phone' => $data['checkoutForm']['cus_phone'],
                'cus_address' => $data['checkoutForm']['cus_address'],
            ], $orderedItems);
        });

        $request->session()->push('checkout_order_ids', $order->getKey());

        return response()->json(
            OrderResource::make($order)->resolve($request),
        );
    }
}
