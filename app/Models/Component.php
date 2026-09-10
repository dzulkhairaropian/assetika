<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Component extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'manufacturer',
        'model_number',
        'serial',
        'qty',
        'min_qty',
        'purchase_cost',
        'location_id',
        'notes',
        'image',
    ];

    protected $casts = [
        'qty' => 'integer',
        'min_qty' => 'integer',
        'purchase_cost' => 'decimal:2',
    ];

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
