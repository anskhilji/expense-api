<?php

namespace App\Repositories\Eloquent;

use App\Models\BudgetAllocation;
use App\Repositories\Contracts\BudgetAllocationRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BudgetAllocationRepository extends BaseRepository implements BudgetAllocationRepositoryInterface
{
    public function __construct(BudgetAllocation $model)
    {
        parent::__construct($model);
    }

    public function forOrganizationInMonth(int $organizationId, Carbon $month): Collection
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->whereDate('month', $month->copy()->startOfMonth())
            ->with('category')
            ->get();
    }

    public function findForCategoryAndMonth(int $categoryId, Carbon $month): ?BudgetAllocation
    {
        return $this->model->newQuery()
            ->where('category_id', $categoryId)
            ->whereDate('month', $month->copy()->startOfMonth())
            ->first();
    }

    public function setAllocation(int $organizationId, int $categoryId, Carbon $month, float $amount): BudgetAllocation
    {
        return $this->model->newQuery()->updateOrCreate(
            [
                'category_id' => $categoryId,
                'month' => $month->copy()->startOfMonth()->toDateString(),
            ],
            [
                'organization_id' => $organizationId,
                'allocated_amount' => $amount,
            ],
        );
    }
}
