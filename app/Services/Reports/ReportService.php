<?php

namespace App\Services\Reports;

use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\IncomeRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Services\Budgeting\BudgetService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(
        private readonly IncomeRepositoryInterface $incomes,
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly BudgetService $budgets,
        private readonly OrganizationRepositoryInterface $organizations,
    ) {
    }

    public function summaryForMonth(int $organizationId, Carbon $month): array
    {
        $envelopes = $this->budgets->envelopesForOrganization($organizationId, $month);
        $totalIncome = $this->incomes->totalForOrganizationInMonth($organizationId, $month);
        $totalAllocated = round($envelopes->sum('allocated'), 2);
        $totalSpent = round($envelopes->sum('spent'), 2);

        return [
            'month' => $month->format('Y-m'),
            'total_income' => $totalIncome,
            'total_allocated' => $totalAllocated,
            'unallocated_income' => round($totalIncome - $totalAllocated, 2),
            'total_spent' => $totalSpent,
            'net' => round($totalIncome - $totalSpent, 2),
            'envelopes' => $envelopes,
        ];
    }

    /**
     * Paginated month-over-month ledger — page 1 is the most recent
     * $perPage months, page 2 the $perPage months before that, and so on,
     * going no further back than the organization's own creation date
     * (nothing to show before the org existed).
     *
     * @return array{data: Collection, has_more: bool, next_page: ?int}
     */
    public function ledger(int $organizationId, int $page = 1, int $perPage = 10): array
    {
        $organization = $this->organizations->findOrFail($organizationId);
        $orgStart = Carbon::parse($organization->created_at)->startOfMonth();

        $to = now()->startOfMonth()->subMonths(($page - 1) * $perPage);
        $from = $to->copy()->subMonths($perPage - 1);

        $hasMore = $from->gt($orgStart);
        if ($from->lt($orgStart)) {
            $from = $orgStart->copy();
        }

        $incomeByMonth = $this->incomes->totalsForOrganizationByMonth($organizationId, $from, $to)
            ->keyBy(fn ($row) => Carbon::parse($row->month)->format('Y-m'));
        $expenseByMonth = $this->expenses->totalsForOrganizationByMonth($organizationId, $from, $to)
            ->keyBy(fn ($row) => Carbon::parse($row->month)->format('Y-m'));

        $rows = collect();
        for ($cursor = $to->copy(); $cursor->gte($from); $cursor->subMonth()) {
            $key = $cursor->format('Y-m');
            $income = (float) ($incomeByMonth->get($key)?->total ?? 0);
            $expense = (float) ($expenseByMonth->get($key)?->total ?? 0);

            $rows->push([
                'month' => $key,
                'label' => $cursor->format('F Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => round($income - $expense, 2),
            ]);
        }

        return [
            'data' => $rows,
            'has_more' => $hasMore,
            'next_page' => $hasMore ? $page + 1 : null,
        ];
    }
}
