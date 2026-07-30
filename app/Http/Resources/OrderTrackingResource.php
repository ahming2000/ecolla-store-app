<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderedItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use UnexpectedValueException;

class OrderTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource instanceof Order) {
            throw new UnexpectedValueException('OrderTrackingResource requires an Order model.');
        }

        $items = $this->resource->items->map(function (OrderedItem $item): array {
            $unitPrice = (float) ($item->sale_price ?? $item->price);

            return [
                'id' => $item->getKey(),
                'name' => $item->name,
                'name_en' => $item->name_en,
                'barcode' => $item->barcode,
                'quantity' => $item->quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $item->quantity, 2),
            ];
        });
        $subtotal = (float) $items->sum('line_total');
        $shippingFee = (float) $this->resource->shipping_fee;

        return [
            'reference_num' => $this->resource->reference_num,
            'delivery_mode' => $this->resource->delivery_mode->value,
            'status' => $this->resource->status->value,
            'tracking_no' => $this->resource->tracking_no,
            'shipping_fee' => $shippingFee,
            'subtotal' => $subtotal,
            'total' => round($subtotal + $shippingFee, 2),
            'note' => $this->resource->note,
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
            'items' => $items->all(),
        ];
    }
}
