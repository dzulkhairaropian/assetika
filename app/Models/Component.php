<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Component extends Model
{
    protected $fillable = [
        'component_tag',
        'name',
        'product_id',
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
        static::creating(function ($component) {
            if (empty($component->component_tag)) {
                $categoryCode = strtoupper(substr($component->category?->code ?? $component->category?->name ?? 'CMP', 0, 4));
                $dateCode = now()->format('ymd');
                $count = static::query()->whereDate('created_at', now()->toDateString())->count() + 1;
                $component->component_tag = sprintf('CMP/%s/%s/%s', $categoryCode, $dateCode, str_pad((string) $count, 3, '0', STR_PAD_LEFT));
            }
        });
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
