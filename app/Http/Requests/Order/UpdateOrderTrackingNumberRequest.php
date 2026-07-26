<?php

namespace App\Http\Requests\Order;

use App\Enums\DeliveryMode;
use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderTrackingNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && $order->delivery_mode === DeliveryMode::DELIVERY
            && $this->user()?->can('updateTrackingNumber', $order) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tracking_no' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $trackingNumber = $this->input('tracking_no');

        if (! is_string($trackingNumber)) {
            return;
        }

        $trackingNumber = trim($trackingNumber);

        $this->merge([
            'tracking_no' => $trackingNumber === '' ? null : $trackingNumber,
        ]);
    }
}
