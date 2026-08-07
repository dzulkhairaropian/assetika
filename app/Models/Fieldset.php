<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Fieldset extends Model
{
    protected $fillable = ['name'];

    public function customFields(): BelongsToMany
    {
        return $this->belongsToMany(CustomField::class, 'custom_field_fieldset')
            ->withPivot(['order', 'is_required'])
            ->withTimestamps()
            ->orderBy('custom_field_fieldset.order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_fieldset')->withTimestamps();
    }
}
