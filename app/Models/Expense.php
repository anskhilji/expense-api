<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = ['organization_id', 'user_id', 'category_id', 'amount', 'note', 'spent_at'];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent_at' => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
