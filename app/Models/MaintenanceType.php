<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceType extends Model
{
    protected $fillable = ['name', 'description', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function assetMaintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function gaMaintenanceLogs(): HasMany
    {
        return $this->hasMany(GaMaintenanceLog::class);
    }
}
