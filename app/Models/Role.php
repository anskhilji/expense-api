<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['slug', 'name'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission(string $slug): bool
    {
        // Loaded once per request and cached on the model instance by
        // Eloquent's relation cache, so repeated checks in one request
        // (e.g. building a "can I see this button" payload) don't re-query.
        return $this->permissions->contains('slug', $slug);
    }
}
