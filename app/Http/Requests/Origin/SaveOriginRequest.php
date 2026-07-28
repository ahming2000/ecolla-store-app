<?php

namespace App\Http\Requests\Origin;

use App\Models\Origin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveOriginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $origin = $this->route('origin');

        if ($origin instanceof Origin) {
            return $this->user()?->can('update', $origin) === true;
        }

        return $this->user()?->can('create', Origin::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array{name: string, name_en: string}
     */
    public function originData(): array
    {
        /** @var array{name: string, name_en: string} $data */
        $data = $this->safe()->only(['name', 'name_en']);

        return $data;
    }
}
