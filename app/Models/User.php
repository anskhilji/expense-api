<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, HasFactory, MustVerifyEmail, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_org_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_org_id');
    }

    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationUser::class);
    }

    /**
     * This user's role within their current organization, or null if they
     * somehow have no membership there (shouldn't happen in normal use —
     * registration always creates one).
     */
    public function currentMembership(): ?OrganizationUser
    {
        if (! $this->current_org_id) {
            return null;
        }

        return $this->memberships()
            ->where('organization_id', $this->current_org_id)
            ->with('role.permissions')
            ->first();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->currentMembership()?->role?->hasPermission($permissionSlug) ?? false;
    }

    /**
     * Overrides Notifiable's default, which would build a link pointing at
     * a backend "password.reset" route — we want it pointing at the React
     * frontend's own reset-password page instead.
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = rtrim(env('FRONTEND_URL'), '/').'/reset-password?token='.$token.'&email='.urlencode($this->email);

        $this->notify(new \App\Notifications\ResetPasswordNotification($url));
    }
}