<?php

namespace App\Services\Organizations;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MemberService
{
    public function __construct(
        private readonly OrganizationRepositoryInterface $organizations,
        private readonly RoleRepositoryInterface $roles,
    ) {
    }

    public function listMembers(Organization $organization): Collection
    {
        return $this->organizations->membersOf($organization);
    }

    public function changeRole(Organization $organization, int $userId, string $roleSlug): void
    {
        if ($userId === $organization->owner_id) {
            throw ValidationException::withMessages(['role' => "The organization owner's role can't be changed."]);
        }

        $role = $this->roles->findBySlug($roleSlug);
        if (! $role) {
            throw ValidationException::withMessages(['role' => 'Unknown role.']);
        }

        $membership = OrganizationUser::query()
            ->where('organization_id', $organization->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $membership->update(['role_id' => $role->id]);
    }

    /**
     * Removes a member outright ONLY if they have no expenses or incomes
     * logged in this organization — deleting the membership of someone
     * with a data trail would either orphan those records or crash on a
     * foreign key constraint. Someone with history must be blocked
     * instead (see blockMember), which keeps their records intact while
     * cutting off further access.
     */
    public function removeMember(Organization $organization, int $userId): void
    {
        if ($userId === $organization->owner_id) {
            throw ValidationException::withMessages(['user' => "The organization owner can't be removed."]);
        }

        if ($this->hasContributed($organization, $userId)) {
            throw ValidationException::withMessages([
                'user' => "This member has added expenses or income and can't be fully removed — block them instead to revoke access while keeping their records.",
            ]);
        }

        OrganizationUser::query()
            ->where('organization_id', $organization->id)
            ->where('user_id', $userId)
            ->delete();
    }

    public function blockMember(Organization $organization, int $userId): void
    {
        if ($userId === $organization->owner_id) {
            throw ValidationException::withMessages(['user' => "The organization owner can't be blocked."]);
        }

        $membership = OrganizationUser::query()
            ->where('organization_id', $organization->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $membership->update(['blocked_at' => now()]);
    }

    public function unblockMember(Organization $organization, int $userId): void
    {
        $membership = OrganizationUser::query()
            ->where('organization_id', $organization->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $membership->update(['blocked_at' => null]);
    }

    private function hasContributed(Organization $organization, int $userId): bool
    {
        return Expense::query()
                ->where('organization_id', $organization->id)
                ->where('user_id', $userId)
                ->exists()
            || Income::query()
                ->where('organization_id', $organization->id)
                ->where('user_id', $userId)
                ->exists();
    }
}