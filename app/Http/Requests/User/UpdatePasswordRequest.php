<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && $user->can('updatePassword', $user);
    }

    /**
     * @return array<string, Rule|ValidationRule|array<int, Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'old_password' => [
                'bail',
                'required',
                'string',
                'current_password',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    public function password(): string
    {
        return $this->string('password')->toString();
    }
}
