<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Component extends Model
{
    protected $fillable = [
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
        'warranty_months',
        'vendor_id',
        'location_id',
        'notes',
        'image',
    ];

    protected $casts = [
        'qty' => 'integer',
        'min_qty' => 'integer',
        'purchase_cost' => 'decimal:2',
        'purchase_date' => 'date',
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
