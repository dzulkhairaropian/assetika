<?php

namespace App\Models;

use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;


class Asset extends Model
{
    use SoftDeletes, HasCustomFields;
    protected $table = 'assets';

    protected $fillable = [
        'parent_asset_id',
        'product_id',
        'operating_system_id',
        'status_id',
        'vendor_id',
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
        // These will be managed through checkouts but kept for compatibility
        'assigned_to_user_id',
        'department_id',
        'location_id',
        'current_checkout_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'release_date' => 'date',
        'warranty_expiry_date' => 'date',
        'retired_at' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    // Relationships

    /**
     * The parent asset this asset belongs to (e.g. a keyboard belonging to a laptop).
     */
    public function parentAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'parent_asset_id');
    }

    /**
     * Child assets (peripherals) attached to this asset.
     */
    public function peripherals()
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

    public function checkedOutToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function getNameAttribute()
    {
        return $this->device_name ?: ($this->product->name ?? 'Unnamed Asset');
    }

    public function getQuantityAttribute()
    {
        return 1;
    }

    // Checkout relationships
    public function currentCheckout(): BelongsTo
    {
        return $this->belongsTo(AssetCheckout::class, 'current_checkout_id');
    }

    public function checkouts()
    {
        return $this->hasMany(AssetCheckout::class);
    }

    public function checkoutHistory()
    {
        return $this->hasMany(AssetCheckout::class)->orderBy('checked_out_at', 'desc');
    }

    // Maintenance relationship
    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class)->orderBy('start_date', 'desc');
    }

    // Checkout helper methods
    public function isCurrentlyCheckedOut(): bool
    {
        return $this->currentCheckout !== null &&
               $this->currentCheckout->isCurrentlyCheckedOut();
    }

    public function isAvailable(): bool
    {
        return ! $this->isCurrentlyCheckedOut();
    }

    public function getCurrentAssignment()
    {
        if (! $this->isCurrentlyCheckedOut()) {
            return null;
        }

        return [
            'user' => $this->currentCheckout->checkedOutToUser,
            'department' => $this->currentCheckout->department,
            'location' => $this->currentCheckout->location,
            'checked_out_at' => $this->currentCheckout->checked_out_at,
            'checked_out_by' => $this->currentCheckout->checkedOutByUser,
            'notes' => $this->currentCheckout->checkout_notes,
        ];
    }

    // Scopes
    public function scopeCurrentlyCheckedOut($query)
    {
        return $query->whereNotNull('current_checkout_id');
    }

    public function scopeAvailableForCheckout($query)
    {
        return $query->whereNull('current_checkout_id');
    }

    public function scopeCheckedOutToUser($query, $userId)
    {
        return $query->whereHas('currentCheckout', function ($q) use ($userId) {
            $q->where('checked_out_to_user_id', $userId)
                ->whereNull('checked_in_at');
        });
    }

    // Generate Asset Tag and Device Name
    protected static function booted(): void
    {
        $generateTags = function (Asset $asset) {
            // Eager-load needed relations from provided IDs when not yet loaded
            if (! $asset->relationLoaded('department') && $asset->department_id) {
                $asset->setRelation('department', Department::query()->with('company')->find($asset->department_id));
            }
            if (! $asset->relationLoaded('product') && $asset->product_id) {
                $asset->setRelation('product', Product::query()->with('category')->find($asset->product_id));
            }

            $companyCode = optional(optional($asset->department)->company)->code; // e.g., DB
            $categoryCode = optional(optional($asset->product)->category)->code;  // e.g., NB
            $departmentCode = optional($asset->department)->code;                  // e.g., IT

            // Generate Asset Tag: company/category/yyMMdd/sequence => DB/NB/251018/001
            if (empty($asset->asset_tag) && $companyCode && $categoryCode) {
                $date = $asset->release_date ? Carbon::parse($asset->release_date) : Carbon::now();
                $dateCode = $date->format('ymd');
                $sequence = static::nextAssetTagSequence($asset, $companyCode, $categoryCode, $dateCode);
                $asset->asset_tag = sprintf('%s/%s/%s/%s', $companyCode, $categoryCode, $dateCode, str_pad((string) $sequence, 3, '0', STR_PAD_LEFT));
            }

            // Generate Device Name: companycodecategorycodedepartment-sequence => DBNBIT-001
            if (empty($asset->device_name) && $companyCode && $categoryCode && $departmentCode) {
                $prefix = $companyCode.$categoryCode.$departmentCode;
                $sequence = static::nextDeviceNameSequence($asset, $prefix);
                $asset->device_name = sprintf('%s-%s', $prefix, str_pad((string) $sequence, 3, '0', STR_PAD_LEFT));
            }
        };

        static::creating($generateTags);
        static::updating($generateTags);
    }

    // Generate Asset Tag Sequence
    protected static function nextAssetTagSequence(Asset $asset, string $companyCode, string $categoryCode, string $dateCode): int
    {
        // Count existing assets for the same company + category within the same year
        // This ensures the sequence continues through the year but resets annually
        $year = Carbon::createFromFormat('ymd', $dateCode)->year;
        $yearCode = $dateCode; // Extract year from the date code (first 2 digits)

        // Create a pattern that matches the year portion of the date code
        // For example, if dateCode is "250101", we match "25%" to get all assets from 2025
        $yearPattern = substr($dateCode, 0, 2) . '%';

        // Count assets with the same company, category and year in the asset tag
        $count = static::query()
            ->where('asset_tag', 'like', sprintf('%s/%s/%s%%', $companyCode, $categoryCode, $yearPattern))
            ->count();

        $next = $count + 1;

        // Ensure uniqueness in case of concurrent requests
        do {
            $candidate = sprintf('%s/%s/%s/%s', $companyCode, $categoryCode, $dateCode, str_pad((string) $next, 3, '0', STR_PAD_LEFT));
            $exists = static::query()->where('asset_tag', $candidate)->exists();
            if (! $exists) {
                return $next;
            }
            $next++;
        } while (true);
    }

    // Generate Device Name Sequence
    protected static function nextDeviceNameSequence(Asset $asset, string $prefix): int
    {
        // Count existing assets with the same prefix regardless of date, then add 1
        $count = static::query()
            ->where('device_name', 'like', $prefix.'-%')
            ->count();

        $next = $count + 1;

        // Ensure uniqueness
        do {
            $candidate = sprintf('%s-%s', $prefix, str_pad((string) $next, 3, '0', STR_PAD_LEFT));
            $exists = static::query()->where('device_name', $candidate)->exists();
            if (! $exists) {
                return $next;
            }
            $next++;
        } while (true);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'component_asset')
            ->withPivot(['qty', 'checked_out_at'])
            ->withTimestamps();
    }

    public function depreciation()
    {
        return $this->belongsTo(Depreciation::class);
    }

    public function getBookValueAttribute(): ?float
    {
        if (!$this->purchase_cost || !$this->depreciation || !$this->purchase_date) {
            return $this->purchase_cost ? (float) $this->purchase_cost : null;
        }

        $months = $this->depreciation->months;
        $perMonth = (float) $this->purchase_cost / $months;
        $elapsed = $this->purchase_date->diffInMonths(now());
        $depreciated = $perMonth * min($elapsed, $months);

        return max(0, (float) $this->purchase_cost - $depreciated);
    }
}
