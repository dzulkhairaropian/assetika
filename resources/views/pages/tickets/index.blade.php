@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedTickets: [],
    selectAll: false,
    toggleAll() {
        this.selectAll = !this.selectAll;
        if (this.selectAll) {
            this.selectedTickets = Array.from(document.querySelectorAll('.ticket-checkbox')).map(el => Number(el.value));
        } else {
            this.selectedTickets = [];
        }
    },
    toggleTicket(ticketId) {
        if (this.selectedTickets.includes(ticketId)) {
            this.selectedTickets = this.selectedTickets.filter(id => id !== ticketId);
        } else {
            this.selectedTickets.push(ticketId);
        }
        this.selectAll = this.selectedTickets.length === document.querySelectorAll('.ticket-checkbox').length;
    }
}">
    <x-common.page-breadcrumb pageTitle="Support Tickets" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
            <div class="relative flex items-center">
                <span class="absolute left-3 text-gray-400">
                    <i data-feather="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, requester, issue..."
                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>

            @php
                $activeCount = 0;
                if (request('status_id')) $activeCount++;
                if (request('priority_id')) $activeCount++;
            @endphp

            <div class="relative" @click.away="filterOpen = false">
                <button type="button" @click="filterOpen = !filterOpen"
                    class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 transition">
                    <svg class="stroke-current fill-white dark:fill-gray-900" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5" />
                        <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5" />
                    </svg>
                    Filters
                    @if($activeCount > 0)
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                    @endif
                </button>

                <div x-show="filterOpen"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute left-0 top-full z-50 mt-2 w-[520px] rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                    style="display: none;">
                    <div class="mb-4 flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Filter Options</h4>
                        <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                            <select name="status_id"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Priority</label>
                            <select name="priority_id"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Priorities</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
                        <a href="{{ route('tickets.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">
                            Clear All Filters
                        </a>
                        <button type="submit" @click="filterOpen = false"
                            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="flex items-center gap-3">
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-feather="plus" class="w-4 h-4"></i>
                Create Ticket
            </a>
        </div>
    </div>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    <form id="bulkDeleteTicketsForm" action="{{ route('tickets.bulk-delete') }}" method="POST" class="hidden">
        @csrf
        <template x-for="ticketId in selectedTickets" :key="ticketId">
            <input type="hidden" name="ids[]" :value="ticketId">
        </template>
    </form>

    <div x-show="selectedTickets.length > 0"
        x-transition
        class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10"
        style="display: none;">
        <div class="flex items-center gap-2">
            <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
            <span class="text-sm font-medium text-brand-700 dark:text-brand-400">
                <span x-text="selectedTickets.length"></span> tickets selected.
            </span>
        </div>
        <div class="flex items-center gap-4">
            <button type="button" @click="if (confirm('Are you sure you want to delete the selected tickets?')) { document.getElementById('bulkDeleteTicketsForm').submit(); }"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
                <i data-feather="trash-2" class="w-4 h-4"></i>
                Delete Selected
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="border-y border-t border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="py-3 pl-6 pr-2 w-10 text-start">
                                <div class="flex items-center">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                            <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                <span :class="selectAll ? '' : 'opacity-0'">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </th>
                            <th class="py-3 pl-2 pr-6 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Ticket Key</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Requester</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Summary</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Priority</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Status</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">SLA Risk</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Assignee</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($tickets as $ticket)
                            @php
                                $isOverdue = !($ticket->status?->is_closed) && $ticket->created_at->diffInDays(now()) >= 3;
                                $priorityIcon = $ticket->priority?->icon ?: 'flag';
                                $statusIcon = $ticket->status?->icon ?: 'clock';
                                $slaIcon = $isOverdue ? 'alert-circle' : 'check-circle';
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="py-3.5 pl-6 pr-2 text-start">
                                    <div class="flex items-center">
                                        <label class="flex cursor-pointer items-center select-none">
                                            <div class="relative">
                                                <input type="checkbox"
                                                    value="{{ $ticket->id }}"
                                                    class="ticket-checkbox sr-only"
                                                    :checked="selectedTickets.includes({{ $ticket->id }})"
                                                    @click="toggleTicket({{ $ticket->id }})">
                                                <div :class="selectedTickets.includes({{ $ticket->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                    class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                    <span :class="selectedTickets.includes({{ $ticket->id }}) ? '' : 'opacity-0'">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </td>
                                <td class="py-3.5 pl-2 pr-6">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-theme-sm font-medium text-brand-600 dark:text-brand-400">{{ $ticket->request_id }}</a>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                        {{ $ticket->requester_name ?? $ticket->requesterUser?->name }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="block max-w-xs truncate text-theme-sm text-gray-600 dark:text-gray-400">
                                        {{ \Illuminate\Support\Str::limit($ticket->problem_description, 50) }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                        <i data-feather="{{ $priorityIcon }}" class="w-3 h-3"></i>
                                        {{ $ticket->priority?->name ?? 'Normal' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        <i data-feather="{{ $statusIcon }}" class="w-3 h-3"></i>
                                        {{ $ticket->status?->name ?? 'Open' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    @if($isOverdue)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-[11px] font-semibold text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                            <i data-feather="{{ $slaIcon }}" class="w-3 h-3"></i>
                                            SLA Risk (>3d)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                            <i data-feather="{{ $slaIcon }}" class="w-3 h-3"></i>
                                            On Track
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="text-theme-sm text-gray-600 dark:text-gray-400">
                                        {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center justify-end gap-3 text-gray-500">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="transition-colors hover:text-brand-500" title="View Ticket">
                                            <i data-feather="eye" class="w-5 h-5"></i>
                                        </a>
                                        <a href="{{ route('tickets.edit', $ticket) }}" class="transition-colors hover:text-brand-500" title="Edit Ticket">
                                            <i data-feather="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="transition-colors hover:text-error-500" title="Delete Ticket">
                                                <i data-feather="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-gray-800/50">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No support tickets found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or create a new ticket to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-6 dark:border-gray-800">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }} results
                    </p>
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
</div>
@endsection
