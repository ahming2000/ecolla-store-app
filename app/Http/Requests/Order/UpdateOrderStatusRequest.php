<?php

namespace App\Http\Requests\Order;

use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && $this->user()?->can('updateStatus', $order) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $order = $this->route('order');

        return [
            'status' => [
                'bail',
                'required',
                Rule::enum(Status::class),
            ],
            'tracking_no' => [
                'bail',
                Rule::requiredIf(
                    $order instanceof Order
                    && $order->delivery_mode === DeliveryMode::DELIVERY
                    && $this->input('status') !== Status::PENDING->value,
                ),
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
