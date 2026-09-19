<?php

namespace App\Repositories\Contracts;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;

interface OrganizationRepositoryInterface extends RepositoryInterface
{
    public function nameExists(string $name): bool;

    public function attachMember(Organization $organization, User $user, Role $role): void;

    public function membersOf(Organization $organization);

    public function membersOfPaginated(Organization $organization, ?string $search, int $perPage = 10): Paginator;
}
