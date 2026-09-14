<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $membership = $this->currentMembership();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'organization' => new OrganizationResource($this->whenLoaded('currentOrganization')),
            'role' => $membership?->role?->slug,
            'permissions' => $membership?->role?->permissions?->pluck('slug') ?? [],
        ];
    }
}
