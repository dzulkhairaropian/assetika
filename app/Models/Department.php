<?php

namespace App\Models;

use App\Traits\HasDataScope;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasDataScope;

    protected $table = 'departments';

    protected $with = ['company'];

    protected $fillable = [
        'name',
        'code',
        'company_id',
        'description',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
