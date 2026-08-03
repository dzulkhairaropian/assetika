<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssetMaintenance extends Model
{
    protected $fillable = [
        'asset_id',
        'service_provider',
        'start_date',
        'completion_date',
        'cost',
        'description',
        'evidence_file_path',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'completion_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function getEvidenceUrlAttribute()
    {
        return $this->evidence_file_path ? Storage::disk('public')->url($this->evidence_file_path) : null;
    }
}
