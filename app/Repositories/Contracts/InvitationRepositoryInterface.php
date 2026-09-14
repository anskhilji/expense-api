<?php

namespace App\Repositories\Contracts;

use App\Models\Invitation;

interface InvitationRepositoryInterface extends RepositoryInterface
{
    public function findByToken(string $token): ?Invitation;

    public function pendingForOrganizationAndEmail(int $organizationId, string $email): ?Invitation;

    public function findByOrganizationAndEmail(int $organizationId, string $email): ?Invitation;
}