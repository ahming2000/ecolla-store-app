<?php

namespace App\Http\Requests\Variation;

use App\Models\Item;
use App\Models\ItemVariation;
use App\Rules\Variation\ValidItemVariationRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

abstract class SaveVariationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $item instanceof Item
            && $this->user()?->can('update', $item) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $variation = $this->route('variation');

        return [
            'barcode' => [
                'bail',
                'required',
                'string',
                'max:255',
                new ValidItemVariationRule(
                    $variation instanceof ItemVariation ? $variation : null,
                ),
            ],
            'name' => ['bail', 'required', 'string', 'max:255'],
            'name_en' => ['bail', 'required', 'string', 'max:255'],
            'price' => ['bail', 'required', 'numeric', 'min:0.01'],
            'sale_price' => [
                'bail',
                'nullable',
                'numeric',
                'min:0',
                'lte:price',
            ],
            'weight' => ['bail', 'required', 'numeric', 'min:0'],
            'stock' => ['bail', 'required', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'barcode' => $this->trimmedInput('barcode'),
            'name' => $this->trimmedInput('name'),
            'name_en' => $this->trimmedInput('name_en'),
            'sale_price' => $this->input('sale_price') === ''
                ? null
                : $this->input('sale_price'),
        ]);
    }

    private function trimmedInput(string $key): mixed
    {
        $value = $this->input($key);

        return is_string($value) ? trim($value) : $value;
    }
}
