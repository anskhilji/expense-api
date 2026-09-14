<?php

namespace App\Http\Requests\Organizations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * If the invited person isn't already signed in, they can create their
     * account and accept the invite in one step — name/password are only
     * required in that case, checked in the controller once we know
     * whether $request->user() is already set.
     */
    public function rules(): array
    {
        $needsAccount = ! $this->user();

        return [
            'name' => [$needsAccount ? 'required' : 'sometimes', 'string', 'max:255'],
            'password' => [$needsAccount ? 'required' : 'sometimes', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }
}
