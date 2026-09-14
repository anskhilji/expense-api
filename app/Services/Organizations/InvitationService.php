<?php

namespace App\Services\Organizations;

use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\Contracts\InvitationRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function __construct(
        private readonly InvitationRepositoryInterface $invitations,
        private readonly OrganizationRepositoryInterface $organizations,
        private readonly RoleRepositoryInterface $roles,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Admin invites someone by email with a chosen role. If that email is
     * already a member, or already has a pending invite, this fails loudly
     * instead of silently creating a duplicate.
     */
    public function invite(Organization $organization, string $email, string $roleSlug): Invitation
    {
        $role = $this->roles->findBySlug($roleSlug);

        if (! $role) {
            throw ValidationException::withMessages(['role' => 'Unknown role.']);
        }

        $existingUser = $this->users->findByEmail($email);
        if ($existingUser && $organization->members()->where('users.id', $existingUser->id)->exists()) {
            throw ValidationException::withMessages(['email' => 'This person is already a member of your organization.']);
        }

        if ($this->invitations->pendingForOrganizationAndEmail($organization->id, $email)) {
            throw ValidationException::withMessages(['email' => 'There is already a pending invite for this email.']);
        }

        $existingInvitation = $this->invitations->findByOrganizationAndEmail($organization->id, $email);

        if ($existingInvitation) {
            // A row already exists for this org+email — most likely someone
            // who was removed and is now being re-invited, or a resend.
            // Reuse and refresh it rather than inserting a second row,
            // which the database's unique (organization_id, email) index
            // would reject anyway.
            $this->invitations->update($existingInvitation, [
                'invited_by' => $organization->owner_id,
                'role_id' => $role->id,
                'token' => Str::random(48),
                'accepted_at' => null,
            ]);

            $invitation = $existingInvitation->fresh();
        } else {
            $invitation = $this->invitations->create([
                'organization_id' => $organization->id,
                'invited_by' => $organization->owner_id,
                'email' => $email,
                'role_id' => $role->id,
                'token' => Str::random(48),
            ]);
        }

        // On-demand route since the invited person may not have an account
        // yet — this sends straight to the raw email address rather than
        // needing a User model to notify().
        Notification::route('mail', $email)->notify(new InvitationNotification($invitation, $organization));

        return $invitation;
    }

    public function findValidByToken(string $token): Invitation
    {
        $invitation = $this->invitations->findByToken($token);

        if (! $invitation || $invitation->isAccepted()) {
            throw ValidationException::withMessages(['token' => 'This invitation is invalid or already used.']);
        }

        return $invitation;
    }

    /**
     * Accepts an invitation either for an already-authenticated user, or by
     * creating the account on the spot (when $newAccountData is given) —
     * either way the person is attached to the INVITING organization with
     * the invited role, never given a fresh organization of their own the
     * way normal registration does.
     *
     * @param array{name: string, password: string}|null $newAccountData
     */
    public function accept(string $token, ?User $authUser, ?array $newAccountData): User
    {
        $invitation = $this->findValidByToken($token);

        return DB::transaction(function () use ($invitation, $authUser, $newAccountData) {
            $user = $authUser ?? $this->users->findByEmail($invitation->email);

            if (! $user) {
                if (! $newAccountData) {
                    throw ValidationException::withMessages([
                        'name' => 'An account is required to accept this invitation.',
                    ]);
                }

                $user = $this->users->create([
                    'name' => $newAccountData['name'],
                    'email' => $invitation->email,
                    'password' => Hash::make($newAccountData['password']),
                ]);
            }

            if (strcasecmp($invitation->email, $user->email) !== 0) {
                throw ValidationException::withMessages([
                    'token' => 'This invitation was sent to a different email address.',
                ]);
            }

            $this->organizations->attachMember($invitation->organization, $user, $invitation->role);
            $this->invitations->update($invitation, ['accepted_at' => now()]);

            // Switch the invited user into the org they just joined, so
            // their very next request already acts inside it.
            $user->update(['current_org_id' => $invitation->organization_id]);

            return $user->fresh(['currentOrganization']);
        });
    }
}
