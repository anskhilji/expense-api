<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface IncomeRepositoryInterface extends RepositoryInterface
{
    public function forOrganizationInMonth(int $organizationId, Carbon $month): Collection;

    public function totalForOrganizationInMonth(int $organizationId, Carbon $month): float;

    public function totalsForOrganizationByMonth(int $organizationId, Carbon $from, Carbon $to): Collection;
}
