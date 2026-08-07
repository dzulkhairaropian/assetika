<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eula extends Model
{
    protected $fillable = ['category_id', 'title', 'body'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
