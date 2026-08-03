<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingSystem extends Model
{
    protected $table = 'operating_systems';

    protected $fillable = [
        'name',
        'brand_id',
        'description',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
