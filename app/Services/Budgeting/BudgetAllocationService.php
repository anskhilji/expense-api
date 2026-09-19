<?php

namespace App\Services\Budgeting;

use App\Models\BudgetAllocation;
use App\Repositories\Contracts\BudgetAllocationRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BudgetAllocationService
{
    public function __construct(
        private readonly BudgetAllocationRepositoryInterface $allocations,
        private readonly CategoryRepositoryInterface $categories,
    ) {
    }

    /**
     * The amount passed in is a TOP-UP, not a replacement — logging 2000
     * against a category already allocated 1000 results in a 3000 total.
     * This is what makes "add more budget to Gas mid-month" work without
     * the admin having to know or re-type the current total first.
     */
    public function allocate(int $organizationId, int $categoryId, string $month, float $amountToAdd): BudgetAllocation
    {
        $category = $this->categories->findOrFail($categoryId);

        if ($category->organization_id !== $organizationId) {
            // Never let one org allocate a budget against another org's
            // category, even if they somehow guessed a valid category id.
            throw ValidationException::withMessages([
                'category_id' => 'That category does not belong to your organization.',
            ]);
        }

        $monthDate = Carbon::parse($month);
        $existing = $this->allocations->findForCategoryAndMonth($categoryId, $monthDate);
        $newTotal = (float) ($existing?->allocated_amount ?? 0) + $amountToAdd;

        return $this->allocations->setAllocation($organizationId, $categoryId, $monthDate, $newTotal);
    }
}
