<?php

namespace App\Http\Requests\Item;

use App\Models\Item;
use App\Rules\Item\CanListItemRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateItemListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $item instanceof Item
            && $this->user()?->can('list', $item) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $item = $this->route('item');

        return [
            'is_listed' => [
                'bail',
                'required',
                'boolean:strict',
                ...($item instanceof Item ? [new CanListItemRule($item)] : []),
            ],
        ];
    }
}
