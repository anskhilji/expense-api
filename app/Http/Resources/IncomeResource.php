<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'source' => $this->source,
            'received_at' => $this->received_at->toDateString(),
            'logged_by' => $this->whenLoaded('loggedBy', fn () => $this->loggedBy->name),
        ];
    }
}
