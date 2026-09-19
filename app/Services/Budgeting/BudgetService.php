<?php

namespace App\Services\Budgeting;

use App\Repositories\Contracts\BudgetAllocationRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The one place the envelope formula lives:
 *
 *     remaining = allocated_amount − SUM(expenses for that category + month)
 *
 * Both the dashboard endpoint and the PDF/print report call this instead of
 * each re-deriving the number their own way — see the build plan's note on
 * keeping this in a single service.
 */
class BudgetService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly BudgetAllocationRepositoryInterface $allocations,
        private readonly ExpenseRepositoryInterface $expenses,
    ) {
    }

    /**
     * One envelope per category that has either an allocation or spending
     * in the given month, so a category doesn't disappear from the
     * dashboard just because nothing was allocated to it yet.
     *
     * Kept as-is (loads every category, unpaginated) — this is what the
     * dashboard's Category envelope cards use, where showing the whole
     * set at once is fine.
     *
     * @return Collection<int, array{category_id:int, category_name:string, icon:?string, allocated:float, spent:float, remaining:float}>
     */
    public function envelopesForOrganization(int $organizationId, Carbon $month): Collection
    {
        $categories = $this->categories->forOrganization($organizationId);
        $allocations = $this->allocations->forOrganizationInMonth($organizationId, $month)
            ->keyBy('category_id');
        $spentByCategory = $this->expenses->totalsByCategoryInMonth($organizationId, $month)
            ->keyBy('category_id');

        return $categories->map(function ($category) use ($allocations, $spentByCategory) {
            $allocated = (float) ($allocations->get($category->id)?->allocated_amount ?? 0);
            $spent = (float) ($spentByCategory->get($category->id)?->total ?? 0);

            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'icon' => $category->icon,
                'allocated' => $allocated,
                'spent' => $spent,
                'remaining' => round($allocated - $spent, 2),
            ];
        })->values();
    }

    /**
     * Paginated + searchable version for the Budgets listing page — same
     * envelope formula, but only builds envelopes for the categories on
     * the requested page. Matches the Expenses/Incomes pagination shape
     * (has_more / next_page) so the frontend can reuse the same
     * useInfiniteQuery pattern.
     *
     * @return array{data: Collection, has_more: bool, next_page: ?int}
     */
    public function envelopesForOrganizationPaginated(int $organizationId, Carbon $month, ?string $search, int $perPage = 10): array
    {
        $categoriesPage = $this->categories->searchPaginated($organizationId, $search, $perPage);

        $allocations = $this->allocations->forOrganizationInMonth($organizationId, $month)
            ->keyBy('category_id');
        $spentByCategory = $this->expenses->totalsByCategoryInMonth($organizationId, $month)
            ->keyBy('category_id');

        $envelopes = collect($categoriesPage->items())->map(function ($category) use ($allocations, $spentByCategory) {
            $allocated = (float) ($allocations->get($category->id)?->allocated_amount ?? 0);
            $spent = (float) ($spentByCategory->get($category->id)?->total ?? 0);

            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'icon' => $category->icon,
                'allocated' => $allocated,
                'spent' => $spent,
                'remaining' => round($allocated - $spent, 2),
            ];
        })->values();

        return [
            'data' => $envelopes,
            'has_more' => $categoriesPage->hasMorePages(),
            'next_page' => $categoriesPage->hasMorePages() ? $categoriesPage->currentPage() + 1 : null,
        ];
    }

    /**
     * How much of a single category's monthly envelope is left — used to
     * warn (not block) when logging an expense would push it negative.
     */
    public function remainingFor(int $categoryId, Carbon $month): float
    {
        $allocation = $this->allocations->findForCategoryAndMonth($categoryId, $month);
        $allocated = (float) ($allocation?->allocated_amount ?? 0);
        $spent = $this->expenses->totalForCategoryInMonth($categoryId, $month);

        return round($allocated - $spent, 2);
    }
}
