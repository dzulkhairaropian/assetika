<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TicketsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    use Exportable;

    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Ticket::with([
            'ticketType',
            'requesterUser',
            'requesterCompany',
            'requesterDepartment',
            'ticketCategory',
            'ticketSubcategory',
            'priority',
            'status',
            'slaStatus',
            'assignedTo',
            'createdBy',
            'asset',
        ]);

        if (! empty($this->filters['tab'])) {
            switch ($this->filters['tab']) {
                case 'created_today':
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'new':
                    $query->whereHas('status', fn ($q) => $q->where('name', 'New'));
                    break;
                case 'in_progress':
                    $query->whereHas('status', fn ($q) => $q->where('name', 'In Progress'));
                    break;
                case 'pending':
                    $query->whereHas('status', fn ($q) => $q->where('name', 'Pending'));
                    break;
                case 'unassigned':
                    $query->whereNull('assigned_to_id');
                    break;
                case 'closed':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Resolved', 'Closed'])->orWhere('is_closed', true));
                    break;
            }
        }

        $dateType = $this->filters['date_type'] ?? 'created_at';
        $startDate = $this->filters['start_date'] ?? $this->filters['from_date'] ?? $this->filters['created_from'] ?? null;
        $endDate = $this->filters['end_date'] ?? $this->filters['to_date'] ?? $this->filters['created_to'] ?? null;

        if (! empty($startDate)) {
            if ($dateType === 'request_date') {
                $query->whereDate('request_date', '>=', $startDate);
            } elseif ($dateType === 'both') {
                $query->where(function ($q) use ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate)
                        ->orWhereDate('request_date', '>=', $startDate);
                });
            } else {
                $query->whereDate('created_at', '>=', $startDate);
            }
        }

        if (! empty($endDate)) {
            if ($dateType === 'request_date') {
                $query->whereDate('request_date', '<=', $endDate);
            } elseif ($dateType === 'both') {
                $query->where(function ($q) use ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate)
                        ->orWhereDate('request_date', '<=', $endDate);
                });
            } else {
                $query->whereDate('created_at', '<=', $endDate);
            }
        }
        if (! empty($this->filters['company_id'])) {
            $query->where('requester_company_id', $this->filters['company_id']);
        }
        if (! empty($this->filters['department_id'])) {
            $query->where('requester_department_id', $this->filters['department_id']);
        }
        if (! empty($this->filters['ticket_category_id'])) {
            $query->where('ticket_category_id', $this->filters['ticket_category_id']);
        } elseif (! empty($this->filters['category_id'])) {
            $query->where('ticket_category_id', $this->filters['category_id']);
        }
        if (! empty($this->filters['priority_id'])) {
            $query->where('priority_id', $this->filters['priority_id']);
        }
        if (! empty($this->filters['status_id'])) {
            $query->where('status_id', $this->filters['status_id']);
        }
        if (! empty($this->filters['created_by_id'])) {
            $query->where('created_by_id', $this->filters['created_by_id']);
        }
        if (! empty($this->filters['assigned_to_id'])) {
            $query->where('assigned_to_id', $this->filters['assigned_to_id']);
        }
        if (! empty($this->filters['year']) && $this->filters['year'] !== 'all') {
            $query->whereRaw('YEAR(created_at) = ?', [$this->filters['year']]);
        }
        if (! empty($this->filters['month']) && $this->filters['month'] !== 'all') {
            $query->whereRaw('MONTH(created_at) = ?', [$this->filters['month']]);
        }

        return $query->latest('request_date');
    }

    public function headings(): array
    {
        return [
            'Ticket Key',
            'Type',
            'Created Date',
            'Request Date',
            'Requester Name',
            'Requester Email',
            'Company',
            'Department',
            'Category',
            'Subcategory',
            'Priority',
            'Status',
            'SLA Status',
            'Assigned To',
            'Created By',
            'Linked Asset',
            'Problem Description',
            'Resolution Notes',
            'Completed Date',
            'Estimated Hours',
            'Actual Hours',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->request_id,
            $ticket->ticketType?->name ?? 'REQ',
            $ticket->created_at?->format('Y-m-d H:i'),
            $ticket->request_date?->format('Y-m-d'),
            $ticket->requester_name ?? $ticket->requesterUser?->name,
            $ticket->requester_email ?? $ticket->requesterUser?->email,
            $ticket->requesterCompany?->name ?? '-',
            $ticket->requesterDepartment?->name ?? '-',
            $ticket->ticketCategory?->name ?? '-',
            $ticket->ticketSubcategory?->name ?? '-',
            $ticket->priority?->name ?? '-',
            $ticket->status?->name ?? '-',
            $ticket->slaStatus?->name ?? '-',
            $ticket->assignedTo?->name ?? 'Unassigned',
            $ticket->createdBy?->name ?? '-',
            $ticket->asset ? "[{$ticket->asset->asset_tag}] {$ticket->asset->device_name}" : '-',
            $ticket->problem_description,
            $ticket->resolution_notes,
            $ticket->completed_date?->format('Y-m-d'),
            $ticket->estimated_hours,
            $ticket->actual_hours,
        ];
    }
}
