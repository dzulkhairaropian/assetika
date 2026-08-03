<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaAssetVehicle extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tax_due_date' => 'date',
        'last_service_date' => 'date',
        'insurance_expiry_date' => 'date',
        'fuel_consumption' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(GaAsset::class);
    }
}
