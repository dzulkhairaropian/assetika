<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ConsumableTransaction extends Model
{
    protected $table = 'consumable_transactions';

    protected $fillable = [
        'consumable_id',
        'user_id',
        'checked_out_to_user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'total_cost',
        'evidence_file_path',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedOutToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_to_user_id');
    }

    public function isRestock(): bool
    {
        return $this->type === 'restock';
    }

    public function isCheckout(): bool
    {
        return $this->type === 'checkout';
    }

    public function getEvidenceUrl(): ?string
    {
        if (!$this->evidence_file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->evidence_file_path);
    }
}
