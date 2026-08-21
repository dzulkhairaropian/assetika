<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $table = 'sla_policies';

    protected $fillable = [
        'priority_id',
        'response_target_minutes',
        'resolution_target_hours',
    ];

    protected $casts = [
        'response_target_minutes' => 'integer',
        'resolution_target_hours' => 'integer',
    ];

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }
}
