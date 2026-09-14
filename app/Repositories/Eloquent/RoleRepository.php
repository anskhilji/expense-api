<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug): ?Role
    {
        return $this->model->newQuery()->where('slug', $slug)->first();
    }
}
