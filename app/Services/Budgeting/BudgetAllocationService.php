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

    public function allocate(int $organizationId, int $categoryId, string $month, float $amount): BudgetAllocation
    {
        $category = $this->categories->findOrFail($categoryId);

        if ($category->organization_id !== $organizationId) {
            // Never let one org allocate a budget against another org's
            // category, even if they somehow guessed a valid category id.
            throw ValidationException::withMessages([
                'category_id' => 'That category does not belong to your organization.',
            ]);
        }

        return $this->allocations->setAllocation($organizationId, $categoryId, Carbon::parse($month), $amount);
    }
}
