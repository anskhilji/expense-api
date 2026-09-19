<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function forOrganization(int $organizationId): Collection
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();
    }

    public function nameExistsInOrganization(int $organizationId, string $name, ?int $exceptId = null): bool
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->where('name', $name)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    public function searchPaginated(int $organizationId, ?string $search, int $perPage = 10): Paginator
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->simplePaginate($perPage);
    }
}
