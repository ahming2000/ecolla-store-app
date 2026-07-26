<?php

namespace App\Http\Requests\User;

use App\Enums\AccessLevel;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                Rule::unique('users', 'username')->withoutTrashed(),
            ],
            'password' => 'required|confirmed',
            'access_level' => [
                'required',
                Rule::enum(AccessLevel::class)->except(AccessLevel::ADMIN),
            ],
        ];
    }
}
