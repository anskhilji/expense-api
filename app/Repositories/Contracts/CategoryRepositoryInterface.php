<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function forOrganization(int $organizationId): Collection;

    public function nameExistsInOrganization(int $organizationId, string $name, ?int $exceptId = null): bool;

    /**
     * Paginated + searchable version used by the Budgets listing page,
     * so envelopes only get built for the categories on the current page
     * instead of loading every category at once.
     */
    public function searchPaginated(int $organizationId, ?string $search, int $perPage = 10): Paginator;
}
