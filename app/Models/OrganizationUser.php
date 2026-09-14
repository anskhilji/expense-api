<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The organization ↔ user membership, carrying the role for that specific
 * membership. Explicit model (rather than a bare pivot) because we need to
 * query and load it on its own — e.g. "does this user have permission X in
 * this org" — not just attach/detach it from either side.
 */
class OrganizationUser extends Model
{
    protected $table = 'organization_user';

    protected $fillable = ['organization_id', 'user_id', 'role_id', 'blocked_at'];

    protected function casts(): array
    {
        return [
            'blocked_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }
}