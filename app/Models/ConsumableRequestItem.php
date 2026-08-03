<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumableRequestItem extends Model
{
    protected $fillable = [
        'consumable_request_id',
        'consumable_id',
        'quantity_requested',
        'quantity_fulfilled',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'integer',
        'quantity_fulfilled' => 'integer',
    ];

    // Relationships
    public function consumableRequest(): BelongsTo
    {
        return $this->belongsTo(ConsumableRequest::class);
    }

    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class);
    }

    // Helpers
    public function isFulfilled(): bool
    {
        return $this->quantity_fulfilled !== null && $this->quantity_fulfilled > 0;
    }

    public function isPartiallyFulfilled(): bool
    {
        return $this->quantity_fulfilled !== null 
            && $this->quantity_fulfilled > 0 
            && $this->quantity_fulfilled < $this->quantity_requested;
    }

    public function isFullyFulfilled(): bool
    {
        return $this->quantity_fulfilled !== null 
            && $this->quantity_fulfilled >= $this->quantity_requested;
    }
}
