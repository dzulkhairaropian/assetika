<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class License extends Model
{
    protected $fillable = [
        'name',
        'product_key',
        'seats',
        'license_email',
        'manufacturer',
        'purchase_date',
        'expiration_date',
        'purchase_cost',
        'notes',
    ];

    protected $casts = [
        'seats' => 'integer',
        'purchase_date' => 'date',
        'expiration_date' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    public function seats(): HasMany
    {
        return $this->hasMany(LicenseSeat::class);
    }

    public function availableSeats(): int
    {
        return $this->seats - $this->seats()->count();
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'assigned_to', 'license_seats')
            ->withPivot(['checked_out_at', 'notes'])
            ->withTimestamps();
    }

    public function assets(): MorphToMany
    {
        return $this->morphedByMany(Asset::class, 'assigned_to', 'license_seats')
            ->withPivot(['checked_out_at', 'notes'])
            ->withTimestamps();
    }
}
