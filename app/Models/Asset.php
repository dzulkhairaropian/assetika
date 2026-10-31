<?php

namespace App\Models;

use App\Traits\HasDataScope;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasDataScope, LogsActivity, SoftDeletes;

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
        'is_components_stripped',
        'purchase_date',
        'release_date',
        'warranty_expiry_date',
        'purchase_cost',
        'invoice_number',
        'sale_price',
        'buyer_name',
        'sale_date',
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
        'is_components_stripped' => 'boolean',
        'purchase_date' => 'date',
        'release_date' => 'date',
        'warranty_expiry_date' => 'date',
        'retired_at' => 'date',
        'sale_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function scopeFilter($query, array $filters)
    {
        $normalize = function ($val) {
            if ($val === null || $val === '') {
                return [];
            }
            $arr = is_array($val) ? $val : explode(',', (string) $val);

            return array_values(array_filter(array_map(fn ($v) => is_string($v) ? trim($v) : $v, $arr), fn ($v) => $v !== null && $v !== ''));
        };

        if (! empty($filters['ids'])) {
            $ids = is_array($filters['ids']) ? $filters['ids'] : explode(',', (string) $filters['ids']);
            $ids = array_values(array_filter(array_map('trim', $ids), fn ($v) => $v !== ''));
            if (! empty($ids)) {
                $query->whereIn('assets.id', $ids);
            }
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('assets.asset_tag', 'like', $like)
                    ->orWhere('assets.serial_number', 'like', $like)
                    ->orWhere('assets.product_number', 'like', $like)
                    ->orWhere('assets.device_name', 'like', $like)
                    ->orWhere('assets.user_name', 'like', $like)
                    ->orWhere('assets.invoice_number', 'like', $like)
                    ->orWhere('assets.notes', 'like', $like)
                    ->orWhere('assets.specification', 'like', $like)
                    ->orWhereHas('product', function ($p) use ($like) {
                        $p->where('name', 'like', $like)
                            ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', $like))
                            ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like));
                    })
                    ->orWhereHas('status', fn ($s) => $s->where('name', 'like', $like))
                    ->orWhereHas('department', fn ($d) => $d->where('name', 'like', $like))
                    ->orWhereHas('location', fn ($l) => $l->where('name', 'like', $like))
                    ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', $like))
                    ->orWhereHas('assignedToUser', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like))
                    ->orWhereHas('currentCheckout.checkedOutToUser', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
            });
        }

        $statusIds = $normalize($filters['status_id'] ?? null);
        if (! empty($statusIds)) {
            $query->whereIn('assets.status_id', $statusIds);
        }

        $deptIds = $normalize($filters['department_id'] ?? null);
        if (! empty($deptIds)) {
            $query->whereIn('assets.department_id', $deptIds);
        }

        $locIds = $normalize($filters['location_id'] ?? null);
        if (! empty($locIds)) {
            $query->whereIn('assets.location_id', $locIds);
        }

        $prodIds = $normalize($filters['product_id'] ?? null);
        if (! empty($prodIds)) {
            $query->whereIn('assets.product_id', $prodIds);
        }

        $vendorIds = $normalize($filters['vendor_id'] ?? null);
        if (! empty($vendorIds)) {
            $query->whereIn('assets.vendor_id', $vendorIds);
        }

        $deprIds = $normalize($filters['depreciation_id'] ?? null);
        if (! empty($deprIds)) {
            $query->whereIn('assets.depreciation_id', $deprIds);
        }

        $catIds = $normalize($filters['category_id'] ?? null);
        if (! empty($catIds)) {
            $query->whereHas('product', fn ($q) => $q->whereIn('category_id', $catIds));
        }

        $brandIds = $normalize($filters['brand_id'] ?? null);
        if (! empty($brandIds)) {
            $query->whereHas('product', fn ($q) => $q->whereIn('brand_id', $brandIds));
        }

        $compIds = $normalize($filters['company_id'] ?? null);
        if (! empty($compIds)) {
            $query->whereHas('department', fn ($q) => $q->whereIn('company_id', $compIds));
        }

        if (! empty($filters['created_from'])) {
            $query->whereDate('assets.created_at', '>=', $filters['created_from']);
        }
        if (! empty($filters['created_to'])) {
            $query->whereDate('assets.created_at', '<=', $filters['created_to']);
        }

        if (! empty($filters['purchase_from'])) {
            $query->whereDate('assets.purchase_date', '>=', $filters['purchase_from']);
        }
        if (! empty($filters['purchase_to'])) {
            $query->whereDate('assets.purchase_date', '<=', $filters['purchase_to']);
        }

        if (isset($filters['cost_min']) && $filters['cost_min'] !== '') {
            $query->where('assets.purchase_cost', '>=', $filters['cost_min']);
        }
        if (isset($filters['cost_max']) && $filters['cost_max'] !== '') {
            $query->where('assets.purchase_cost', '<=', $filters['cost_max']);
        }

        $years = array_filter(array_map('intval', (array) ($filters['year'] ?? [])));
        if (! empty($years)) {
            $query->where(function ($q) use ($years) {
                foreach (array_values($years) as $idx => $year) {
                    $subWhere = function ($sub) use ($year) {
                        $sub->whereYear('assets.release_date', $year)
                            ->orWhere(function ($s2) use ($year) {
                                $s2->whereNull('assets.release_date')
                                    ->whereYear('assets.purchase_date', $year);
                            })
                            ->orWhere(function ($s2) use ($year) {
                                $s2->whereNull('assets.release_date')
                                    ->whereNull('assets.purchase_date')
                                    ->whereYear('assets.created_at', $year);
                            });
                    };

                    if ($idx === 0) {
                        $q->where($subWhere);
                    } else {
                        $q->orWhere($subWhere);
                    }
                }
            });
        }

        $agings = array_filter((array) ($filters['aging'] ?? []));
        if (! empty($agings)) {
            $now = Carbon::now();
            $query->where(function ($q) use ($agings, $now) {
                foreach (array_values($agings) as $idx => $aging) {
                    $agingClause = function ($sub) use ($aging, $now) {
                        switch ($aging) {
                            case '0-1':
                            case 'new':
                                $sub->where('assets.release_date', '>', $now->copy()->subYear())
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->where('assets.purchase_date', '>', $now->copy()->subYear());
                                    })
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereNull('assets.purchase_date')
                                            ->where('assets.created_at', '>', $now->copy()->subYear());
                                    });
                                break;
                            case '1-2':
                                $sub->whereBetween('assets.release_date', [$now->copy()->subYears(2), $now->copy()->subYear()])
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereBetween('assets.purchase_date', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                    })
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereNull('assets.purchase_date')
                                            ->whereBetween('assets.created_at', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                    });
                                break;
                            case '2-3':
                                $sub->whereBetween('assets.release_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)])
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereBetween('assets.purchase_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                    })
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereNull('assets.purchase_date')
                                            ->whereBetween('assets.created_at', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                    });
                                break;
                            case '2years':
                                $sub->whereBetween('assets.release_date', [$now->copy()->subYears(4), $now->copy()->subYears(2)])
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereBetween('assets.purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(2)]);
                                    })
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereNull('assets.purchase_date')
                                            ->whereBetween('assets.created_at', [$now->copy()->subYears(4), $now->copy()->subYears(2)]);
                                    });
                                break;
                            case '3-4':
                                $sub->whereBetween('assets.release_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)])
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereBetween('assets.purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                    })
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereNull('assets.purchase_date')
                                            ->whereBetween('assets.created_at', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                    });
                                break;
                            case '4+':
                            case '4years':
                            case 'aged':
                                $sub->where('assets.release_date', '<=', $now->copy()->subYears(4))
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->where('assets.purchase_date', '<=', $now->copy()->subYears(4));
                                    })
                                    ->orWhere(function ($s2) use ($now) {
                                        $s2->whereNull('assets.release_date')
                                            ->whereNull('assets.purchase_date')
                                            ->where('assets.created_at', '<=', $now->copy()->subYears(4));
                                    });
                                break;
                            case 'fully_depreciated':
                                $sub->whereHas('depreciation', function ($dep) use ($now) {
                                    $dep->whereRaw('TIMESTAMPDIFF(MONTH, COALESCE(assets.release_date, assets.purchase_date, assets.created_at), ?) >= depreciations.months', [$now->format('Y-m-d')]);
                                });
                                break;
                            case 'no_depreciation':
                                $sub->whereNull('assets.depreciation_id');
                                break;
                            case 'warranty_expired':
                                $sub->whereNotNull('assets.warranty_expiry_date')
                                    ->where('assets.warranty_expiry_date', '<', $now->format('Y-m-d'));
                                break;
                            case 'warranty_30d':
                                $sub->whereNotNull('assets.warranty_expiry_date')
                                    ->whereBetween('assets.warranty_expiry_date', [$now->format('Y-m-d'), $now->copy()->addDays(30)->format('Y-m-d')]);
                                break;
                        }
                    };

                    if ($idx === 0) {
                        $q->where($agingClause);
                    } else {
                        $q->orWhere($agingClause);
                    }
                }
            });
        }

        if (! empty($filters['tab'])) {
            switch ($filters['tab']) {
                case 'today':
                    $query->whereDate('assets.created_at', Carbon::today());
                    break;
                case 'draft':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Draft']));
                    break;
                case 'ready':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Ready to Deploy']));
                    break;
                case 'deployed':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Deployed', 'Borrowed']));
                    break;
                case 'service':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Service', 'Maintenance', 'In Repair']));
                    break;
                case 'broken':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Broken']));
                    break;
                case 'retired':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Retired', 'Disposal']));
                    break;
                case 'sold':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Sold', 'For Sale']));
                    break;
                case 'aged':
                    $now4 = Carbon::now()->subYears(4);
                    $query->where(function ($sub) use ($now4) {
                        $sub->where('assets.release_date', '<=', $now4)
                            ->orWhere(function ($s2) use ($now4) {
                                $s2->whereNull('assets.release_date')
                                    ->where('assets.purchase_date', '<=', $now4);
                            });
                    });
                    break;
            }
        }

        return $query;
    }

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

    public function lastCheckout()
    {
        return $this->hasOne(AssetCheckout::class)->latestOfMany('checked_out_at');
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

    public function transfers()
    {
        return $this->hasMany(AssetTransfer::class)->orderBy('created_at', 'desc');
    }

    // Checkout helper methods
    public function isCurrentlyCheckedOut(): bool
    {
        return $this->currentCheckout !== null &&
               $this->currentCheckout->isCurrentlyCheckedOut();
    }

    public function isOverdue(): bool
    {
        return $this->currentCheckout?->isOverdue() ?? false;
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

    public function getUtilizationSummaryAttribute(): array
    {
        $totalMinutes = 0;
        $userDurations = [];

        foreach ($this->checkoutHistory as $h) {
            $start = $h->checked_out_at;
            $end = $h->checked_in_at ?? now();
            if ($start) {
                $diff = $start->diffInMinutes($end);
                $totalMinutes += $diff;
                $uName = $h->checkedOutToUser?->name ?? 'Unknown';
                $userDurations[$uName] = ($userDurations[$uName] ?? 0) + $diff;
            }
        }

        arsort($userDurations);
        $topUser = ! empty($userDurations) ? key($userDurations) : null;
        $topMinutes = ! empty($userDurations) ? current($userDurations) : 0;

        $totalDays = (int) floor($totalMinutes / 1440);
        $totalMonths = (int) floor($totalDays / 30);
        $remainingDays = $totalDays % 30;

        if ($totalMonths >= 12) {
            $totalY = (int) floor($totalMonths / 12);
            $totalM = $totalMonths % 12;
            $totalDisplay = $totalY.'y '.($totalM > 0 ? $totalM.'m' : '');
        } elseif ($totalMonths >= 1) {
            $totalDisplay = $totalMonths.'m '.($remainingDays > 0 ? $remainingDays.'d' : '');
        } else {
            $totalDisplay = $totalDays > 0 ? $totalDays.' days' : '< 1 day';
        }

        $topDays = (int) floor($topMinutes / 1440);
        $topMonths = (int) floor($topDays / 30);
        if ($topMonths >= 1) {
            $topDisplay = $topMonths.' '.Str::plural('month', $topMonths);
        } else {
            $topDisplay = $topDays > 0 ? $topDays.' '.Str::plural('day', $topDays) : '< 1 day';
        }

        return [
            'total_display' => $totalDisplay,
            'top_user' => $topUser,
            'top_display' => $topDisplay,
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
        $yearPattern = substr($dateCode, 0, 2).'%';

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
        if (! $this->purchase_cost || ! $this->depreciation || ! $this->release_date) {
            return $this->purchase_cost ? (float) $this->purchase_cost : null;
        }

        $months = (int) $this->depreciation->months;
        if ($months <= 0) {
            return (float) $this->purchase_cost;
        }

        $perMonth = (float) $this->purchase_cost / $months;
        $elapsed = $this->release_date->diffInMonths(now());
        $depreciated = $perMonth * min($elapsed, $months);

        return max(0, (float) $this->purchase_cost - $depreciated);
    }

    public function installedComponents(): HasMany
    {
        return $this->hasMany(Component::class, 'assigned_asset_id');
    }
}
