<?php

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by 'permission:expenses.create'
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
            'spent_at' => ['required', 'date', 'before_or_equal:today'],
        ];
    }
}
