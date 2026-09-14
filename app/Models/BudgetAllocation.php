<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetAllocation extends Model
{
    protected $fillable = ['organization_id', 'category_id', 'month', 'allocated_amount'];

    protected $casts = [
        'month' => 'date',
        'allocated_amount' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
