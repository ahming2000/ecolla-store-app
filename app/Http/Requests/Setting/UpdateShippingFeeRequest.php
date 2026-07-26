<?php

namespace App\Http\Requests\Setting;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $shippingFee = Setting::query()->find(SettingService::SHIPPING_FEE);

        return $shippingFee instanceof Setting
            && $this->user()?->can('update', $shippingFee) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipping_fee' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
        ];
    }

    public function shippingFee(): float
    {
        return (float) $this->validated('shipping_fee');
    }
}
