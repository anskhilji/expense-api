<?php

namespace App\Http\Requests\Organizations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by 'permission:organization.manage'
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['admin', 'editor', 'contributor', 'viewer'])],
        ];
    }
}
