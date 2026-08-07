<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kit extends Model
{
    protected $fillable = ['name', 'description'];

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'kit_asset')->withPivot('qty')->withTimestamps();
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'kit_license')->withPivot('qty')->withTimestamps();
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'kit_component')->withPivot('qty')->withTimestamps();
    }

    public function consumables(): BelongsToMany
    {
        return $this->belongsToMany(Consumable::class, 'kit_consumable')->withPivot('qty')->withTimestamps();
    }
}
