<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaAssetCategory extends Model
{
    protected $guarded = [];

    public function assets()
    {
        return $this->hasMany(GaAsset::class, 'category_id');
    }
}
