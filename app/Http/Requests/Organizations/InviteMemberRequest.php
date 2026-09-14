<?php

namespace App\Http\Requests\Organizations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by 'permission:organization.manage'
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            // "owner" is not invitable — there is exactly one, set at signup.
            'role' => ['required', Rule::in(['admin', 'editor', 'contributor', 'viewer'])],
        ];
    }
}
