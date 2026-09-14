<?php

namespace App\Repositories\Eloquent;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    public function __construct(Organization $model)
    {
        parent::__construct($model);
    }

    public function nameExists(string $name): bool
    {
        return $this->model->newQuery()->where('name', $name)->exists();
    }

    public function attachMember(Organization $organization, User $user, Role $role): void
    {
        OrganizationUser::query()->updateOrCreate(
            ['organization_id' => $organization->id, 'user_id' => $user->id],
            ['role_id' => $role->id],
        );
    }

    /**
     * Returns the organization_user membership rows themselves (not bare
     * User models) — each one already carries the role for THIS
     * organization specifically, which matters because a user can belong
     * to more than one org with a different role in each.
     */
    public function membersOf(Organization $organization): Collection
    {
        return OrganizationUser::query()
            ->where('organization_id', $organization->id)
            ->with(['user:id,name,email', 'role.permissions'])
            ->get();
    }
}
