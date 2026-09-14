<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps an OrganizationUser (membership) row, not a bare User — so the role
 * shown is always this organization's role for that person, never
 * whichever org they happen to be currently acting in.
 */
class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'role' => $this->role->slug,
            'permissions' => $this->role->permissions->pluck('slug'),
            'blocked' => $this->blocked_at !== null,
            'blocked_at' => $this->blocked_at,
        ];
    }
}