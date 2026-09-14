<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    protected $fillable = ['organization_id', 'user_id', 'amount', 'source', 'received_at'];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_at' => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
