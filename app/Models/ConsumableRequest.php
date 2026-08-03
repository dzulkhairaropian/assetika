<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConsumableRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsumableRequest extends Model
{
    protected $fillable = [
        'request_number',
        'requester_id',
        'department_id',
        'location_id',
        'processed_by',
        'status',
        'requested_at',
        'processed_at',
        'notes',
        'admin_notes',
    ];

    protected $casts = [
        'status' => ConsumableRequestStatus::class,
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            // Set requested_at first if not provided
            if (empty($model->requested_at)) {
                $model->requested_at = now();
            }
            
            // Generate request number based on requested_at date
            if (empty($model->request_number)) {
                $model->request_number = self::generateRequestNumber($model->requested_at);
            }
        });
    }

    /**
     * Generate a unique request number in format: CR/YYMMDD/001
     * 
     * Sequence resets daily based on the requested date.
     * This allows proper sequencing for backdated requests.
     * 
     * @param \Carbon\Carbon|string|null $date The date to use for generating the number (defaults to now)
     * @return string The generated request number
     */
    public static function generateRequestNumber(\Carbon\Carbon|string|null $date = null): string
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        
        $prefix = 'CR';
        $dateString = $date->format('ymd'); // YYMMDD format

        // Find the last request with requested_at on this specific date
        // Use requested_at instead of created_at for proper backdating support
        $lastRequest = self::whereDate('requested_at', $date->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        // Extract sequence from last request number, or start at 1
        $sequence = 1;
        if ($lastRequest && $lastRequest->request_number) {
            // Extract the last 3 digits from format CR/YYMMDD/001
            $parts = explode('/', $lastRequest->request_number);
            if (count($parts) === 3) {
                $sequence = ((int) $parts[2]) + 1;
            }
        }

        // Format: CR/YYMMDD/001
        return sprintf('%s/%s/%03d', $prefix, $dateString, $sequence);
    }

    // Relationships
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsumableRequestItem::class);
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === ConsumableRequestStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === ConsumableRequestStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === ConsumableRequestStatus::REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === ConsumableRequestStatus::COMPLETED;
    }

    public function canBeProcessed(): bool
    {
        return $this->isPending();
    }
}
