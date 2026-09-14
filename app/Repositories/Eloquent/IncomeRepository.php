<?php

namespace App\Repositories\Eloquent;

use App\Models\Income;
use App\Repositories\Contracts\IncomeRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IncomeRepository extends BaseRepository implements IncomeRepositoryInterface
{
    public function __construct(Income $model)
    {
        parent::__construct($model);
    }

    public function forOrganizationInMonth(int $organizationId, Carbon $month): Collection
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->whereBetween('received_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderByDesc('received_at')
            ->with('loggedBy:id,name')
            ->get();
    }

    public function totalForOrganizationInMonth(int $organizationId, Carbon $month): float
    {
        return (float) $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->whereBetween('received_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('amount');
    }

    public function totalsForOrganizationByMonth(int $organizationId, Carbon $from, Carbon $to): Collection
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->whereBetween('received_at', [$from->copy()->startOfMonth(), $to->copy()->endOfMonth()])
            ->selectRaw("DATE_FORMAT(received_at, '%Y-%m-01') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
