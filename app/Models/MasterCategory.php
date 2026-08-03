<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCategory extends Model
{
    use SoftDeletes;

    protected $table = 'master_categories';

    protected $fillable = [
        'name',
        'description',
    ];

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }
}
