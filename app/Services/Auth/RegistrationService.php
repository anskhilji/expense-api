<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RegistrationService
{
    /** A sensible starting set so a brand-new organization isn't an empty screen — freely editable afterwards. */
    private const DEFAULT_CATEGORIES = [
        ['name' => 'Groceries', 'icon' => '🛒'],
        ['name' => 'Electricity', 'icon' => '💡'],
        ['name' => 'Internet', 'icon' => '📶'],
        ['name' => 'Rent', 'icon' => '🏠'],
        ['name' => 'Transport', 'icon' => '🚗'],
        ['name' => 'Health', 'icon' => '💊'],
    ];

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly OrganizationRepositoryInterface $organizations,
        private readonly RoleRepositoryInterface $roles,
        private readonly CategoryRepositoryInterface $categories,
    ) {
    }

    /**
     * Create the user and their own organization together, as one unit —
     * the org name is chosen by the user (validated unique upstream in
     * RegisterRequest), never generated. Wrapped in a transaction so a
     * failure partway through never leaves an orphaned user with no org.
     *
     * @param array{name: string, email: string, password: string, organization_name: string} $data
     */
    public function register(array $data): User
    {
        $user = DB::transaction(function () use ($data) {
            $ownerRole = $this->roles->findBySlug('owner');

            if (! $ownerRole) {
                // Fails loudly rather than silently creating a user with no
                // usable role — means RolePermissionSeeder hasn't run yet.
                throw new RuntimeException('The "owner" role is not seeded. Run RolePermissionSeeder first.');
            }

            /** @var User $user */
            $user = $this->users->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $organization = $this->organizations->create([
                'name' => $data['organization_name'],
                'owner_id' => $user->id,
            ]);

            $this->organizations->attachMember($organization, $user, $ownerRole);

            $user->update(['current_org_id' => $organization->id]);

            foreach (self::DEFAULT_CATEGORIES as $category) {
                $this->categories->create([
                    'organization_id' => $organization->id,
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                ]);
            }

            return $user->fresh(['currentOrganization']);
        });

        $user->sendEmailVerificationNotification();

        return $user;
    }
}