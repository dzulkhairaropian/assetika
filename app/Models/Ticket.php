<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

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
        'category_id',
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
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'request_time' => 'datetime', // Cast to datetime to use time functions
            'completed_date' => 'date',
            'completed_time' => 'datetime',
            'response_time_minutes' => 'integer',
            'resolution_time_hours' => 'decimal:2',
        ];
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
