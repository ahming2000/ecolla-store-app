<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function loginPage(): Response
    {
        return Inertia::render('admin/auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function login(): RedirectResponse
    {
        $data = request()->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = request()->boolean('remember');

        if (! auth()->attempt([...$data, 'is_enabled' => true], $remember)) {
            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        request()->session()->regenerate();

        return redirect()->intended(route('admin.dashboard.page', absolute: false));
    }

    public function logout(): RedirectResponse
    {
        auth()->guard()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->redirectToRoute('login');
    }
}
