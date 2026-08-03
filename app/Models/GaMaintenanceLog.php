<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaMaintenanceLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(GaAsset::class, 'asset_id');
    }
}
