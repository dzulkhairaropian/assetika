<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Attachment;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use App\Models\Label;
use App\Models\Priority;
use App\Models\SlaStatus;
use App\Models\Source;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Exports\TicketsExport;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function export(Request $request)
    {
        return (new TicketsExport($request->all()))->download('tickets-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function index(Request $request): View
    {
        $query = Ticket::with(['ticketCategory', 'ticketSubcategory', 'priority', 'status', 'slaStatus', 'assignedTo', 'requesterUser', 'requesterDepartment']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('request_id', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('problem_description', 'like', "%{$search}%");
            });
        }

        if ($startDate = ($request->input('start_date') ?: $request->input('from_date'))) {
            $query->where(function($q) use ($startDate) {
                $q->whereDate('request_date', '>=', $startDate)
                  ->orWhere(function($sub) use ($startDate) {
                      $sub->whereNull('request_date')
                          ->whereDate('created_at', '>=', $startDate);
                  });
            });
        }

        if ($endDate = ($request->input('end_date') ?: $request->input('to_date'))) {
            $query->where(function($q) use ($endDate) {
                $q->whereDate('request_date', '<=', $endDate)
                  ->orWhere(function($sub) use ($endDate) {
                      $sub->whereNull('request_date')
                          ->whereDate('created_at', '<=', $endDate);
                  });
            });
        }

        if ($status = $request->input('status_id')) {
            $query->where('status_id', $status);
        }

        if ($priority = $request->input('priority_id')) {
            $query->where('priority_id', $priority);
        }

        if ($catId = ($request->input('ticket_category_id') ?: $request->input('category_id'))) {
            $query->where('ticket_category_id', $catId);
        }

        if ($companyId = $request->input('company_id')) {
            $query->where('requester_company_id', $companyId);
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('requester_department_id', $deptId);
        }

        $ticketSummary = [
            'total' => (clone $query)->count(),
            'new' => (clone $query)->whereHas('status', fn($q) => $q->where('name', 'New'))->count(),
            'in_progress' => (clone $query)->whereHas('status', fn($q) => $q->where('name', 'In Progress'))->count(),
            'pending' => (clone $query)->whereHas('status', fn($q) => $q->where('name', 'Pending'))->count(),
            'unassigned' => (clone $query)->whereNull('assigned_to_id')->count(),
            'closed' => (clone $query)->whereHas('status', fn($q) => $q->whereIn('name', ['Resolved', 'Closed'])->orWhere('is_closed', true))->count(),
        ];

        switch ($request->input('tab')) {
            case 'new':
                $query->whereHas('status', fn($q) => $q->where('name', 'New'));
                break;
            case 'in_progress':
                $query->whereHas('status', fn($q) => $q->where('name', 'In Progress'));
                break;
            case 'pending':
                $query->whereHas('status', fn($q) => $q->where('name', 'Pending'));
                break;
            case 'unassigned':
                $query->whereNull('assigned_to_id');
                break;
            case 'closed':
                $query->whereHas('status', fn($q) => $q->whereIn('name', ['Resolved', 'Closed'])->orWhere('is_closed', true));
                break;
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $statuses = TicketStatus::all();
        $priorities = Priority::all();

        $companies = Company::all();
        $departments = Department::all();
        $categories = TicketCategory::all();
        $users = User::all();

        return view('pages.tickets.index', compact('tickets', 'statuses', 'priorities', 'search', 'ticketSummary', 'companies', 'departments', 'categories', 'users'));
    }

    public function create(): View
    {
        $users = User::all();
        $ticketTypes = TicketType::all();
        $ticketCategories = TicketCategory::where('is_active', true)->with('subcategories')->get();
        $priorities = Priority::all();
        $labels = Label::all();
        $sources = Source::all();
        $ticketStatuses = TicketStatus::all();
        $slaStatuses = SlaStatus::all();
        $assets = Asset::with([
            'product.brand',
            'assignedToUser',
            'currentCheckout.checkedOutToUser',
            'department',
            'status'
        ])->latest('id')->get();

        return view('pages.tickets.create', compact(
            'users',
            'ticketTypes',
            'ticketCategories',
            'priorities',
            'labels',
            'sources',
            'ticketStatuses',
            'slaStatuses',
            'assets'
        ))->with('ticket', null);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'created_at' => ['nullable', 'date'],
            'ticket_type_id' => ['nullable', 'exists:ticket_types,id'],
            'requester_user_id' => ['required', 'exists:users,id'],
            'ticket_category_id' => ['nullable', 'exists:ticket_categories,id'],
            'ticket_subcategory_id' => ['nullable', 'exists:ticket_subcategories,id'],
            'priority_id' => ['nullable', 'exists:priorities,id'],
            'label_id' => ['nullable', 'exists:labels,id'],
            'source_id' => ['nullable', 'exists:sources,id'],
            'status_id' => ['nullable', 'exists:ticket_statuses,id'],
            'sla_status_id' => ['nullable', 'exists:status_slas,id'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'problem_description' => ['required', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $ticketDate = !empty($data['created_at']) ? \Illuminate\Support\Carbon::parse($data['created_at']) : now();
        $ticketType = !empty($data['ticket_type_id']) ? TicketType::find($data['ticket_type_id']) : null;
        $requestCode = strtoupper($ticketType?->code ?: 'REQ');
        $prefix = $requestCode . '-' . $ticketDate->format('ymd') . '-';
        
        $latestTicket = Ticket::where('request_id', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(request_id, "-", -1) AS UNSIGNED) DESC')
            ->first();
            
        $nextSeq = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->request_id);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        $dailySequence = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);

        $data['request_id'] = $requestCode . '-' . $ticketDate->format('ymd') . '-' . $dailySequence;
        $data['request_date'] = $ticketDate->toDateString();
        $data['request_time'] = $ticketDate;
        $data['created_at'] = $ticketDate;

        $requester = User::find($data['requester_user_id']);
        if ($requester) {
            $data['requester_name'] = $requester->name;
            $data['requester_email'] = $requester->email;
            $data['requester_company_id'] = $requester->company_id;
            $data['requester_department_id'] = $requester->department_id;
        }

        $data['created_by_id'] = auth()->id();

        if (empty($data['sla_status_id'])) {
            $fulfilledSla = \App\Models\SlaStatus::where('code', 'FULFILLED')->orWhere('name', 'Fulfilled')->first();
            if ($fulfilledSla) {
                $data['sla_status_id'] = $fulfilledSla->id;
            }
        }

        $ticket = Ticket::create($data);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments/tickets', 'public');
            $ticket->attachments()->create([
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => 'Created Ticket',
            'description' => 'Ticket #' . $ticket->request_id . ' created.',
        ]);

        if ($ticket->assignedTo()->exists()) {
            $ticket->assignedTo->notify(new SystemNotification(
                'New Ticket Assigned',
                'Ticket #' . $ticket->request_id . ' has been assigned to you.',
                route('tickets.show', $ticket),
                'ticket',
                'ITSM'
            ));
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load([
            'ticketType', 'requesterUser', 'requesterCompany', 'requesterDepartment',
            'ticketCategory', 'ticketSubcategory', 'priority', 'label', 'source', 'assignedTo', 'createdBy',
            'status', 'slaStatus', 'asset.product.brand', 'asset.assignedToUser', 'asset.currentCheckout.checkedOutToUser', 'asset.department', 'asset.status',
            'attachments', 'comments.user', 'comments.attachments', 'histories.user'
        ]);

        $projects = \App\Models\Project::latest()->get();

        return view('pages.tickets.show', compact('ticket', 'projects'));
    }

    public function edit(Ticket $ticket): View
    {
        $users = User::all();
        $ticketTypes = TicketType::all();
        $ticketCategories = TicketCategory::where('is_active', true)->with('subcategories')->get();
        $priorities = Priority::all();
        $labels = Label::all();
        $sources = Source::all();
        $ticketStatuses = TicketStatus::all();
        $slaStatuses = SlaStatus::all();
        $assets = Asset::with([
            'product.brand',
            'assignedToUser',
            'currentCheckout.checkedOutToUser',
            'department',
            'status'
        ])->latest('id')->get();

        return view('pages.tickets.edit', compact(
            'ticket',
            'users',
            'ticketTypes',
            'ticketCategories',
            'priorities',
            'labels',
            'sources',
            'ticketStatuses',
            'slaStatuses',
            'assets'
        ));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $oldStatusId = $ticket->status_id;
        $data = $request->validate([
            'created_at' => ['nullable', 'date'],
            'ticket_type_id' => ['nullable', 'exists:ticket_types,id'],
            'requester_user_id' => ['required', 'exists:users,id'],
            'ticket_category_id' => ['nullable', 'exists:ticket_categories,id'],
            'ticket_subcategory_id' => ['nullable', 'exists:ticket_subcategories,id'],
            'priority_id' => ['nullable', 'exists:priorities,id'],
            'label_id' => ['nullable', 'exists:labels,id'],
            'source_id' => ['nullable', 'exists:sources,id'],
            'status_id' => ['nullable', 'exists:ticket_statuses,id'],
            'sla_status_id' => ['nullable', 'exists:status_slas,id'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'problem_description' => ['required', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
            'resolution_time_hours' => ['nullable', 'numeric'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        if (!empty($data['created_at'])) {
            $ticketDate = \Illuminate\Support\Carbon::parse($data['created_at']);
            $data['request_date'] = $ticketDate->toDateString();
            $data['request_time'] = $ticketDate;
            $data['created_at'] = $ticketDate;
        }

        $requester = User::find($data['requester_user_id']);
        if ($requester) {
            $data['requester_name'] = $requester->name;
            $data['requester_email'] = $requester->email;
            $data['requester_company_id'] = $requester->company_id;
            $data['requester_department_id'] = $requester->department_id;
        }

        // Auto SLA resolution calculation on closing
        if (isset($data['status_id'])) {
            $statusObj = TicketStatus::find($data['status_id']);
            if ($statusObj && $statusObj->is_closed) {
                if (!$ticket->completed_date) {
                    $data['completed_date'] = now()->toDateString();
                    $data['completed_time'] = now();
                    if (! isset($data['resolution_time_hours']) || ! $data['resolution_time_hours']) {
                        $hours = $ticket->created_at ? round($ticket->created_at->diffInMinutes(now()) / 60, 2) : 0;
                        $data['resolution_time_hours'] = $hours;
                    }
                }
                if (empty($data['sla_status_id'])) {
                    $fulfilledSla = \App\Models\SlaStatus::where('code', 'FULFILLED')->orWhere('name', 'Fulfilled')->first();
                    if ($fulfilledSla) {
                        $data['sla_status_id'] = $fulfilledSla->id;
                    }
                }
            }
        }

        $ticket->update($data);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments/tickets', 'public');
            $ticket->attachments()->create([
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => 'Updated Ticket',
            'description' => 'Ticket #' . $ticket->request_id . ' updated.',
        ]);

        if (isset($data['status_id']) && $data['status_id'] !== $oldStatusId && $ticket->requesterUser) {
            $newStatus = TicketStatus::find($data['status_id']);
            $ticket->requesterUser->notify(new SystemNotification(
                'Ticket Status Updated',
                'Ticket #' . $ticket->request_id . ' status is now ' . ($newStatus?->name ?? 'updated') . '.',
                route('tickets.show', $ticket),
                'info',
                'ITSM'
            ));
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully.');
    }

    public function comment(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'is_internal' => 'nullable|boolean',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $validated['comment'],
            'is_internal' => $request->boolean('is_internal'),
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments/comments', 'public');
            $comment->attachments()->create([
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        // Compute First Response Time Metrics
        if (is_null($ticket->response_time_minutes) && auth()->id() !== $ticket->requester_user_id) {
            $responseMins = (int) round($ticket->created_at->diffInMinutes(now()));
            $ticket->update(['response_time_minutes' => $responseMins]);
        }

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => 'Comment Added',
            'description' => ($request->boolean('is_internal') ? 'Internal note' : 'Public comment') . ' added.',
        ]);

        // Notify relevant user
        if (! $request->boolean('is_internal')) {
            $recipient = auth()->id() === $ticket->requester_user_id ? $ticket->assignedTo : $ticket->requesterUser;
            if ($recipient) {
                $recipient->notify(new SystemNotification(
                    'New Ticket Comment',
                    'New comment on Ticket #' . $ticket->request_id,
                    route('tickets.show', $ticket),
                    'message-square',
                    'ITSM'
                ));
            }
        }

        return redirect()->back()->with('success', 'Comment added.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tickets,id'],
        ]);

        Ticket::whereIn('id', $data['ids'])->delete();

        return redirect()->route('tickets.index')->with('success', 'Selected tickets deleted successfully.');
    }
}
