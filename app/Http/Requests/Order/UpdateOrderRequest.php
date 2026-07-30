<?php

namespace App\Http\Requests\Order;

use App\Enums\DeliveryMode;
use App\Models\Order;
use App\Models\OrderedItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && $this->user()?->can('update', $order) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $order = $this->route('order');
        $orderId = $order instanceof Order ? $order->getKey() : null;

        return [
            'delivery_mode' => [
                'bail',
                'required',
                Rule::enum(DeliveryMode::class),
            ],
            'shipping_fee' => [
                'bail',
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:999999.99',
            ],
            'note' => ['nullable', 'string', 'max:255'],
            'cus_name' => [
                'bail',
                Rule::requiredIf($this->isDelivery()),
                'nullable',
                'string',
                'max:255',
            ],
            'cus_phone' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'cus_address' => [
                'bail',
                Rule::requiredIf($this->isDelivery()),
                'nullable',
                'string',
                'max:255',
            ],
            'items' => ['present', 'array', 'max:500'],
            'items.*' => ['required', 'array:id,quantity,effective_price'],
            'items.*.id' => [
                'bail',
                'required',
                'integer',
                'distinct:strict',
                Rule::exists(OrderedItem::class, 'id')
                    ->where('order_id', $orderId)
                    ->whereNull('deleted_at'),
            ],
            'items.*.quantity' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:999999',
            ],
            'items.*.effective_price' => [
                'bail',
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:999999.99',
            ],
            'cancel_when_empty' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array{
     *     delivery_mode: string,
     *     shipping_fee: float,
     *     note: string|null,
     *     cus_name: string|null,
     *     cus_phone: string,
     *     cus_address: string|null,
     *     items: list<array{
     *         id: int,
     *         quantity: int,
     *         effective_price: float
     *     }>,
     *     cancel_when_empty: bool
     * }
     */
    public function orderData(): array
    {
        $validated = $this->validated();
        $items = [];

        foreach ($validated['items'] as $item) {
            $items[] = [
                'id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
                'effective_price' => (float) $item['effective_price'],
            ];
        }

        return [
            'delivery_mode' => (string) $validated['delivery_mode'],
            'shipping_fee' => (float) $validated['shipping_fee'],
            'note' => is_string($validated['note'] ?? null)
                ? $validated['note']
                : null,
            'cus_name' => is_string($validated['cus_name'] ?? null)
                ? $validated['cus_name']
                : null,
            'cus_phone' => (string) $validated['cus_phone'],
            'cus_address' => is_string($validated['cus_address'] ?? null)
                ? $validated['cus_address']
                : null,
            'items' => $items,
            'cancel_when_empty' => (bool) $validated['cancel_when_empty'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $validator->errors()->isEmpty()
                    && $this->input('items') === []
                    && ! $this->boolean('cancel_when_empty')
                ) {
                    $validator->errors()->add(
                        'items',
                        'An order without items must be canceled.',
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'note' => $this->trimmedNullableInput('note'),
            'cus_name' => $this->trimmedNullableInput('cus_name'),
            'cus_phone' => $this->trimmedNullableInput('cus_phone'),
            'cus_address' => $this->trimmedNullableInput('cus_address'),
        ]);
    }

    private function isDelivery(): bool
    {
        return $this->input('delivery_mode')
            === DeliveryMode::DELIVERY->value;
    }

    private function trimmedNullableInput(string $key): mixed
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        $trimmedValue = Str::of($value)->trim()->toString();

        return $trimmedValue === '' ? null : $trimmedValue;
    }
}
