<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'company_id',
        'location_id',
        'department_id',
        'position_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the company that owns the user.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the department that owns the user.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    /**
     * Get the location that owns the user.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    /**
     * Get the position that owns the user.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Position::class);
    }

    /**
     * Get all asset checkouts assigned to this user.
     */
    public function assetCheckouts(): HasMany
    {
        return $this->hasMany(\App\Models\AssetCheckout::class, 'checked_out_to_user_id');
    }

    /**
     * Get currently held assets (not yet returned).
     */
    public function currentAssetCheckouts(): HasMany
    {
        return $this->hasMany(\App\Models\AssetCheckout::class, 'checked_out_to_user_id')
            ->whereNull('checked_in_at');
    }

    /**
     * Get asset checkout history (returned assets).
     */
    public function assetCheckoutHistory(): HasMany
    {
        return $this->hasMany(\App\Models\AssetCheckout::class, 'checked_out_to_user_id')
            ->whereNotNull('checked_in_at')
            ->orderBy('checked_in_at', 'desc');
    }

    /**
     * Get consumable transactions for this user.
     */
    public function consumableTransactions(): HasMany
    {
        return $this->hasMany(\App\Models\ConsumableTransaction::class, 'checked_out_to_user_id')
            ->where('type', 'checkout')
            ->orderBy('transaction_date', 'desc');
    }
}
