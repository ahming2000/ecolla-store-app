<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function landingPage(): Factory|View|Application
    {
        $highestViewItems = Item::query()
            ->orderBy('view_count', 'DESC')
            ->limit(10)
            ->get();

        $highestSoldItems = Item::query()
            ->orderBy('sold', 'DESC')
            ->limit(10)
            ->get();

        return view('listing.info.landing', compact('highestViewItems', 'highestSoldItems'));
    }

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
