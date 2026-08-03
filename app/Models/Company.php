<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'website',
        'address',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
