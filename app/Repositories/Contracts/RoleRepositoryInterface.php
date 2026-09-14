<?php

namespace App\Repositories\Contracts;

use App\Models\Role;

interface RoleRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug): ?Role;
}
