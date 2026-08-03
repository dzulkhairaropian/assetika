<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GaAssetCategory;
use App\Models\GaAsset;

class GaAssetSubcategory extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(GaAssetCategory::class);
    }

    public function assets()
    {
        return $this->hasMany(GaAsset::class, 'subcategory_id');
    }
}
