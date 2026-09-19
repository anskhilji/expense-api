<?php

namespace App\Repositories\Eloquent;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
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

    public function membersOf(Organization $organization): Collection
    {
        return OrganizationUser::query()
            ->where('organization_id', $organization->id)
            ->with(['user:id,name,email', 'role.permissions'])
            ->get();
    }

    /**
     * Paginated + searchable version for the Members listing page —
     * matches by the member's name, email, or role slug.
     */
    public function membersOfPaginated(Organization $organization, ?string $search, int $perPage = 10): Paginator
    {
        return OrganizationUser::query()
            ->where('organization_id', $organization->id)
            ->with(['user:id,name,email', 'role.permissions'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('role', function ($rq) use ($search) {
                        $rq->where('slug', 'like', "%{$search}%");
                    });
                });
            })
            ->simplePaginate($perPage);
    }
}
