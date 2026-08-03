<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';

    protected $fillable = [
        'name',
        'icon',
        'color',
        'master_category_id',
        'description',
    ];

    public function masterCategory()
    {
        return $this->belongsTo(MasterCategory::class, 'master_category_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function consumables()
    {
        return $this->hasMany(Consumable::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
