<?php

namespace App\Http\Controllers;

use App\Util\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function changeLanguage(string $lang): RedirectResponse
    {
        $cart = new Cart();
        $cart->insert(session('cart'));
        $cart->lang = $lang;
        session()->put('cart', $cart);

        return response()->redirectTo(request('redirectTo') ?? '/');
    }
}
