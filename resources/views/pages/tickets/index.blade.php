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

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Track, assign, and resolve IT support requests across your organization.
    </p>

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
        {{-- Card Header: Tabs + Toolbar --}}
        <div class="space-y-4 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            {{-- Quick Filter Tabs --}}
            @php
                $currentTab = request('tab');
                $tabFilters = array_filter(request()->except(['page', 'tab']));
                $tabs = [
                    ['key' => null,          'label' => 'All Tickets', 'count' => $ticketSummary['total'],       'icon' => 'layers',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                    ['key' => 'new',         'label' => 'New',         'count' => $ticketSummary['new'],         'icon' => 'alert-circle', 'badge' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400'],
                    ['key' => 'in_progress', 'label' => 'In Progress', 'count' => $ticketSummary['in_progress'], 'icon' => 'clock',        'badge' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500'],
                    ['key' => 'pending',     'label' => 'Pending',     'count' => $ticketSummary['pending'],     'icon' => 'pause-circle', 'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400'],
                    ['key' => 'unassigned',  'label' => 'Unassigned',  'count' => $ticketSummary['unassigned'],  'icon' => 'user-x',       'badge' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
                    ['key' => 'closed',      'label' => 'Closed / Resolved', 'count' => $ticketSummary['closed'],      'icon' => 'check-circle', 'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                ];
            @endphp
            <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5">
                @foreach($tabs as $tab)
                    @php
                        $isActive = $currentTab === $tab['key'];
                        $tabUrl = route('tickets.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                    @endphp
                    <a href="{{ $tabUrl }}"
                       class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all {{ $isActive ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
                        <i data-feather="{{ $tab['icon'] }}" class="w-4 h-4"></i>
                        {{ $tab['label'] }}
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                    </a>
                @endforeach
            </div>

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
                        <i data-feather="sliders" class="w-4 h-4"></i>
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
                                    <i data-feather="x" class="w-5 h-5"></i>
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

                <div class="flex items-center gap-3" x-data="{ exportModalOpen: false }">
                    <button type="button" @click="exportModalOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="download" class="w-4 h-4"></i>
                        Export Excel
                    </button>
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                        <i data-feather="plus" class="w-4 h-4"></i>
                        Create Ticket
                    </a>

                    <!-- Export Modal -->
                    <div x-show="exportModalOpen" x-cloak @keydown.escape.window="exportModalOpen = false"
                        class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-4"
                        style="display: none;">
                        <div @click="exportModalOpen = false" class="fixed inset-0 h-full w-full bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                        
                        <div @click.stop class="relative w-full max-w-md rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900 z-10 border border-gray-200 dark:border-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2.5 dark:border-gray-800">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Export Tickets</h3>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Select filters to export support tickets to Excel.</p>
                                </div>
                                <button type="button" @click="exportModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i data-feather="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <form action="{{ route('tickets.export') }}" method="GET" class="space-y-3">
                                <div class="grid grid-cols-2 gap-2.5 text-left">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">From Date</label>
                                        <input type="date" name="from_date" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">To Date</label>
                                        <input type="date" name="to_date" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Company</label>
                                        <select name="company_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Companies</option>
                                            @foreach($companies as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Department</label>
                                        <select name="department_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Departments</option>
                                            @foreach($departments as $d)
                                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Category</label>
                                        <select name="ticket_category_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Priority</label>
                                        <select name="priority_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Priorities</option>
                                            @foreach($priorities as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                                        <select name="status_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Statuses</option>
                                            @foreach($statuses as $st)
                                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Created By</label>
                                        <select name="created_by_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Users</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <button type="button" @click="exportModalOpen = false" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                        Cancel
                                    </button>
                                    <button type="submit" @click="exportModalOpen = false" class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        <i data-feather="download" class="w-3.5 h-3.5"></i>
                                        Export Excel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">
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
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Ticket Key / Problem</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requester</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Priority</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">SLA Risk</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Assignee</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center justify-end">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($tickets as $ticket)
                        @php
                            $isOverdue = !($ticket->status?->is_closed) && $ticket->created_at->diffInDays(now()) >= 3;
                            $priorityIcon = $ticket->priority?->icon ?: 'flag';
                            $statusIcon = $ticket->status?->icon ?: 'clock';
                            $slaIcon = $isOverdue ? 'alert-circle' : 'check-circle';
                            
                            $rowBg = 'hover:bg-gray-50/50 dark:hover:bg-white/[0.02]';
                            if ($isOverdue) {
                                $rowBg = 'bg-error-50/70 dark:bg-error-500/10 hover:bg-error-100/60 dark:hover:bg-error-500/15';
                            } elseif ($ticket->status?->is_closed) {
                                $rowBg = 'bg-gray-100/70 dark:bg-white/[0.04] hover:bg-gray-200/50 dark:hover:bg-white/[0.06]';
                            } elseif ($ticket->priority?->name === 'High') {
                                $rowBg = 'bg-warning-50/60 dark:bg-warning-500/10 hover:bg-warning-100/60 dark:hover:bg-warning-500/15';
                            } elseif ($ticket->priority?->name === 'Urgent') {
                                $rowBg = 'bg-error-50/60 dark:bg-error-500/10 hover:bg-error-100/60 dark:hover:bg-error-500/15';
                            }
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] {{ $rowBg }}">
                            <td class="px-5 py-4 sm:px-6">
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
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col">
                                    <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $ticket->request_id }}</span>
                                    <span class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($ticket->problem_description, 60) }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                        {{ $ticket->requester_name ?? $ticket->requesterUser?->name }}
                                    </span>
                                    @if($ticket->requesterDepartment || $ticket->requesterUser?->department)
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                            {{ $ticket->requesterDepartment?->name ?? $ticket->requesterUser?->department?->name }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if($ticket->priority)
                                    <x-ui.badge 
                                        size="sm" 
                                        :color="$ticket->priority->color ?? 'secondary'"
                                        :featherIcon="$ticket->priority->icon ?? 'flag'"
                                    >
                                        {{ $ticket->priority->name }}
                                    </x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if($ticket->status)
                                    <x-ui.badge 
                                        size="sm" 
                                        :color="$ticket->status->color ?? 'primary'"
                                        :featherIcon="$ticket->status->icon ?? 'disc'"
                                    >
                                        {{ $ticket->status->name }}
                                    </x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if($ticket->slaStatus)
                                    <x-ui.badge 
                                        size="sm" 
                                        :color="$ticket->slaStatus->color ?? ($isOverdue ? 'danger' : 'success')"
                                        :featherIcon="$ticket->slaStatus->icon ?? ($isOverdue ? 'alert-circle' : 'check-circle')"
                                    >
                                        {{ $ticket->slaStatus->name }}
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge 
                                        size="sm" 
                                        :color="$isOverdue ? 'danger' : 'success'"
                                        :featherIcon="$isOverdue ? 'alert-circle' : 'check-circle'"
                                    >
                                        {{ $isOverdue ? 'SLA Risk' : 'On Track' }}
                                    </x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col gap-1">
                                    <span class="text-theme-sm text-gray-600 dark:text-gray-400">
                                        {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                                    </span>
                                    @if($ticket->assignedTo?->department)
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                            {{ $ticket->assignedTo->department->name }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-right">
                                <div x-data="{ open: false }" class="relative inline-block text-left" @click.away="open = false">
                                    <button type="button" @click="open = !open" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                        <i data-feather="more-vertical" class="w-5 h-5"></i>
                                    </button>

                                    <div x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 top-full z-50 mt-1 w-44 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
                                        style="display: none;">
                                        
                                        <a href="{{ route('tickets.show', $ticket) }}" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                            <i data-feather="eye" class="w-4 h-4 text-gray-400"></i>
                                            View Details
                                        </a>

                                        <a href="{{ route('tickets.edit', $ticket) }}" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                            <i data-feather="edit-3" class="w-4 h-4 text-gray-400"></i>
                                            Edit
                                        </a>

                                        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                        <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 transition-colors">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                                Delete Ticket
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-gray-800/50">
                                        <i data-feather="file-text" class="w-6 h-6"></i>
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

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection
