<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Label;
use App\Models\Priority;
use App\Models\SlaStatus;
use App\Models\Source;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ticket::with(['category', 'priority', 'status', 'slaStatus', 'assignedTo', 'requesterUser']);

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

        $tickets = $query->latest()->paginate(10)->withQueryString();
        $statuses = TicketStatus::all();
        $priorities = Priority::all();

        return view('tickets.index', compact('tickets', 'statuses', 'priorities', 'search'));
    }

    public function create(): View
    {
        $users = User::all();
        $ticketTypes = TicketType::all();
        $categories = Category::all();
        $priorities = Priority::all();
        $labels = Label::all();
        $sources = Source::all();
        $ticketStatuses = TicketStatus::all();
        $slaStatuses = SlaStatus::all();
        $assets = Asset::with('assignedToUser')->get();

        return view('tickets.form', compact(
            'users',
            'ticketTypes',
            'categories',
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
            'category_id' => ['nullable', 'exists:categories,id'],
            'priority_id' => ['nullable', 'exists:priorities,id'],
            'label_id' => ['nullable', 'exists:labels,id'],
            'source_id' => ['nullable', 'exists:sources,id'],
            'status_id' => ['nullable', 'exists:ticket_statuses,id'],
            'sla_status_id' => ['nullable', 'exists:status_slas,id'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'problem_description' => ['required', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $requester = User::find($data['requester_user_id']);
        if ($requester) {
            $data['requester_name'] = $requester->name;
            $data['requester_email'] = $requester->email;
            $data['requester_company_id'] = $requester->company_id;
            $data['requester_department_id'] = $requester->department_id;
        }

        $data['created_by_id'] = auth()->id();

        Ticket::create($data);

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['ticketType', 'requesterUser', 'requesterCompany', 'requesterDepartment', 'category', 'priority', 'label', 'source', 'assignedTo', 'createdBy', 'status', 'slaStatus', 'asset']);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket): View
    {
        $users = User::all();
        $ticketTypes = TicketType::all();
        $categories = Category::all();
        $priorities = Priority::all();
        $labels = Label::all();
        $sources = Source::all();
        $ticketStatuses = TicketStatus::all();
        $slaStatuses = SlaStatus::all();
        $assets = Asset::with('assignedToUser')->get();

        return view('tickets.form', compact(
            'ticket',
            'users',
            'ticketTypes',
            'categories',
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
            'category_id' => ['nullable', 'exists:categories,id'],
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
        ]);

        $requester = User::find($data['requester_user_id']);
        if ($requester) {
            $data['requester_name'] = $requester->name;
            $data['requester_email'] = $requester->email;
            $data['requester_company_id'] = $requester->company_id;
            $data['requester_department_id'] = $requester->department_id;
        }

        $ticket->update($data);

        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }
}
