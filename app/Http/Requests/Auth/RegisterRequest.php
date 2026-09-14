<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Anyone can attempt to register — there is no signed-in user yet.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            // Unique across the whole system.
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],

            // Chosen by the user, not generated — also unique across the
            // whole system, same rule shape as email.
            'organization_name' => ['required', 'string', 'max:255', 'unique:organizations,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'An account with this email already exists.',
            'organization_name.unique' => 'That organization name is already taken — try another.',
        ];
    }
}
