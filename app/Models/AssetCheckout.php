<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssetCheckout extends Model
{
    protected $table = 'asset_checkouts';

    protected $fillable = [
        'asset_id',
        'checked_out_by_user_id',
        'checked_out_to_user_id',
        'supervisor_user_id',
        'department_id',
        'location_id',
        'checkout_evidence_file_path',
        'checkout_evidence_file_name',
        'checked_out_at',
        'checkout_notes',
        'checked_in_at',
        'checked_in_by_user_id',
        'condition_report',
        'checkin_evidence_file_path',
        'checkin_evidence_file_name',
        'checkin_notes',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    // Relationships
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function checkedOutByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by_user_id');
    }

    public function checkedOutToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_to_user_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function checkedInByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by_user_id');
    }

    // Scopes
    public function scopeCurrentlyCheckedOut($query)
    {
        return $query->whereNotNull('checked_out_at')
            ->whereNull('checked_in_at');
    }

    public function scopeCheckedOutTo($query, $userId)
    {
        return $query->where('checked_out_to_user_id', $userId)
            ->whereNotNull('checked_out_at')
            ->whereNull('checked_in_at');
    }

    // Helper methods
    public function isCurrentlyCheckedOut(): bool
    {
        return $this->checked_out_at !== null && $this->checked_in_at === null;
    }

    public function isCompleted(): bool
    {
        return $this->checked_out_at !== null && $this->checked_in_at !== null;
    }

    public function getCheckoutDuration()
    {
        if (! $this->checked_out_at) {
            return null;
        }

        $endTime = $this->checked_in_at ?: now();

        return $this->checked_out_at->diff($endTime);
    }

    public function getCheckoutEvidenceUrl()
    {
        if (! $this->checkout_evidence_file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->checkout_evidence_file_path);
    }

    public function getCheckinEvidenceUrl()
    {
        if (! $this->checkin_evidence_file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->checkin_evidence_file_path);
    }
}
