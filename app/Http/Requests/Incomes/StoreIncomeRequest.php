<?php

namespace App\Http\Requests\Incomes;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by 'permission:incomes.create'
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'source' => ['nullable', 'string', 'max:100'],
            'received_at' => ['required', 'date', 'before_or_equal:today'],
        ];
    }
}
