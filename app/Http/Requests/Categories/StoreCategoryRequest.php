<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by the 'permission:categories.manage' route middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:10'],
        ];
    }
}
