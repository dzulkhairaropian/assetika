<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    protected $fillable = [
        'name',
        'field_type',
        'field_values',
        'help_text',
        'default_value',
        'order',
    ];

    protected $casts = [
        'field_values' => 'array',
    ];

    public function fieldsets(): BelongsToMany
    {
        return $this->belongsToMany(Fieldset::class, 'custom_field_fieldset')
            ->withPivot(['order', 'is_required'])
            ->withTimestamps();
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
