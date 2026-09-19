<?php

namespace App\Http\Requests\Budgets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by 'permission:budgets.manage'
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'month' => ['required', 'date_format:Y-m-d'],
            'allocated_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
