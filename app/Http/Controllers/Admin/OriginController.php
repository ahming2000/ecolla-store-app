<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Origin\SaveOriginRequest;
use App\Models\Origin;
use App\Services\OriginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class OriginController extends Controller
{
    public function __construct(private readonly OriginService $originService) {}

    public function index(): JsonResponse
    {
        $origins = $this->originService->getOriginsWithItemCount(true);

        return response()->json($origins);
    }

    public function store(SaveOriginRequest $request): RedirectResponse
    {
        Origin::query()->create($request->originData());

        return back();
    }

    public function update(
        SaveOriginRequest $request,
        Origin $origin,
    ): RedirectResponse {
        $origin->update($request->originData());

        return back();
    }

    public function destroy(Origin $origin): RedirectResponse
    {
        if ($origin->items()->exists()) {
            return back()->withErrors([
                'origin' => 'An origin assigned to items cannot be deleted.',
            ]);
        }

        $origin->delete();

        return back();
    }
}
