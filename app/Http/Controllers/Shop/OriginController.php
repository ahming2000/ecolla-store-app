<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\OriginService;
use Illuminate\Http\JsonResponse;

class OriginController extends Controller
{
    public function __construct(private readonly OriginService $originService) {}

    public function index(): JsonResponse
    {
        $origins = $this->originService->getOriginsWithItemCount();

        return response()->json($origins);
    }
}
