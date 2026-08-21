<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Renewal extends Model
{
    use HasFactory;

    protected $table = 'renewals';

    protected $fillable = [
        'name',
        'type',
        'renewable_type',
        'renewable_id',
        'issued_date',
        'expiration_date',
        'reminder_days_before',
        'vendor_id',
        'responsible_user_id',
        'cost',
        'auto_renew',
        'status',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiration_date' => 'date',
        'reminder_days_before' => 'integer',
        'cost' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    public function renewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
