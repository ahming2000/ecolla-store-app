<?php

namespace App\Http\Controllers;

use App\Models\Variation;
use App\Util\Cart;
use App\Util\CartItem;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function cartPage(): Factory|View|Application
    {
        return view('listing.cart.index');
    }

    public function count(): JsonResponse
    {
        $cart = Cart::useSession();
        return response()->json(['count' => $cart->count()]);
    }

    public function add(): JsonResponse
    {
        $addList = request('addList');

        $cart = Cart::useSession();
        $cartItems = [];

        foreach ($addList as $item) {
            $variation = Variation::query()
                ->find($item['barcode']);

            if ($variation == null) return response()->json(['isAdded' => false], 400);

            $cartItems[] = new CartItem($variation, $item['quantity']);
        }

        foreach ($cartItems as $cartItem) {
            $cart->add($cartItem);
            $cart->saveSession();
        }

        return response()->json(['isAdded' => true]);
    }

    public function remove(): JsonResponse
    {
        $barcode = request('barcode');

        $cart = Cart::useSession();
        $cart->remove($barcode);
        $cart->saveSession();

        return response()->json(['isRemoved' => true]);
    }

    public function reset(): JsonResponse
    {
        $cart = Cart::useSession();
        $cart->reset();
        $cart->saveSession();

        return response()->json(['isReset' => true]);
    }

    public function updateQuantity(): JsonResponse
    {
        $barcode = request('barcode');
        $quantity = request('quantity');

        $cart = Cart::useSession();

        if ($cart->adjust($barcode, $quantity)) {
            $cart->saveSession();
            return response()->json(['isUpdated' => true]);
        } else {
            return response()->json(['isUpdated' => false], 400);
        }
    }

    public function updateOrderMode(): JsonResponse
    {
        $orderMode = request('orderMode');

        $cart = Cart::useSession();

        $cart->orderMode = $orderMode;
        $cart->saveSession();

        return response()->json(['isUpdated' => true]);
    }
}
