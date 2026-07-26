<?php

namespace App\Http\Requests\Item;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexShopItemsRequest extends FormRequest
{
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
            'barcodes' => ['sometimes', 'array'],
            'barcodes.*' => [
                'bail',
                'string',
                'max:255',
                'distinct:strict',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function barcodes(): array
    {
        return array_values($this->validated('barcodes', []));
    }
}
