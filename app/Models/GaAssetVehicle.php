<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaAssetVehicle extends Model
{
    protected $fillable = [
        'asset_id',
        'plate_number',
        'chassis_number',
        'engine_number',
        'vehicle_type',
        'fuel_type',
        'tax_due_date',
        'plate_expiry_date',
        'last_service_date',
        'insurance_expiry_date',
        'fuel_consumption',
        'notes',
    ];

    protected $casts = [
        'tax_due_date' => 'date',
        'plate_expiry_date' => 'date',
        'last_service_date' => 'date',
        'insurance_expiry_date' => 'date',
        'fuel_consumption' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(GaAsset::class);
    }
}
