<?php

namespace App\Repositories\Contracts;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;

interface OrganizationRepositoryInterface extends RepositoryInterface
{
    public function nameExists(string $name): bool;

    public function attachMember(Organization $organization, User $user, Role $role): void;

    public function membersOf(Organization $organization);
}
