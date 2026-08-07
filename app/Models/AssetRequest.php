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
}
