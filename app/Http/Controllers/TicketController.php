<?php

namespace App\Http\Controllers;

use App\Exports\TicketsExport;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Department;
use App\Models\Label;
use App\Models\Priority;
use App\Models\Project;
use App\Models\SlaPolicy;
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
use Illuminate\Support\Str;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function export(Request $request)
    {
        return (new TicketsExport($request->all()))->download('tickets-'.now()->format('Y-m-d').'.xlsx');
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $isStaff = $user?->hasAnyRole(['it_admin', 'super_admin']);

        $query = Ticket::with(['ticketCategory', 'priority', 'status', 'slaStatus', 'assignedTo.department', 'requesterUser.department', 'requesterDepartment']);

        if (! $isStaff && $user) {
            $query->where(function ($q) use ($user) {
                $q->where('requester_user_id', $user->id)
                    ->orWhere('created_by_id', $user->id);
            });
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('request_id', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
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

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'request_id' || $sortBy === 'number') {
            $query->orderBy('request_id', $sortDir);
        } elseif ($sortBy === 'requester_name' || $sortBy === 'requester') {
            $query->orderBy('requester_name', $sortDir);
        } elseif ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortDir);
        } else {
            $query->latest();
        }

        $tickets = $query->paginate($perPage)->withQueryString();
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
        $isStaff = auth()->user()?->hasAnyRole(['it_admin', 'super_admin']);

        $data = $request->validate([
            'created_at' => ['nullable', 'date'],
            'subject' => ['nullable', 'string', 'max:255'],
            'ticket_type_id' => ['nullable', 'exists:ticket_types,id'],
            'requester_user_id' => [$isStaff ? 'required' : 'nullable', 'exists:users,id'],
            'ticket_category_id' => ['nullable', 'exists:ticket_categories,id'],
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

        // Security & workflow enforcement for non-staff users (requesters)
        if (! $isStaff) {
            $data['requester_user_id'] = auth()->id();
            $data['created_at'] = now();
            $data['assigned_to_id'] = null;
            $data['internal_notes'] = null;
            $data['completed_date'] = null;
            $data['completed_time'] = null;
            $data['actual_hours'] = 0;
            $data['estimated_hours'] = 0;

            // Default status to New
            $newStatus = TicketStatus::where('name', 'New')->first();
            $data['status_id'] = $newStatus?->id;

            // Default portal source
            $portalSource = Source::where('code', 'PORTAL')->orWhere('name', 'Portal')->first();
            $data['source_id'] = $portalSource?->id ?? $data['source_id'] ?? null;

            // Default Incident type
            $incidentType = TicketType::where('code', 'INCIDENT')->orWhere('name', 'Incident')->first();
            $data['ticket_type_id'] = $incidentType?->id ?? $data['ticket_type_id'] ?? null;
        }

        if (empty($data['subject'])) {
            $firstLine = trim(strtok((string) $data['problem_description'], "\r\n") ?: (string) $data['problem_description']);
            $data['subject'] = Str::limit($firstLine, 120);
        }

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
        $data['estimated_hours'] = $data['estimated_hours'] ?? 0;
        $data['actual_hours'] = $data['actual_hours'] ?? 0;
        $ticketType = ! empty($data['ticket_type_id']) ? TicketType::find($data['ticket_type_id']) : null;
        $requestCode = strtoupper($ticketType?->code ?: 'REQ');
        $prefix = $requestCode.'-'.$ticketDate->format('ymd').'-';

        // Race-safe daily sequence: find a free slot even under concurrent creation
        $nextSeq = 1;
        do {
            $candidate = $prefix.str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);
            $exists = Ticket::where('request_id', $candidate)->exists();
            if (! $exists) {
                break;
            }
            $nextSeq++;
        } while (true);

        $dailySequence = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);

        $data['request_id'] = $candidate;
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
            // Auto compute resolution_time_hours if not specified
            if (empty($data['resolution_time_hours'])) {
                $completedAt = Carbon::parse($data['completed_date'].' '.Carbon::parse($data['completed_time'])->format('H:i:s'));
                $data['resolution_time_hours'] = max(0.1, round($ticketDate->diffInMinutes($completedAt) / 60, 2));
            }
            // SLA is fulfilled only for tickets that are already completed on creation
            if (empty($data['sla_status_id'])) {
                $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->orWhere('name', 'Fulfilled')->first();
                if ($fulfilledSla) {
                    $data['sla_status_id'] = $fulfilledSla->id;
                }
            }
        }

        // Compute SLA due date from the SLA policy of the selected priority
        $slaDueAt = $this->computeSlaDueAt($ticketDate, $data['priority_id'] ?? null, $data['status_id'] ?? null);
        if ($slaDueAt) {
            $data['sla_due_at'] = $slaDueAt;
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
        $user = auth()->user();
        $isStaff = $user?->hasAnyRole(['it_admin', 'super_admin']);
        if (! $isStaff && $user && $ticket->requester_user_id !== $user->id && $ticket->created_by_id !== $user->id) {
            abort(403, 'Unauthorized. You can only view your own tickets.');
        }

        $ticket->load([
            'ticketType', 'requesterUser', 'requesterCompany', 'requesterDepartment',
            'ticketCategory', 'priority', 'label', 'source', 'assignedTo', 'createdBy',
            'status', 'slaStatus', 'asset.product.brand', 'asset.assignedToUser', 'asset.currentCheckout.checkedOutToUser', 'asset.department', 'asset.status',
            'attachments', 'comments.user', 'comments.attachments', 'histories.user',
        ]);

        $projects = Project::latest()->get();
        $statuses = TicketStatus::orderBy('name')->get();

        return view('pages.tickets.show', compact('ticket', 'projects', 'statuses'));
    }

    /**
     * Quick status update from the ticket detail page (distinct from full edit).
     */
    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['it_admin', 'super_admin'])) {
            return back()->with('error', 'Unauthorized. Only IT Staff can update ticket status.');
        }

        $data = $request->validate([
            'status_id' => ['required', 'exists:ticket_statuses,id'],
            'resolution_notes' => ['nullable', 'string'],
            'completed_date' => ['nullable', 'date'],
            'completed_time' => ['nullable'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        $oldStatus = $ticket->status;
        $status = TicketStatus::find($data['status_id']);
        $oldStatusId = $ticket->status_id;

        // Auto first-response tracking if moved to in-progress or resolved by staff
        if (is_null($ticket->first_responded_at) && auth()->id() !== $ticket->requester_user_id) {
            $inProgressOrClosed = $status && ($status->is_closed || in_array(strtolower($status->name), ['in progress', 'in-progress']));
            if ($inProgressOrClosed) {
                $now = now();
                $data['first_responded_at'] = $now;
                $data['response_time_minutes'] = (int) round($ticket->created_at->diffInMinutes($now));
            }
        }

        // Auto-complete bookkeeping when moving to a closed status
        if ($status && $status->is_closed) {
            if (empty($data['completed_date'])) {
                $data['completed_date'] = now()->toDateString();
            }
            if (empty($data['completed_time'])) {
                $data['completed_time'] = now();
            }
            if (empty($ticket->resolution_time_hours)) {
                $createdAt = $ticket->created_at ?? now();
                $compDate = $data['completed_date'] ?? now()->toDateString();
                $compTime = $data['completed_time'] ?? now();
                $completedAt = Carbon::parse($compDate.' '.Carbon::parse($compTime)->format('H:i:s'));
                $data['resolution_time_hours'] = max(0.1, round($createdAt->diffInMinutes($completedAt) / 60, 2));
            }
            if (empty($ticket->sla_status_id)) {
                $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->orWhere('name', 'Fulfilled')->first();
                if ($fulfilledSla) {
                    $data['sla_status_id'] = $fulfilledSla->id;
                }
            }
        } else {
            // Moving between open statuses clears completion data
            $data['completed_date'] = null;
            $data['completed_time'] = null;
        }

        if (array_key_exists('actual_hours', $data) && is_null($data['actual_hours'])) {
            $data['actual_hours'] = $ticket->actual_hours ?? 0;
        }

        $ticket->fill($data);
        $ticket->save();

        if ($status && (string) $oldStatusId !== (string) $status->id) {
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => 'Status Updated',
                'description' => 'Status changed from '.($oldStatus?->name ?? 'None').' to '.$status->name.'.',
            ]);

            if ($ticket->requesterUser) {
                $ticket->requesterUser->notify(new SystemNotification(
                    'Ticket Status Updated',
                    'Ticket #'.$ticket->request_id.' status is now '.$status->name.'.',
                    route('tickets.show', $ticket),
                    'info',
                    'ITSM'
                ));
            }
        }

        return back()->with('success', 'Ticket status updated to '.$status?->name.'.');
    }

    public function edit(Ticket $ticket): View
    {
        $user = auth()->user();
        $isStaff = $user?->hasAnyRole(['it_admin', 'super_admin']);
        if (! $isStaff && ($ticket->requester_user_id !== $user->id || $ticket->status?->is_closed)) {
            abort(403, 'Unauthorized. Only IT Staff can edit this ticket.');
        }

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
        $user = auth()->user();
        $isStaff = $user?->hasAnyRole(['it_admin', 'super_admin']);
        if (! $isStaff && ($ticket->requester_user_id !== $user->id || $ticket->status?->is_closed)) {
            abort(403, 'Unauthorized.');
        }

        $oldStatusId = $ticket->status_id;
        $oldAssignedToId = $ticket->assigned_to_id;
        $data = $request->validate([
            'created_at' => ['nullable', 'date'],
            'subject' => ['nullable', 'string', 'max:255'],
            'ticket_type_id' => ['nullable', 'exists:ticket_types,id'],
            'requester_user_id' => [$isStaff ? 'required' : 'nullable', 'exists:users,id'],
            'ticket_category_id' => ['nullable', 'exists:ticket_categories,id'],
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

        if (! $isStaff) {
            $data['requester_user_id'] = $ticket->requester_user_id;
            $data['assigned_to_id'] = $ticket->assigned_to_id;
            $data['status_id'] = $ticket->status_id;
            $data['internal_notes'] = $ticket->internal_notes;
            $data['resolution_notes'] = $ticket->resolution_notes;
            $data['completed_date'] = $ticket->completed_date;
            $data['completed_time'] = $ticket->completed_time;
            $data['created_at'] = $ticket->created_at;
            $data['actual_hours'] = $ticket->actual_hours;
            $data['estimated_hours'] = $ticket->estimated_hours;
        }

        if (empty($data['subject'])) {
            $firstLine = trim(strtok((string) $data['problem_description'], "\r\n") ?: (string) $data['problem_description']);
            $data['subject'] = Str::limit($firstLine, 120);
        }

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

        // Recompute SLA due date when the ticket date or priority changed and the ticket is not closed
        $statusIdForSla = isset($data['status_id']) ? (int) $data['status_id'] : $ticket->status_id;
        $statusForSla = $statusIdForSla ? TicketStatus::find($statusIdForSla) : null;
        if ($statusForSla && ! $statusForSla->is_closed) {
            $baseDate = isset($data['created_at']) ? Carbon::parse($data['created_at']) : $ticket->created_at;
            $priorityIdForSla = isset($data['priority_id']) ? (int) $data['priority_id'] : $ticket->priority_id;
            $slaDueAt = $this->computeSlaDueAt($baseDate ? Carbon::parse($baseDate) : null, $priorityIdForSla, $statusIdForSla);
            if ($slaDueAt) {
                $data['sla_due_at'] = $slaDueAt;
            }
        } elseif ($statusForSla && $statusForSla->is_closed) {
            $data['sla_due_at'] = null;
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
                    $compDate = $data['completed_date'] ?? now()->toDateString();
                    $compTime = $data['completed_time'] ?? now();
                    $completedAt = Carbon::parse($compDate.' '.Carbon::parse($compTime)->format('H:i:s'));
                    $hours = $createdAt ? max(0.1, round($createdAt->diffInMinutes($completedAt) / 60, 2)) : 0;
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

        if (array_key_exists('actual_hours', $data) && is_null($data['actual_hours'])) {
            $data['actual_hours'] = $ticket->actual_hours ?? 0;
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

        if (isset($data['status_id']) && (string) $data['status_id'] !== (string) $oldStatusId && $ticket->requesterUser) {
            $newStatus = TicketStatus::find($data['status_id']);
            $ticket->requesterUser->notify(new SystemNotification(
                'Ticket Status Updated',
                'Ticket #'.$ticket->request_id.' status is now '.($newStatus?->name ?? 'updated').'.',
                route('tickets.show', $ticket),
                'info',
                'ITSM'
            ));
        }

        // Notify the new assignee when the assignment changed to a different user
        if (isset($data['assigned_to_id'])
            && $data['assigned_to_id']
            && (string) $data['assigned_to_id'] !== (string) $oldAssignedToId
            && ($newAssignee = User::find($data['assigned_to_id']))) {
            $newAssignee->notify(new SystemNotification(
                'Ticket Assigned to You',
                'Ticket #'.$ticket->request_id.' has been assigned to you.',
                route('tickets.show', $ticket),
                'ticket',
                'ITSM'
            ));
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully.');
    }

    public function comment(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();
        $isStaff = $user?->hasAnyRole(['it_admin', 'super_admin']);
        if (! $isStaff && $user && $ticket->requester_user_id !== $user->id && $ticket->created_by_id !== $user->id) {
            abort(403, 'Unauthorized to comment on this ticket.');
        }

        $validated = $request->validate([
            'comment' => 'required|string',
            'is_internal' => 'nullable|boolean',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $validated['comment'],
            'is_internal' => $isStaff && $request->boolean('is_internal'),
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

        // Compute First Response Time Metrics (public replies only, excluding the requester)
        if (is_null($ticket->response_time_minutes)
            && ! $request->boolean('is_internal')
            && auth()->id() !== $ticket->requester_user_id) {
            $now = now();
            $responseMins = (int) round($ticket->created_at->diffInMinutes($now));
            $ticket->update([
                'first_responded_at' => $now,
                'response_time_minutes' => $responseMins,
            ]);
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

    /**
     * Take / claim ticket to current logged-in staff user.
     */
    public function takeTicket(Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['it_admin', 'super_admin'])) {
            return back()->with('error', 'Unauthorized. Only IT Staff can take or be assigned tickets.');
        }

        $inProgressStatus = TicketStatus::where('name', 'In Progress')->first();
        $updates = [
            'assigned_to_id' => $user->id,
        ];

        // Move to In Progress if currently New
        if ($ticket->status && in_array(strtolower($ticket->status->name), ['new', 'open']) && $inProgressStatus) {
            $updates['status_id'] = $inProgressStatus->id;
        }

        // Record first response if first time taken
        if (is_null($ticket->first_responded_at) && $ticket->requester_user_id !== $user->id) {
            $now = now();
            $updates['first_responded_at'] = $now;
            $updates['response_time_minutes'] = (int) round($ticket->created_at->diffInMinutes($now));
        }

        $ticket->update($updates);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'action' => 'Ticket Claimed',
            'description' => "Ticket claimed by {$user->name}.",
        ]);

        return back()->with('success', 'Ticket claimed successfully.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        // Enforce role authorization: only super_admin or it_admin can delete tickets
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'it_admin'])) {
            return back()->with('error', 'Unauthorized. Only IT Administrators can delete support tickets.');
        }

        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        // Enforce role authorization: only super_admin or it_admin can bulk delete tickets
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'it_admin'])) {
            return back()->with('error', 'Unauthorized. Only IT Administrators can delete support tickets.');
        }

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tickets,id'],
        ]);

        Ticket::whereIn('id', $data['ids'])->delete();

        return redirect()->route('tickets.index')->with('success', 'Selected tickets deleted successfully.');
    }

    /**
     * Compute the SLA due timestamp from the SLA policy of the ticket priority.
     * Returns null for already-closed tickets or when no base date exists.
     */
    private function computeSlaDueAt(?Carbon $ticketDate, ?int $priorityId, ?int $statusId): ?Carbon
    {
        if (! $ticketDate) {
            return null;
        }

        $status = $statusId ? TicketStatus::find($statusId) : null;
        if ($status && $status->is_closed) {
            return null;
        }

        $policy = $priorityId ? SlaPolicy::where('priority_id', $priorityId)->first() : null;
        $hours = (int) ($policy?->resolution_target_hours ?? 72);

        return $ticketDate->copy()->addHours($hours);
    }
}
