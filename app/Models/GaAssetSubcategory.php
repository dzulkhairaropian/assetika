<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaAssetSubcategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'code',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(GaAssetCategory::class);
    }

    public function assets()
    {
        return $this->hasMany(GaAsset::class, 'subcategory_id');
    }
}
