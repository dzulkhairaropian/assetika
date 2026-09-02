<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaAssetCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'useful_life_years',
        'description',
    ];

    public function assets()
    {
        return $this->hasMany(GaAsset::class, 'category_id');
    }
}
