<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'code',
        'master_category_id',
        'description',
    ];

    public function masterCategory()
    {
        return $this->belongsTo(MasterCategory::class, 'master_category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
