<?php

namespace App\Http\Requests\Image;

use App\Enums\ImageUploadOption;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UploadImageRequest extends FormRequest
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
            'image' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'gif', 'webp'])
                    ->max('1750kb'),
            ],
            'option' => [
                'required',
                Rule::enum(ImageUploadOption::class),
            ],
            'with_thumbnail' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
