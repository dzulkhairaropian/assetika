<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Status extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'color',
        'master_category_id',
        'description',
    ];

    public function masterCategory(): BelongsTo
    {
        return $this->belongsTo(MasterCategory::class);
    }
}
