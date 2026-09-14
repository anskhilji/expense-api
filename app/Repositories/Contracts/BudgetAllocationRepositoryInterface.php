<?php

namespace App\Repositories\Contracts;

use App\Models\BudgetAllocation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface BudgetAllocationRepositoryInterface extends RepositoryInterface
{
    public function forOrganizationInMonth(int $organizationId, Carbon $month): Collection;

    public function findForCategoryAndMonth(int $categoryId, Carbon $month): ?BudgetAllocation;

    public function setAllocation(int $organizationId, int $categoryId, Carbon $month, float $amount): BudgetAllocation;
}
