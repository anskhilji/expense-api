<?php

namespace App\Services\Categories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {
    }

    public function listFor(int $organizationId): Collection
    {
        return $this->categories->forOrganization($organizationId);
    }

    public function create(int $organizationId, string $name, ?string $icon): Category
    {
        if ($this->categories->nameExistsInOrganization($organizationId, $name)) {
            throw ValidationException::withMessages([
                'name' => 'A category with this name already exists in your organization.',
            ]);
        }

        return $this->categories->create([
            'organization_id' => $organizationId,
            'name' => $name,
            'icon' => $icon,
        ]);
    }

    public function update(Category $category, string $name, ?string $icon): Category
    {
        if ($this->categories->nameExistsInOrganization($category->organization_id, $name, $category->id)) {
            throw ValidationException::withMessages([
                'name' => 'A category with this name already exists in your organization.',
            ]);
        }

        return $this->categories->update($category, ['name' => $name, 'icon' => $icon]);
    }

    public function delete(Category $category): bool
    {
        if ($category->expenses()->exists()) {
            throw ValidationException::withMessages([
                'category' => "This category has expenses logged against it and can't be deleted. Remove those first.",
            ]);
        }

        // An allocation with no expenses against it is just unused budget —
        // safe to clear out along with the category, nothing to lose.
        $category->budgetAllocations()->delete();

        return $this->categories->delete($category);
    }
}
