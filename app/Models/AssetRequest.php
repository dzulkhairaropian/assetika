<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssetRequest extends Model
{
    protected $fillable = [
        'user_id',
        'requestable_type',
        'requestable_id',
        'qty',
        'status',
        'notes',
        'admin_notes',
        'processed_by_user_id',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Resolve display name regardless of requestable type
     * (Consumable stores name on its product relation).
     */
    public function getRequestableNameAttribute(): string
    {
        $req = $this->requestable;

        if (! $req) {
            return 'Item';
        }

        if ($req instanceof Consumable) {
            return $req->product?->name ?? 'Consumable';
        }

        return $req->name ?? $req->title ?? 'Item';
    }

    /**
     * Human-friendly request type label.
     */
    public function getRequestableTypeLabelAttribute(): string
    {
        return match (class_basename($this->requestable_type)) {
            'Category' => 'Hardware',
            'Consumable' => 'Consumable',
            'License' => 'License',
            default => class_basename($this->requestable_type),
        };
    }

    /**
     * Resolve the matching Status record from the statuses table (by name,
     * scoped to the IT Request master category), so badge color & icon are
     * driven by the Status model value.
     */
    public function getStatusRecordAttribute(): ?Status
    {
        $name = match ($this->status) {
            'approved' => 'Fulfilled',
            'rejected' => 'Declined',
            default => 'Pending',
        };

        return Status::where('name', $name)
            ->whereHas('masterCategory', fn ($q) => $q->where('name', 'IT Request'))
            ->first();
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return $this->statusRecord?->color ?? 'gray';
    }

    public function getStatusBadgeIconAttribute(): ?string
    {
        return $this->statusRecord?->icon;
    }
}
