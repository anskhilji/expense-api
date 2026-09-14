<?php

namespace App\Repositories\Eloquent;

use App\Models\Invitation;
use App\Repositories\Contracts\InvitationRepositoryInterface;

class InvitationRepository extends BaseRepository implements InvitationRepositoryInterface
{
    public function __construct(Invitation $model)
    {
        parent::__construct($model);
    }

    public function findByToken(string $token): ?Invitation
    {
        return $this->model->newQuery()->where('token', $token)->first();
    }

    public function pendingForOrganizationAndEmail(int $organizationId, string $email): ?Invitation
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->first();
    }

    public function findByOrganizationAndEmail(int $organizationId, string $email): ?Invitation
    {
        return $this->model->newQuery()
            ->where('organization_id', $organizationId)
            ->where('email', $email)
            ->first();
    }
}