<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['organization_id', 'name', 'icon'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function budgetAllocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
