<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budgets\AllocateBudgetRequest;
use App\Services\Budgeting\BudgetAllocationService;
use App\Services\Budgeting\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BudgetController extends Controller
{
    public function __construct(
        private readonly BudgetService $budgets,
        private readonly BudgetAllocationService $allocations,
    ) {
    }

    /**
     * The live envelope view — allocated / spent / remaining per category
     * for one month. This is what the Dashboard's envelope cards render.
     */
    public function index(Request $request)
    {
        $month = Carbon::parse($request->input('month', now()->toDateString()));
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        $result = $this->budgets->envelopesForOrganizationPaginated(
            $request->user()->current_org_id,
            $month,
            $search,
            $perPage,
        );

        return [
            'month' => $month->format('Y-m'),
            'data' => $result['data'],
            'has_more' => $result['has_more'],
            'next_page' => $result['next_page'],
        ];
    }

    public function store(AllocateBudgetRequest $request)
    {
        $allocation = $this->allocations->allocate(
            $request->user()->current_org_id,
            (int) $request->input('category_id'),
            $request->input('month'),
            (float) $request->input('allocated_amount'),
        );

        return response()->json([
            'category_id' => $allocation->category_id,
            'month' => $allocation->month->format('Y-m-d'),
            'allocated_amount' => (float) $allocation->allocated_amount,
        ], 201);
    }
}
