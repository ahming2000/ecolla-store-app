<?php

namespace App\Http\Controllers;

use App\Models\Variation;
use App\Util\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function count(): JsonResponse
    {
        return response()->json(['count' => session('cart')->count()]);
    }

    public function add(): JsonResponse
    {
        $addList = request('addList');
        $cartItems = [];

        foreach ($addList as $item) {
            $variation = Variation::query()
                ->find($item['barcode']);

            if ($variation == null) return response()->json(['isAdded' => false], 400);

            $cartItems[] = new CartItem($variation, $item['quantity']);
        }

        foreach ($cartItems as $cartItem) {
            session('cart')->add($cartItem);
        }

        return response()->json(['isAdded' => true]);
    }
}
