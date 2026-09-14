<?php

namespace App\Services\Expenses;

use App\Models\Expense;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Services\Budgeting\BudgetService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CategoryRepositoryInterface $categories,
        private readonly BudgetService $budgets,
    ) {
    }

    /**
     * @param array{category_id: int, amount: float, note: ?string, spent_at: string} $data
     * @return array{expense: Expense, remaining_in_category: float}
     */
    public function log(int $organizationId, int $userId, array $data): array
    {
        $category = $this->categories->findOrFail($data['category_id']);

        if ($category->organization_id !== $organizationId) {
            throw ValidationException::withMessages([
                'category_id' => 'That category does not belong to your organization.',
            ]);
        }

        $expense = $this->expenses->create([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'category_id' => $category->id,
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
            'spent_at' => $data['spent_at'],
        ]);

        // Going negative is allowed (real life overspends the grocery
        // budget) — it's surfaced to the UI, never silently blocked.
        $remaining = $this->budgets->remainingFor($category->id, Carbon::parse($data['spent_at']));

        return ['expense' => $expense, 'remaining_in_category' => $remaining];
    }

    public function forOrganization(int $organizationId, ?int $categoryId, ?string $month): Collection
    {
        return $this->expenses->forOrganization(
            $organizationId,
            $categoryId,
            $month ? Carbon::parse($month) : null,
        );
    }

    public function update(Expense $expense, array $data): Expense
    {
        return $this->expenses->update($expense, $data);
    }

    public function delete(Expense $expense): bool
    {
        return $this->expenses->delete($expense);
    }
}
