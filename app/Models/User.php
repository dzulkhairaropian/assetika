<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, CanResetPassword {
        HasRoles::hasPermissionTo as parentHasPermissionTo;
    }

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
        'is_active',
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

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhereHas('department', fn($d) => $d->where('name', 'like', $like))
                    ->orWhereHas('company', fn($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('position', fn($p) => $p->where('name', 'like', $like))
                    ->orWhereHas('location', fn($l) => $l->where('name', 'like', $like))
                    ->orWhereHas('roles', fn($r) => $r->where('name', 'like', $like));
            });
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        return $query;
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

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by_id');
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->parentHasPermissionTo($permission, $guardName);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to_id');
    }

    public function requestedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_user_id');
    }
}
