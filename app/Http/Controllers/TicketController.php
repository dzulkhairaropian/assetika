<?php

namespace App\Http\Controllers;

use App\Exports\TicketsExport;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Department;
use App\Models\Label;
use App\Models\Priority;
use App\Models\Project;
use App\Models\SlaStatus;
use App\Models\Source;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\AssetTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function export(Request $request)
    {
        return (new TicketsExport($request->all()))->download('tickets-'.now()->format('Y-m-d').'.xlsx');
    }

    public function index(Request $request): View
    {
        $query = Ticket::with(['ticketCategory', 'priority', 'status', 'slaStatus', 'assignedTo.department', 'requesterUser.department', 'requesterDepartment']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('request_id', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('problem_description', 'like', "%{$search}%");
            });
        }

        $dateType = $request->input('date_type', 'created_at');
        if ($startDate = ($request->input('start_date') ?: $request->input('from_date') ?: $request->input('created_from'))) {
            if ($dateType === 'request_date') {
                $query->whereDate('request_date', '>=', $startDate);
            } elseif ($dateType === 'both') {
                $query->where(function ($q) use ($startDate) {
                    $q->whereDate('tickets.created_at', '>=', $startDate)
                        ->orWhereDate('request_date', '>=', $startDate);
                });
            } else {
                $query->whereDate('tickets.created_at', '>=', $startDate);
            }
        }

        if ($endDate = ($request->input('end_date') ?: $request->input('to_date') ?: $request->input('created_to'))) {
            if ($dateType === 'request_date') {
                $query->whereDate('request_date', '<=', $endDate);
            } elseif ($dateType === 'both') {
                $query->where(function ($q) use ($endDate) {
                    $q->whereDate('tickets.created_at', '<=', $endDate)
                        ->orWhereDate('request_date', '<=', $endDate);
                });
            } else {
                $query->whereDate('tickets.created_at', '<=', $endDate);
            }
        }

        if ($status = $request->input('status_id')) {
            $query->where('status_id', $status);
        }

        if ($priority = $request->input('priority_id')) {
            $query->where('priority_id', $priority);
        }

        if ($catId = ($request->input('ticket_category_id') ?: $request->input('category_id'))) {
            if ($catId === 'general') {
                $query->whereNull('ticket_category_id');
            } else {
                $query->where('ticket_category_id', $catId);
            }
        }

        if ($companyId = $request->input('company_id')) {
            $query->where('requester_company_id', $companyId);
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('requester_department_id', $deptId);
        }

        $ticketSummary = [
            'total' => (clone $query)->count(),
            'created_today' => (clone $query)->whereDate('tickets.created_at', Carbon::today())->count(),
            'new' => (clone $query)->whereHas('status', fn ($q) => $q->where('name', 'New'))->count(),
            'in_progress' => (clone $query)->whereHas('status', fn ($q) => $q->where('name', 'In Progress'))->count(),
            'pending' => (clone $query)->whereHas('status', fn ($q) => $q->where('name', 'Pending'))->count(),
            'unassigned' => (clone $query)->whereNull('assigned_to_id')->count(),
            'closed' => (clone $query)->whereHas('status', fn ($q) => $q->whereIn('name', ['Resolved', 'Closed'])->orWhere('is_closed', true))->count(),
        ];

        switch ($request->input('tab')) {
            case 'created_today':
            case 'today':
                $query->whereDate('tickets.created_at', Carbon::today());
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
        $users = User::with('department')->orderBy('name')->get();
        $staffUsers = User::role(['it_admin', 'super_admin'])->with('department')->orderBy('name')->get();
        if ($staffUsers->isEmpty()) {
            $staffUsers = $users;
        }
        $ticketTypes = TicketType::all();
        $ticketCategories = TicketCategory::where('is_active', true)->orderBy('name')->get();
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
            'status',
        ])->latest('id')->get();

        $userOpts = $users->map(fn ($u) => ['id' => $u->id, 'text' => $u->name.' ('.($u->department?->name ?? $u->email).')'])->values()->all();
        $staffOpts = $staffUsers->map(fn ($u) => ['id' => $u->id, 'text' => $u->name.' ('.($u->department?->name ?? $u->email).')'])->values()->all();
        $typeOpts = $ticketTypes->map(fn ($t) => ['id' => $t->id, 'text' => $t->name])->values()->all();
        $catOpts = $ticketCategories->map(fn ($c) => ['id' => $c->id, 'text' => $c->name])->values()->all();
        $priorityOpts = $priorities->map(fn ($p) => ['id' => $p->id, 'text' => $p->name])->values()->all();
        $labelOpts = $labels->map(fn ($l) => ['id' => $l->id, 'text' => $l->name])->values()->all();
        $sourceOpts = $sources->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->values()->all();
        $statusOpts = $ticketStatuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->values()->all();
        $assetOpts = $assets->map(fn ($a) => [
            'id' => $a->id,
            'text' => $a->asset_tag.' - '.($a->product?->name ?? $a->device_name).($a->assignedToUser ? ' ('.$a->assignedToUser->name.')' : ''),
        ])->values()->all();

        return view('pages.tickets.create', compact(
            'users',
            'staffUsers',
            'ticketTypes',
            'ticketCategories',
            'priorities',
            'labels',
            'sources',
            'ticketStatuses',
            'slaStatuses',
            'assets',
            'userOpts',
            'staffOpts',
            'typeOpts',
            'catOpts',
            'priorityOpts',
            'labelOpts',
            'sourceOpts',
            'statusOpts',
            'assetOpts'
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
            'completed_date' => ['nullable', 'date'],
            'completed_time' => ['nullable'],
            'internal_notes' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
            'resolution_time_hours' => ['nullable', 'numeric'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        // Auto-map category and subcategory from asset if asset_id is provided
        if (! empty($data['asset_id']) && empty($data['ticket_category_id'])) {
            $assetObj = Asset::with('product.category')->find($data['asset_id']);
            if ($assetObj) {
                $mapped = AssetTicketService::resolveTicketCategoryForAsset($assetObj);
                $data['ticket_category_id'] = $mapped['category_id'];
                $data['ticket_subcategory_id'] = $mapped['subcategory_id'];
            }
        }

        $ticketDate = ! empty($data['created_at']) ? Carbon::parse($data['created_at']) : now();
        $ticketType = ! empty($data['ticket_type_id']) ? TicketType::find($data['ticket_type_id']) : null;
        $requestCode = strtoupper($ticketType?->code ?: 'REQ');
        $prefix = $requestCode.'-'.$ticketDate->format('ymd').'-';

        $latestTicket = Ticket::where('request_id', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->request_id);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        $dailySequence = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);

        $data['request_id'] = $requestCode.'-'.$ticketDate->format('ymd').'-'.$dailySequence;
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

        // If completed_date is provided, auto-set status to Completed
        if (! empty($data['completed_date'])) {
            $currentStatus = ! empty($data['status_id']) ? TicketStatus::find($data['status_id']) : null;
            if (! $currentStatus || ! $currentStatus->is_closed) {
                $completedStatus = TicketStatus::where('name', 'Completed')->first()
                    ?? TicketStatus::where('is_closed', true)->first();
                if ($completedStatus) {
                    $data['status_id'] = $completedStatus->id;
                }
            }
            if (empty($data['completed_time'])) {
                $data['completed_time'] = now();
            }
        }

        if (empty($data['sla_status_id'])) {
            $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->orWhere('name', 'Fulfilled')->first();
            if ($fulfilledSla) {
                $data['sla_status_id'] = $fulfilledSla->id;
            }
        }

        $ticket = new Ticket;
        $ticket->fill($data);
        $ticket->created_at = $ticketDate;
        $ticket->save();

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
            'description' => 'Ticket #'.$ticket->request_id.' created.',
        ]);

        if ($ticket->assignedTo()->exists()) {
            $ticket->assignedTo->notify(new SystemNotification(
                'New Ticket Assigned',
                'Ticket #'.$ticket->request_id.' has been assigned to you.',
                route('tickets.show', $ticket),
                'ticket',
                'ITSM'
            ));
        }

        if ($request->has('create_another')) {
            return redirect()->route('tickets.create')->with('success', 'Ticket created successfully.');
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load([
            'ticketType', 'requesterUser', 'requesterCompany', 'requesterDepartment',
            'ticketCategory', 'priority', 'label', 'source', 'assignedTo', 'createdBy',
            'status', 'slaStatus', 'asset.product.brand', 'asset.assignedToUser', 'asset.currentCheckout.checkedOutToUser', 'asset.department', 'asset.status',
            'attachments', 'comments.user', 'comments.attachments', 'histories.user',
        ]);

        $projects = Project::latest()->get();

        return view('pages.tickets.show', compact('ticket', 'projects'));
    }

    public function edit(Ticket $ticket): View
    {
        $users = User::with('department')->orderBy('name')->get();
        $staffUsers = User::role(['it_admin', 'super_admin'])->with('department')->orderBy('name')->get();
        if ($staffUsers->isEmpty()) {
            $staffUsers = $users;
        }
        $ticketTypes = TicketType::all();
        $ticketCategories = TicketCategory::where('is_active', true)->orderBy('name')->get();
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
            'status',
        ])->latest('id')->get();

        $userOpts = $users->map(fn ($u) => ['id' => $u->id, 'text' => $u->name.' ('.($u->department?->name ?? $u->email).')'])->values()->all();
        $staffOpts = $staffUsers->map(fn ($u) => ['id' => $u->id, 'text' => $u->name.' ('.($u->department?->name ?? $u->email).')'])->values()->all();
        $typeOpts = $ticketTypes->map(fn ($t) => ['id' => $t->id, 'text' => $t->name])->values()->all();
        $catOpts = $ticketCategories->map(fn ($c) => ['id' => $c->id, 'text' => $c->name])->values()->all();
        $priorityOpts = $priorities->map(fn ($p) => ['id' => $p->id, 'text' => $p->name])->values()->all();
        $labelOpts = $labels->map(fn ($l) => ['id' => $l->id, 'text' => $l->name])->values()->all();
        $sourceOpts = $sources->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->values()->all();
        $statusOpts = $ticketStatuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->values()->all();
        $assetOpts = $assets->map(fn ($a) => [
            'id' => $a->id,
            'text' => $a->asset_tag.' - '.($a->product?->name ?? $a->device_name).($a->assignedToUser ? ' ('.$a->assignedToUser->name.')' : ''),
        ])->values()->all();

        return view('pages.tickets.edit', compact(
            'ticket',
            'users',
            'staffUsers',
            'ticketTypes',
            'ticketCategories',
            'priorities',
            'labels',
            'sources',
            'ticketStatuses',
            'slaStatuses',
            'assets',
            'userOpts',
            'staffOpts',
            'typeOpts',
            'catOpts',
            'priorityOpts',
            'labelOpts',
            'sourceOpts',
            'statusOpts',
            'assetOpts'
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
            'completed_date' => ['nullable', 'date'],
            'completed_time' => ['nullable'],
            'internal_notes' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
            'resolution_time_hours' => ['nullable', 'numeric'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        if (! empty($data['created_at'])) {
            $ticketDate = Carbon::parse($data['created_at']);
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

        // If completed_date is provided and status is not explicitly set to a closed status, auto-set status to Completed
        if (! empty($data['completed_date'])) {
            $currentStatus = ! empty($data['status_id']) ? TicketStatus::find($data['status_id']) : null;
            if (! $currentStatus || ! $currentStatus->is_closed) {
                $completedStatus = TicketStatus::where('name', 'Completed')->first()
                    ?? TicketStatus::where('is_closed', true)->first();
                if ($completedStatus) {
                    $data['status_id'] = $completedStatus->id;
                }
            }
            if (empty($data['completed_time'])) {
                $data['completed_time'] = now();
            }
        }

        // Auto SLA resolution calculation on closing
        if (isset($data['status_id'])) {
            $statusObj = TicketStatus::find($data['status_id']);
            if ($statusObj && $statusObj->is_closed) {
                if (empty($data['completed_date'])) {
                    $data['completed_date'] = now()->toDateString();
                }
                if (empty($data['completed_time'])) {
                    $data['completed_time'] = now();
                }
                if (! isset($data['resolution_time_hours']) || ! $data['resolution_time_hours']) {
                    $createdAt = isset($data['created_at']) ? Carbon::parse($data['created_at']) : ($ticket->created_at ?? now());
                    $hours = $createdAt ? round($createdAt->diffInMinutes(now()) / 60, 2) : 0;
                    $data['resolution_time_hours'] = $hours;
                }
                if (empty($data['sla_status_id'])) {
                    $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->orWhere('name', 'Fulfilled')->first();
                    if ($fulfilledSla) {
                        $data['sla_status_id'] = $fulfilledSla->id;
                    }
                }
            }
        }

        $ticket->fill($data);
        if (! empty($data['created_at'])) {
            $ticket->created_at = Carbon::parse($data['created_at']);
        }
        $ticket->save();

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
            'description' => 'Ticket #'.$ticket->request_id.' updated.',
        ]);

        if (isset($data['status_id']) && $data['status_id'] !== $oldStatusId && $ticket->requesterUser) {
            $newStatus = TicketStatus::find($data['status_id']);
            $ticket->requesterUser->notify(new SystemNotification(
                'Ticket Status Updated',
                'Ticket #'.$ticket->request_id.' status is now '.($newStatus?->name ?? 'updated').'.',
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
            'description' => ($request->boolean('is_internal') ? 'Internal note' : 'Public comment').' added.',
        ]);

        // Notify relevant user
        if (! $request->boolean('is_internal')) {
            $recipient = auth()->id() === $ticket->requester_user_id ? $ticket->assignedTo : $ticket->requesterUser;
            if ($recipient) {
                $recipient->notify(new SystemNotification(
                    'New Ticket Comment',
                    'New comment on Ticket #'.$ticket->request_id,
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
