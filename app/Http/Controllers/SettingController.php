<?php

namespace App\Http\Controllers;

use App\Util\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function changeLanguage(string $lang): RedirectResponse
    {
        session('cart')->lang = $lang;
        return response()->redirectTo(request('redirectTo') ?? '/');
    }
}
