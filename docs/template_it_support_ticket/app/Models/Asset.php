<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_asset_id',
        'department_id',
        'product_id',
        'operating_system_id',
        'status_id',
        'location_id',
        'vendor_id',
        'assigned_to_user_id',
        'current_checkout_id',
        'asset_tag',
        'serial_number',
        'product_number',
        'device_name',
        'user_name',
        'license_windows',
        'license_office',
        'mac_address_wireless',
        'mac_address_ethernet',
        'specification',
        'accessories',
        'purchase_date',
        'release_date',
        'warranty_expiry_date',
        'purchase_cost',
        'invoice_number',
        'notes',
        'retired_at',
        'retirement_reason',
        'retirement_notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'release_date' => 'date',
        'warranty_expiry_date' => 'date',
        'retired_at' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    public function parentAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'parent_asset_id');
    }

    public function peripherals(): HasMany
    {
        return $this->hasMany(Asset::class, 'parent_asset_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function operatingSystem(): BelongsTo
    {
        return $this->belongsTo(OperatingSystem::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
