<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incomes\StoreIncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Income;
use App\Services\Incomes\IncomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IncomeController extends Controller
{
    public function __construct(
        private readonly IncomeService $incomes,
    ) {
    }

    public function index(Request $request)
    {
        $month = Carbon::parse($request->input('month', now()->toDateString()));

        return [
            'data' => IncomeResource::collection($this->incomes->forMonth($request->user()->current_org_id, $month)),
            'total' => $this->incomes->totalForMonth($request->user()->current_org_id, $month),
        ];
    }

    public function store(StoreIncomeRequest $request)
    {
        $income = $this->incomes->log(
            $request->user()->current_org_id,
            $request->user()->id,
            $request->validated(),
        );

        return (new IncomeResource($income))->response()->setStatusCode(201);
    }

    public function update(StoreIncomeRequest $request, Income $income)
    {
        $this->authorizeOrgOwnership($request, $income);

        return new IncomeResource($this->incomes->update($income, $request->validated()));
    }

    public function destroy(Request $request, Income $income)
    {
        $this->authorizeOrgOwnership($request, $income);

        $this->incomes->delete($income);

        return response()->noContent();
    }
}
