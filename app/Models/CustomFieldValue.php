<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends Model
{
    protected $fillable = [
        'custom_field_id',
        'valuable_type',
        'valuable_id',
        'value',
    ];

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }

    public function valuable(): MorphTo
    {
        return $this->morphTo();
    }
}
