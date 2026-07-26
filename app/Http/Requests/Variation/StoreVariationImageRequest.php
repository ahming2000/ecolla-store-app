<?php

namespace App\Http\Requests\Variation;

use App\Models\Image;
use App\Models\Item;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariationImageRequest extends FormRequest
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
        return [
            'image_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists(Image::class, 'id'),
            ],
        ];
    }
}
