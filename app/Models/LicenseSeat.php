<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LicenseSeat extends Model
{
    protected $fillable = [
        'license_id',
        'assigned_to_type',
        'assigned_to_id',
        'checked_out_at',
        'notes',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function assignedTo(): MorphTo
    {
        return $this->morphTo();
    }
}
