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
use App\Notifications\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ticket::with(['ticketCategory', 'ticketSubcategory', 'priority', 'status', 'slaStatus', 'assignedTo', 'requesterUser']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('request_id', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('problem_description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status_id')) {
            $query->where('status_id', $status);
        }

        if ($priority = $request->input('priority_id')) {
            $query->where('priority_id', $priority);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $statuses = TicketStatus::all();
        $priorities = Priority::all();

        return view('pages.tickets.index', compact('tickets', 'statuses', 'priorities', 'search'));
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
        $assets = Asset::with('assignedToUser')->get();

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

        $now = now();
        $ticketType = !empty($data['ticket_type_id']) ? TicketType::find($data['ticket_type_id']) : null;
        $requestCode = strtoupper($ticketType?->code ?: 'REQ');
        $dailySequence = str_pad(
            (string) (Ticket::whereDate('created_at', $now->toDateString())->count() + 1),
            3,
            '0',
            STR_PAD_LEFT
        );

        $data['request_id'] = $requestCode . '-' . $now->format('ymd') . '-' . $dailySequence;
        $data['request_date'] = $now->toDateString();
        $data['request_time'] = $now;

        $requester = User::find($data['requester_user_id']);
        if ($requester) {
            $data['requester_name'] = $requester->name;
            $data['requester_email'] = $requester->email;
            $data['requester_company_id'] = $requester->company_id;
            $data['requester_department_id'] = $requester->department_id;
        }

        $data['created_by_id'] = auth()->id();

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
            'status', 'slaStatus', 'asset', 'attachments', 'comments.user', 'comments.attachments', 'histories.user'
        ]);

        return view('pages.tickets.show', compact('ticket'));
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
        $assets = Asset::with('assignedToUser')->get();

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
        $data = $request->validate([
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
            if ($statusObj && $statusObj->is_closed && ! $ticket->completed_date) {
                $data['completed_date'] = now()->toDateString();
                $data['completed_time'] = now();
                if (! isset($data['resolution_time_hours']) || ! $data['resolution_time_hours']) {
                    $hours = $ticket->created_at ? round($ticket->created_at->diffInMinutes(now()) / 60, 2) : 0;
                    $data['resolution_time_hours'] = $hours;
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
