<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'request_id',
        'ticket_type_id',
        'requester_user_id',
        'request_date',
        'request_time',
        'requester_name',
        'requester_email',
        'requester_company_id',
        'requester_department_id',
        'asset_id',
        'ticket_category_id',
        'ticket_subcategory_id',
        'priority_id',
        'label_id',
        'source_id',
        'problem_description',
        'assigned_to_id',
        'created_by_id',
        'completed_date',
        'completed_time',
        'response_time_minutes',
        'resolution_time_hours',
        'resolution_notes',
        'status_id',
        'sla_status_id',
        'sla_due_at',
        'internal_notes',
        'estimated_hours',
        'actual_hours',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'request_time' => 'datetime', // Cast to datetime to use time functions
            'completed_date' => 'date',
            'completed_time' => 'datetime',
            'sla_due_at' => 'datetime',
            'response_time_minutes' => 'integer',
            'resolution_time_hours' => 'decimal:2',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
        ];
    }

    /**
     * Whether the ticket has breached its SLA resolution target.
     * Uses sla_due_at (computed from sla_policies) with a legacy 3-day fallback.
     */
    public function isSlaOverdue(): bool
    {
        if ($this->status?->is_closed || $this->status?->is_paused) {
            return false;
        }

        if ($this->sla_due_at) {
            return now()->gt($this->sla_due_at);
        }

        return $this->created_at !== null && $this->created_at->diffInDays(now()) >= 3;
    }

    public function scopeFilter($query, array $filters)
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('request_id', 'like', $like)
                    ->orWhere('problem_description', 'like', $like)
                    ->orWhere('requester_name', 'like', $like)
                    ->orWhere('requester_email', 'like', $like);
            });
        }

        if (! empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (! empty($filters['priority_id'])) {
            $query->where('priority_id', $filters['priority_id']);
        }

        if (! empty($filters['assigned_to_id'])) {
            $query->where('assigned_to_id', $filters['assigned_to_id']);
        }

        if (! empty($filters['ticket_category_id'])) {
            $query->where('ticket_category_id', $filters['ticket_category_id']);
        }

        return $query;
    }

    // Relationships
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function requesterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function requesterCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'requester_company_id');
    }

    public function requesterDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requester_department_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ticketCategory(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function ticketSubcategory(): BelongsTo
    {
        return $this->belongsTo(TicketSubcategory::class, 'ticket_subcategory_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function slaStatus(): BelongsTo
    {
        return $this->belongsTo(SlaStatus::class, 'sla_status_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
