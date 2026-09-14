<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * The handful of operations every repository in this app needs. Concrete
 * repositories add their own domain-specific finder methods on top of this
 * (see UserRepositoryInterface::findByEmail, for example) — this base just
 * keeps the generic CRUD shape in one place instead of copy-pasted per model.
 */
interface RepositoryInterface
{
    public function find(int $id): ?Model;

    public function findOrFail(int $id): Model;

    public function create(array $attributes): Model;

    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;
}
