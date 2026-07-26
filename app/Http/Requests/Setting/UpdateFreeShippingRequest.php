<?php

namespace App\Http\Requests\Setting;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFreeShippingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $freeShipping = Setting::query()->find(
            SettingService::FREE_SHIPPING_IS_ACTIVATED,
        );

        return $freeShipping instanceof Setting
            && $this->user()?->can('update', $freeShipping) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_activated' => ['required', 'boolean'],
            'threshold' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'description' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function isActivated(): bool
    {
        return $this->boolean('is_activated');
    }

    public function threshold(): float
    {
        return (float) $this->validated('threshold');
    }

    public function description(): string
    {
        return (string) ($this->validated('description') ?? '');
    }
}
