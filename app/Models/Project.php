<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'owner_id',
        'status_id',
        'status',
        'priority',
        'start_date',
        'due_date',
        'budget',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function statusRel(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function getProgressPercentageAttribute(): int
    {
        $tasks = $this->tasks()->with('statusRel')->get();
        $total = $tasks->count();
        if ($total === 0) {
            return 0;
        }

        $totalScore = 0;
        foreach ($tasks as $t) {
            $stName = strtolower($t->statusRel?->name ?? $t->status ?? '');
            if (str_contains($stName, 'done') || str_contains($stName, 'completed')) {
                $totalScore += 100;
            } elseif (str_contains($stName, 'review')) {
                $totalScore += 80;
            } elseif (str_contains($stName, 'progress') || str_contains($stName, 'doing')) {
                $totalScore += 50;
            } else {
                $totalScore += 0; // To Do / Backlog
            }
        }

        return (int) round($totalScore / $total);
    }
}
