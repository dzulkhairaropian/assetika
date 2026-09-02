<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'code',
        'logo',
        'description',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}
