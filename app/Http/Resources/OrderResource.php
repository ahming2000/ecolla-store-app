<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use UnexpectedValueException;

class OrderResource extends AppJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource instanceof Order) {
            throw new UnexpectedValueException('OrderResource requires an Order model.');
        }

        return [
            ...$this->serializeDatesForUser(
                $request,
                parent::toArray($request),
            ),
            'created_at_display' => $this->formatDateTimeForUser(
                $request,
                $this->resource->created_at,
                'Y/m/d H:i',
            ),
        ];
    }
}
