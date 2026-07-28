<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardSalesRequest;
use App\Services\DashboardSalesService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardSalesService $dashboardSalesService,
    ) {}

    public function page(DashboardSalesRequest $request): Response
    {
        return Inertia::render('admin/dashboard/DashboardPage', [
            'dashboard' => $this->dashboardSalesService->getOverview(
                $request->period(),
                $request->selectedDate(),
                $request->timezone(),
            ),
        ]);
    }
}
