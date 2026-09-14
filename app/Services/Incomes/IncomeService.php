<?php

namespace App\Services\Incomes;

use App\Models\Income;
use App\Repositories\Contracts\IncomeRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IncomeService
{
    public function __construct(
        private readonly IncomeRepositoryInterface $incomes,
    ) {
    }

    /**
     * @param array{amount: float, source: ?string, received_at: string} $data
     */
    public function log(int $organizationId, int $userId, array $data): Income
    {
        // Every deposit is its own row — logging a second income this month
        // does not overwrite or merge with the first, it adds to the total.
        return $this->incomes->create([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'amount' => $data['amount'],
            'source' => $data['source'] ?? null,
            'received_at' => $data['received_at'],
        ]);
    }

    public function forMonth(int $organizationId, Carbon $month): Collection
    {
        return $this->incomes->forOrganizationInMonth($organizationId, $month);
    }

    public function totalForMonth(int $organizationId, Carbon $month): float
    {
        return $this->incomes->totalForOrganizationInMonth($organizationId, $month);
    }

    public function update(Income $income, array $data): Income
    {
        return $this->incomes->update($income, $data);
    }

    public function delete(Income $income): bool
    {
        return $this->incomes->delete($income);
    }
}
