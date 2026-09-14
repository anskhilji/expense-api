<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function forOrganization(int $organizationId): Collection;

    public function nameExistsInOrganization(int $organizationId, string $name, ?int $exceptId = null): bool;
}
