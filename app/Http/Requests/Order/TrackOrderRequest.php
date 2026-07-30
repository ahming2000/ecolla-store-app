<?php

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class TrackOrderRequest extends FormRequest
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
            'reference_num' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', 'regex:/\d/'],
        ];
    }

    public function referenceNumber(): string
    {
        return (string) $this->validated('reference_num');
    }

    public function phone(): string
    {
        return (string) $this->validated('phone');
    }

    protected function prepareForValidation(): void
    {
        $referenceNumber = $this->input('reference_num');
        $phoneNumber = $this->input('phone');

        $this->merge([
            'reference_num' => is_string($referenceNumber)
                ? Str::upper(trim($referenceNumber))
                : $referenceNumber,
            'phone' => is_string($phoneNumber)
                ? trim($phoneNumber)
                : $phoneNumber,
        ]);
    }
}
