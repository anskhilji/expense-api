<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expenses\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\Expenses\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenses,
    ) {
    }

    public function index(Request $request)
    {
        return ExpenseResource::collection($this->expenses->forOrganization(
            $request->user()->current_org_id,
            $request->input('category_id') ? (int) $request->input('category_id') : null,
            $request->input('month'),
        ));
    }

    /**
     * Logs the expense and hands back how much of that category's envelope
     * is left this month — this single number is what lets the frontend
     * update the envelope card immediately, no separate refetch required.
     */
    public function store(StoreExpenseRequest $request)
    {
        $result = $this->expenses->log(
            $request->user()->current_org_id,
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'expense' => new ExpenseResource($result['expense']->load(['category', 'loggedBy'])),
            'remaining_in_category' => $result['remaining_in_category'],
        ], 201);
    }

    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        $this->authorizeOrgOwnership($request, $expense);

        return new ExpenseResource(
            $this->expenses->update($expense, $request->validated())->load(['category', 'loggedBy'])
        );
    }

    public function destroy(Request $request, Expense $expense)
    {
        $this->authorizeOrgOwnership($request, $expense);

        $this->expenses->delete($expense);

        return response()->noContent();
    }
}
