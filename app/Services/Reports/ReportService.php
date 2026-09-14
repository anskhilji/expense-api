<?php

namespace App\Services\Reports;

use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\IncomeRepositoryInterface;
use App\Services\Budgeting\BudgetService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(
        private readonly IncomeRepositoryInterface $incomes,
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly BudgetService $budgets,
    ) {
    }

    /**
     * Everything the Dashboard and the PDF/print report both need for one
     * month — the same envelopes BudgetService computes for the live UI, so
     * the printed report can never disagree with the on-screen totals.
     */
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
     * The month-over-month ledger: income vs. expense totals for each of
     * the last N months, most recent first.
     */
    public function ledger(int $organizationId, int $months = 3): Collection
    {
        $to = now()->startOfMonth();
        $from = $to->copy()->subMonths($months - 1);

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

        return $rows;
    }
}
