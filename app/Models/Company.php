<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'group_id',
        'name',
        'code',
        'email',
        'phone',
        'website',
        'address',
        'logo',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
