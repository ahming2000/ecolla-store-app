<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function loginPage(): Factory|View|Application
    {
        return view('management.auth.login');
    }

    public function login(): RedirectResponse
    {
        $credentials = request()->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        $credentials = array_merge($credentials, ['is_enabled' => true]);

        if (auth()->attempt($credentials, request('remember'))) {
            request()->session()->regenerate();

            return redirect()->intended('/management');
        }

        return back()->withErrors([
            'password' => '账户ID或者密码错误',
        ]);
    }

    public function logout(): RedirectResponse
    {
        auth()->guard()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return response()->redirectToRoute('management.login.index');
    }
}
