<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaMaintenanceLog extends Model
{
    protected $fillable = [
        'asset_id',
        'maintenance_type_id',
        'title',
        'description',
        'scheduled_date',
        'completed_date',
        'cost',
        'status',
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(GaAsset::class, 'asset_id');
    }

    public function maintenanceType()
    {
        return $this->belongsTo(MaintenanceType::class, 'maintenance_type_id');
    }
}
