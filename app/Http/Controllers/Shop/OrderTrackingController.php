<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\TrackOrderRequest;
use App\Http\Resources\OrderTrackingResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OrderTrackingController extends Controller
{
    public function page(Request $request): Response
    {
        $referenceNumber = $request->query('reference');

        return Inertia::render('shop/order-tracking/OrderTrackingPage', [
            'initialReferenceNumber' => is_string($referenceNumber)
                ? Str::limit(trim($referenceNumber), 255, '')
                : '',
        ]);
    }

    public function lookup(TrackOrderRequest $request): JsonResponse
    {
        $normalizedPhoneNumber = $this->normalizePhoneNumber(
            $request->phone(),
        );
        $order = Order::query()
            ->with('items')
            ->where('reference_num', $request->referenceNumber())
            ->latest('id')
            ->get()
            ->first(function (Order $order) use ($normalizedPhoneNumber): bool {
                return hash_equals(
                    $this->normalizePhoneNumber($order->cus_phone),
                    $normalizedPhoneNumber,
                );
            });

        if (! $order instanceof Order) {
            return response()->json([
                'message' => 'Order details could not be matched.',
            ], 404);
        }

        return response()->json(
            OrderTrackingResource::make($order)->resolve($request),
        );
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';

        if (str_starts_with($digits, '60')) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }
}
