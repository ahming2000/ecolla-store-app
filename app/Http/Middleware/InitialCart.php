<?php

namespace App\Http\Middleware;

use App\Util\Cart;
use Closure;
use Illuminate\Http\Request;

class InitialCart
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (session('cart') == null) {
            session()->put('cart', new Cart());
        }
        else {
            $cart = Cart::useSession();
            $cart->saveSession();
        }

        return $next($request);
    }
}
