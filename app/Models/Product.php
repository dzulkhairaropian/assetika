<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'product_type',
        'brand_id',
        'category_id',
        'master_category_id',
        'description',
    ];

    // Relationships
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function masterCategory(): BelongsTo
    {
        return $this->belongsTo(MasterCategory::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function consumables()
    {
        return $this->hasMany(Consumable::class);
    }

    public function getModelAttribute()
    {
        return $this->name;
    }
}
