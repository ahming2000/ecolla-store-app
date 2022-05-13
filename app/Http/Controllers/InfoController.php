<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function paymentMethodPage(): Factory|View|Application
    {
        $payments = PaymentMethod::all();

        return view('listing.info.payment-method', compact('payments'));
    }
}
