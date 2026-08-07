<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Priority extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'label',
        'color',
        'icon',
        'response_time_minutes',
        'resolution_time_hours',
        'description',
        'example_cases',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'response_time_minutes' => 'integer',
            'resolution_time_hours' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
