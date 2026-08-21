<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TicketsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
            'asset'
        ]);

        if (!empty($this->filters['from_date'])) {
            $query->whereDate('request_date', '>=', $this->filters['from_date']);
        }
        if (!empty($this->filters['to_date'])) {
            $query->whereDate('request_date', '<=', $this->filters['to_date']);
        }
        if (!empty($this->filters['company_id'])) {
            $query->where('requester_company_id', $this->filters['company_id']);
        }
        if (!empty($this->filters['department_id'])) {
            $query->where('requester_department_id', $this->filters['department_id']);
        }
        if (!empty($this->filters['ticket_category_id'])) {
            $query->where('ticket_category_id', $this->filters['ticket_category_id']);
        }
        if (!empty($this->filters['priority_id'])) {
            $query->where('priority_id', $this->filters['priority_id']);
        }
        if (!empty($this->filters['status_id'])) {
            $query->where('status_id', $this->filters['status_id']);
        }
        if (!empty($this->filters['created_by_id'])) {
            $query->where('created_by_id', $this->filters['created_by_id']);
        }
        if (!empty($this->filters['assigned_to_id'])) {
            $query->where('assigned_to_id', $this->filters['assigned_to_id']);
        }

        return $query->latest('request_date');
    }

    public function headings(): array
    {
        return [
            'Ticket Key',
            'Type',
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
