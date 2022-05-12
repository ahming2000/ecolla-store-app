<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function changeLanguage(string $lang): RedirectResponse
    {
        session()->put('lang', $lang);
        return response()->redirectTo(request('redirectTo') ?? '/');
    }
}
