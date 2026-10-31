<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'code',
        'master_category_id',
        'device_type',
        'useful_life_years',
        'description',
    ];

    protected $casts = [
        'useful_life_years' => 'integer',
    ];

    public function getDeviceTypeLabelAttribute(): string
    {
        return match ($this->device_type) {
            'laptop' => 'Laptop / Notebook',
            'desktop' => 'Desktop / Workstation',
            'display' => 'Display / Monitor',
            'infrastructure' => 'Server & Network',
            'mobile' => 'Mobile & Tablet',
            'peripheral' => 'Peripheral',
            default => 'General Hardware',
        };
    }

    public function masterCategory()
    {
        return $this->belongsTo(MasterCategory::class, 'master_category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
