<?php

namespace App\Http\Requests\Cart;

use App\Enums\DeliveryMode;
use App\Models\Image;
use App\Models\Item;
use App\Models\ItemVariation;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart' => ['required', 'array'],
            'cart.deliveryMode' => [
                'bail',
                'required',
                Rule::enum(DeliveryMode::class),
            ],
            'cart.items' => ['required', 'array', 'min:1'],
            'cart.items.*.item' => ['required', 'array'],
            'cart.items.*.item.id' => [
                'bail',
                'required',
                'integer',
                Rule::exists(Item::class, 'id')->whereNull('deleted_at'),
            ],
            'cart.items.*.variation' => ['required', 'array'],
            'cart.items.*.variation.id' => [
                'bail',
                'required',
                'integer',
                'distinct:strict',
                Rule::exists(ItemVariation::class, 'id')->whereNull('deleted_at'),
            ],
            'cart.items.*.variation.barcode' => [
                'bail',
                'required',
                'string',
                'distinct:strict',
            ],
            'cart.items.*.quantity' => [
                'bail',
                'required',
                'integer',
                'min:1',
            ],
            'cart.shippingFee' => ['sometimes', 'numeric'],

            'checkoutForm' => ['required', 'array'],
            'checkoutForm.cus_name' => [
                Rule::requiredIf($this->isDelivery()),
                'nullable',
                'string',
                'max:255',
            ],
            'checkoutForm.cus_phone' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'checkoutForm.cus_address' => [
                Rule::requiredIf($this->isDelivery()),
                'nullable',
                'string',
                'max:255',
            ],
            'checkoutForm.receipt_image' => ['required', 'array'],
            'checkoutForm.receipt_image.id' => [
                'bail',
                'required',
                'integer',
                Rule::exists(Image::class, 'id'),
            ],
            'checkoutForm.payment_method' => ['required', 'array'],
            'checkoutForm.payment_method.id' => [
                'bail',
                'required',
                'integer',
                Rule::exists(PaymentMethod::class, 'id')
                    ->where('is_enabled', true)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var list<array<string, mixed>> $cartItems */
                $cartItems = $this->input('cart.items', []);
                $variations = ItemVariation::query()
                    ->whereKey(
                        collect($cartItems)->pluck('variation.id')->all(),
                    )
                    ->get()
                    ->keyBy('id');

                foreach ($cartItems as $index => $cartItem) {
                    $variation = $variations->get(
                        data_get($cartItem, 'variation.id'),
                    );

                    if (
                        ! $variation instanceof ItemVariation
                        || $variation->item_id !== data_get($cartItem, 'item.id')
                        || $variation->barcode !== data_get($cartItem, 'variation.barcode')
                    ) {
                        $validator->errors()->add(
                            "cart.items.{$index}.variation.id",
                            'The selected item variation is unavailable.',
                        );

                        continue;
                    }

                    if ($variation->stock < data_get($cartItem, 'quantity')) {
                        $validator->errors()->add(
                            "cart.items.{$index}.quantity",
                            'The requested quantity is no longer available.',
                        );
                    }
                }
            },
        ];
    }

    private function isDelivery(): bool
    {
        return $this->input('cart.deliveryMode') === DeliveryMode::DELIVERY->value;
    }
}
