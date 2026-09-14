<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Component extends Model
{
    protected $fillable = [
        'component_tag',
        'name',
        'product_id',
        'assigned_asset_id',
        'status_id',
        'status',
        'category_id',
        'manufacturer',
        'model_number',
        'serial',
        'qty',
        'min_qty',
        'purchase_cost',
        'purchase_date',
        'release_date',
        'warranty_months',
        'vendor_id',
        'location_id',
        'notes',
        'image',
    ];

    protected static function booted()
    {
        $generateTag = function ($component) {
            if (empty($component->component_tag)) {
                $userCompanyCode = auth()->user()?->company?->code ?? Company::first()?->code ?? 'DB';
                $companyCode = strtoupper(trim($userCompanyCode));

                $category = $component->category ?? ($component->category_id ? Category::find($component->category_id) : null);
                $catRaw = $category?->code ?: ($category?->name ?: 'COMP');
                $categoryCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $catRaw), 0, 4));
                if (empty($categoryCode)) {
                    $categoryCode = 'COMP';
                }

                $date = $component->purchase_date ? Carbon::parse($component->purchase_date) : ($component->release_date ? Carbon::parse($component->release_date) : now());
                $dateCode = $date->format('ymd');

                // Sequence calculation identical to Asset Tag (per company, category, and year)
                $yearPattern = substr($dateCode, 0, 2).'%';
                $count = static::query()
                    ->where('component_tag', 'like', sprintf('%s/%s/%s%%', $companyCode, $categoryCode, $yearPattern))
                    ->count();

                $next = $count + 1;
                do {
                    $candidate = sprintf('%s/%s/%s/%s', $companyCode, $categoryCode, $dateCode, str_pad((string) $next, 3, '0', STR_PAD_LEFT));
                    $exists = static::query()->where('component_tag', $candidate)->where('id', '!=', $component->id)->exists();
                    if (! $exists) {
                        $component->component_tag = $candidate;

                        break;
                    }
                    $next++;
                } while (true);
            }
        };

        static::creating($generateTag);
        static::updating($generateTag);
    }

    protected $casts = [
        'qty' => 'integer',
        'min_qty' => 'integer',
        'purchase_cost' => 'decimal:2',
        'purchase_date' => 'date',
        'release_date' => 'date',
        'warranty_months' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function assignedAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'assigned_asset_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'component_asset')
            ->withPivot(['qty', 'checked_out_at'])
            ->withTimestamps();
    }

    public function availableQty(): int
    {
        $checkedOut = $this->assets()->sum('component_asset.qty');

        return $this->qty - $checkedOut;
    }
}
