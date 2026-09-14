<?php

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExpenseRepository extends BaseRepository implements ExpenseRepositoryInterface
{
    public function __construct(Expense $model)
    {
        parent::__construct($model);
    }

    public function forOrganization(int $organizationId, ?int $categoryId, ?Carbon $month): Collection
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($month, fn ($q) => $q->whereBetween('spent_at', [
                $month->copy()->startOfMonth(), $month->copy()->endOfMonth(),
            ]))
            ->orderByDesc('spent_at')
            ->with(['category:id,name,icon', 'loggedBy:id,name'])
            ->get();
    }

    public function totalForCategoryInMonth(int $categoryId, Carbon $month): float
    {
        return (float) $this->model->newQuery()
            ->where('category_id', $categoryId)
            ->whereBetween('spent_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('amount');
    }

    public function totalsForOrganizationByMonth(int $organizationId, Carbon $from, Carbon $to): Collection
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->whereBetween('spent_at', [$from->copy()->startOfMonth(), $to->copy()->endOfMonth()])
            ->selectRaw("DATE_FORMAT(spent_at, '%Y-%m-01') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function totalsByCategoryInMonth(int $organizationId, Carbon $month): Collection
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->whereBetween('spent_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get();
    }
}
