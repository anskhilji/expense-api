<?php

namespace App\Repositories\Contracts;

use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface ExpenseRepositoryInterface extends RepositoryInterface
{
    public function forOrganization(int $organizationId, ?int $categoryId, ?Carbon $month): Collection;

    public function totalForCategoryInMonth(int $categoryId, Carbon $month): float;

    public function totalsForOrganizationByMonth(int $organizationId, Carbon $from, Carbon $to): Collection;

    public function totalsByCategoryInMonth(int $organizationId, Carbon $month): Collection;
}
