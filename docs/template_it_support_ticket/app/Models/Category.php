<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'name',
        'code',
        'master_category_id',
        'description',
    ];

    public function masterCategory(): BelongsTo
    {
        return $this->belongsTo(MasterCategory::class);
    }
}
