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

    public function dashboardPage(): Factory|View|Application
    {
        return view('management.dashboard.index');
    }

    public function changingLogPage(): Factory|View|Application
    {
        $notes = json_decode(file_get_contents(__DIR__.'/../../../docs/changing-logs.json'));

        return view('management.changing-log.index', compact('notes'));
    }
}
