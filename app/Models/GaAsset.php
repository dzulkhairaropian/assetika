<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GaAsset extends Model
{
    protected $guarded = [];

    protected $casts = [
        "qty" => "integer",
        "purchase_date" => "date",
        "purchase_price" => "decimal:2",
        "additional_cost" => "decimal:2",
        "total_asset_value" => "decimal:2",
        "warranty_expiry_date" => "date",
        "retired_at" => "date",
    ];

    public function category()
    {
        return $this->belongsTo(GaAssetCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(GaAssetSubcategory::class, "subcategory_id");
    }

    public function vehicle()
    {
        return $this->hasOne(GaAssetVehicle::class, "asset_id");
    }

    public function currentHolder()
    {
        return $this->belongsTo(User::class, "current_holder_id");
    }

    public function assignments()
    {
        return $this->hasMany(GaAssetAssignment::class, "asset_id");
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(GaMaintenanceLog::class, "asset_id");
    }

    public function getRetirementEvidenceUrlAttribute(): ?string
    {
        $path = trim((string) ($this->retirement_evidence_path ?? ""));

        if ($path === "") {
            return null;
        }

        return Storage::disk("public")->url($path);
    }

    public function getStatusObjectAttribute()
    {
        return \App\Models\Status::where('name', $this->status)->first();
    }

    public function setRetirementEvidencePathAttribute($value): void
    {
        $path = is_string($value) ? trim($value) : $value;
        $this->attributes["retirement_evidence_path"] = blank($path)
            ? null
            : $path;
    }
}
