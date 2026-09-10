<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GaAsset extends Model
{
    protected $fillable = [
        'asset_code',
        'name',
        'category_id',
        'subcategory_id',
        'brand',
        'model',
        'serial_number',
        'status',
        'qty',
        'purchase_date',
        'purchase_price',
        'additional_cost',
        'total_asset_value',
        'warranty_expiry_date',
        'current_holder_id',
        'image_path',
        'retirement_evidence_path',
        'retired_at',
        'retirement_reason',
        'notes',
    ];

    protected $casts = [
        'qty' => 'integer',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'additional_cost' => 'decimal:2',
        'total_asset_value' => 'decimal:2',
        'warranty_expiry_date' => 'date',
        'retired_at' => 'date',
    ];

    public function scopeFilter($query, array $filters)
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('asset_code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('model', 'like', $like)
                    ->orWhere('serial_number', 'like', $like);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query;
    }

    public function category()
    {
        return $this->belongsTo(GaAssetCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(GaAssetSubcategory::class, 'subcategory_id');
    }

    public function vehicle()
    {
        return $this->hasOne(GaAssetVehicle::class, 'asset_id');
    }

    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    public function assignments()
    {
        return $this->hasMany(GaAssetAssignment::class, 'asset_id');
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(GaMaintenanceLog::class, 'asset_id');
    }

    public function getRetirementEvidenceUrlAttribute(): ?string
    {
        $path = trim((string) ($this->retirement_evidence_path ?? ''));

        if ($path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function getStatusObjectAttribute()
    {
        return Status::where('name', $this->status)->first();
    }

    public function setRetirementEvidencePathAttribute($value): void
    {
        $path = is_string($value) ? trim($value) : $value;
        $this->attributes['retirement_evidence_path'] = blank($path)
            ? null
            : $path;
    }
}
