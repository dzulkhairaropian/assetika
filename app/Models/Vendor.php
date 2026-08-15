<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $table = 'vendors';

    protected $fillable = [
        'name',
        'email',
        'contact_person',
        'phone',
        'website',
        'address',
        'npwp_number',
        'npwp_name',
        'npwp_photo',
        'nib',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'notes',
    ];
}
