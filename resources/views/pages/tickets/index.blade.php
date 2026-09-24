@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{
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

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
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
        class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/50 p-3 dark:border-brand-900/30 dark:bg-brand-500/10"
        style="display: none;">
        <div class="flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
            <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                <span x-text="selectedTickets.length"></span> tickets selected.
            </span>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="if (confirm('Are you sure you want to delete the selected tickets?')) { document.getElementById('bulkDeleteTicketsForm').submit(); }"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-600">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                Delete Selected
            </button>
        </div>
    </div>

    <div class="overflow-hidden xl:overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        {{-- Card Header: Tabs + Toolbar --}}
        <div class="space-y-3 border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
            {{-- Quick Filter Tabs --}}
            @php
                $currentTab = request('tab');
                $tabFilters = array_filter(request()->except(['page', 'tab']));
                $tabs = [
                    ['key' => null,          'label' => 'All Tickets', 'count' => $ticketSummary['total'],       'icon' => 'layers',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                    ['key' => 'created_today','label' => 'Created Today', 'count' => $ticketSummary['created_today'], 'icon' => 'calendar',     'badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'],
                    ['key' => 'new',         'label' => 'New',         'count' => $ticketSummary['new'],         'icon' => 'alert-circle', 'badge' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400'],
                    ['key' => 'in_progress', 'label' => 'In Progress', 'count' => $ticketSummary['in_progress'], 'icon' => 'clock',        'badge' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400'],
                    ['key' => 'pending',     'label' => 'Pending',     'count' => $ticketSummary['pending'],     'icon' => 'pause-circle', 'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400'],
                    ['key' => 'unassigned',  'label' => 'Unassigned',  'count' => $ticketSummary['unassigned'],  'icon' => 'user-x',       'badge' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
                    ['key' => 'closed',      'label' => 'Closed / Resolved', 'count' => $ticketSummary['closed'],      'icon' => 'circle-check', 'badge' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400'],
                ];
            @endphp
            <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5">
                @foreach($tabs as $tab)
                    @php
                        $isActive = $currentTab === $tab['key'];
                        $tabUrl = route('tickets.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                    @endphp
                    <a href="{{ $tabUrl }}"
                       class="flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
                        <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                        {{ $tab['label'] }}
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                    <div class="relative flex items-center">
                        <span class="absolute left-2.5 text-gray-400">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, requester, issue..."
                            class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                    </div>

                    @php
                        $activeCount = 0;
                        if (request('status_id')) $activeCount++;
                        if (request('priority_id')) $activeCount++;
                        if (request('ticket_category_id')) $activeCount++;
                        if (request('company_id')) $activeCount++;
                        if (request('department_id')) $activeCount++;
                        if (request('start_date') || request('from_date') || request('created_from')) $activeCount++;
                        if (request('end_date') || request('to_date') || request('created_to')) $activeCount++;
                        if (request('date_type')) $activeCount++;
                    @endphp

                    <div class="relative" @click.away="filterOpen = false">
                        <button type="button" @click="filterOpen = !filterOpen"
                            class="relative inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                            Filters
                            @if($activeCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[18px] h-4 px-1 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                            @endif
                        </button>

                        <div x-show="filterOpen"
                            x-transition
                            class="absolute left-0 top-full z-50 mt-2 w-80 sm:w-96 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            style="display: none;">
                            <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5 text-left">
                                <div class="col-span-2">
                                    <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Date Filter Basis</label>
                                    <div class="relative">
                                        <select name="date_type" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                            <option value="created_at" {{ request('date_type', 'created_at') === 'created_at' ? 'selected' : '' }}>Created Date (System Input Date)</option>
                                            <option value="request_date" {{ request('date_type') === 'request_date' ? 'selected' : '' }}>Request Date (Ticket Date)</option>
                                            <option value="both" {{ request('date_type') === 'both' ? 'selected' : '' }}>Both (Either Created or Request Date)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-span-2 sm:col-span-1">
                                    <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Start Date</label>
                                    <input type="date" name="start_date" value="{{ request('start_date', request('from_date', request('created_from'))) }}"
                                        class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                </div>

                                <div class="col-span-2 sm:col-span-1">
                                    <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">End Date</label>
                                    <input type="date" name="end_date" value="{{ request('end_date', request('to_date', request('created_to'))) }}"
                                        class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                </div>

                                <div>
                                    <x-form.searchable-select name="status_id" label="Status" :options="$statuses->map(fn($s) => ['id' => $s->id, 'text' => $s->name])->toArray()" :selected="request('status_id')" placeholder="All Statuses" :multiple="true" />
                                </div>

                                <div>
                                    <x-form.searchable-select name="priority_id" label="Priority" :options="$priorities->map(fn($p) => ['id' => $p->id, 'text' => $p->name])->toArray()" :selected="request('priority_id')" placeholder="All Priorities" :multiple="true" />
                                </div>

                                <div>
                                    @php
                                        $catOptions = collect([['id' => 'general', 'text' => 'General']])
                                            ->concat($categories->map(fn($c) => ['id' => (string)$c->id, 'text' => $c->name]))
                                            ->toArray();
                                    @endphp
                                    <x-form.searchable-select name="ticket_category_id" label="Category" :options="$catOptions" :selected="request('ticket_category_id', request('category_id'))" placeholder="All Categories" :multiple="true" />
                                </div>

                                <div>
                                    <x-form.searchable-select name="company_id" label="Company" :options="$companies->map(fn($co) => ['id' => $co->id, 'text' => $co->name])->toArray()" :selected="request('company_id')" placeholder="All Companies" :multiple="true" />
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <a href="{{ route('tickets.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                    Clear All Filters
                                </a>
                                <button type="submit" @click="filterOpen = false"
                                    class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-2" x-data="{ exportModalOpen: false }">
                    <button type="button" @click="exportModalOpen = true" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="download" class="w-3.5 h-3.5 text-gray-500"></i>
                        Export Excel
                    </button>
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
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
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <form action="{{ route('tickets.export') }}" method="GET" class="space-y-3">
                                <div class="grid grid-cols-2 gap-2.5 text-left">
                                    <div class="col-span-2">
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Date Filter Basis</label>
                                        <select name="date_type" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="created_at" {{ request('date_type', 'created_at') === 'created_at' ? 'selected' : '' }}>Created Date (System Input Date)</option>
                                            <option value="request_date" {{ request('date_type') === 'request_date' ? 'selected' : '' }}>Request Date (Ticket Date)</option>
                                            <option value="both" {{ request('date_type') === 'both' ? 'selected' : '' }}>Both (Either Created or Request Date)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">From Date</label>
                                        <input type="date" name="from_date" value="{{ request('start_date', request('from_date', request('created_from'))) }}" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">To Date</label>
                                        <input type="date" name="to_date" value="{{ request('end_date', request('to_date', request('created_to'))) }}" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Company</label>
                                        <select name="company_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Companies</option>
                                            @foreach($companies as $c)
                                                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Department</label>
                                        <select name="department_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Departments</option>
                                            @foreach($departments as $d)
                                                <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Category</label>
                                        <select name="ticket_category_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ request('ticket_category_id', request('category_id')) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Priority</label>
                                        <select name="priority_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Priorities</option>
                                            @foreach($priorities as $p)
                                                <option value="{{ $p->id }}" {{ request('priority_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                                        <select name="status_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Statuses</option>
                                            @foreach($statuses as $st)
                                                <option value="{{ $st->id }}" {{ request('status_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Created By</label>
                                        <select name="created_by_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">All Users</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ request('created_by_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <button type="button" @click="exportModalOpen = false" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                        Cancel
                                    </button>
                                    <button type="submit" @click="setTimeout(() => exportModalOpen = false, 500)" class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
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
        <div class="max-w-full overflow-x-auto xl:overflow-visible">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">
                            <label class="flex cursor-pointer items-center select-none">
                                <div class="relative">
                                    <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                    <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                        class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                        <span :class="selectAll ? '' : 'opacity-0'">
                                            <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </th>
                        <th class="px-4 py-2.5 text-start">
                            <x-common.sortable-header column="created_at" label="Created Date" />
                        </th>
                        <th class="px-4 py-2.5 text-start">
                            <x-common.sortable-header column="request_id" label="Ticket Key / Problem" />
                        </th>
                        <th class="px-4 py-2.5 text-start">
                            <x-common.sortable-header column="requester_name" label="Requester" />
                        </th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Priority</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">SLA Risk</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Assignee</th>
                        <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tickets as $ticket)
                        @php
                            $isOverdue = !($ticket->status?->is_closed) && $ticket->created_at->diffInDays(now()) >= 3;
                            $priorityIcon = $ticket->priority?->icon ?: 'flag';
                            $statusIcon = $ticket->status?->icon ?: 'disc';
                            $slaIcon = $isOverdue ? 'alert-circle' : 'circle-check';
                            
                            $rowBg = 'hover:bg-gray-50/50 dark:hover:bg-white/[0.02]';
                            if ($isOverdue) {
                                $rowBg = 'bg-red-50/50 dark:bg-red-500/10 hover:bg-red-100/50';
                            } elseif ($ticket->status?->is_closed) {
                                $rowBg = 'bg-gray-50 dark:bg-white/[0.02] hover:bg-gray-100/50';
                            } elseif ($ticket->priority?->name === 'High') {
                                $rowBg = 'bg-orange-50/50 dark:bg-orange-500/10 hover:bg-orange-100/50';
                            } elseif ($ticket->priority?->name === 'Urgent' || $ticket->priority?->name === 'Critical') {
                                $rowBg = 'bg-red-50/40 dark:bg-red-500/10 hover:bg-red-100/40';
                            }
                        @endphp
                        <tr class="transition {{ $rowBg }}">
                            <td class="px-4 py-2.5">
                                <label class="flex cursor-pointer items-center select-none">
                                    <div class="relative">
                                        <input type="checkbox"
                                            value="{{ $ticket->id }}"
                                            class="ticket-checkbox sr-only"
                                            :checked="selectedTickets.includes({{ $ticket->id }})"
                                            @click="toggleTicket({{ $ticket->id }})">
                                        <div :class="selectedTickets.includes({{ $ticket->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                            class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                            <span :class="selectedTickets.includes({{ $ticket->id }}) ? '' : 'opacity-0'">
                                                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <span class="font-mono text-xs text-gray-600 dark:text-gray-300">
                                    {{ $ticket->created_at?->format('d/m/Y') ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-mono font-semibold tracking-tight text-brand-600 hover:text-brand-700 dark:text-brand-400">#{{ $ticket->request_id }}</a>
                                    <span class="text-[11px] text-gray-400">{{ \Illuminate\Support\Str::limit($ticket->problem_description, 60) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $ticket->requester_name ?? $ticket->requesterUser?->name }}
                                    </span>
                                    @if($ticket->requesterDepartment || $ticket->requesterUser?->department)
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                            {{ $ticket->requesterDepartment?->name ?? $ticket->requesterUser?->department?->name }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <x-ui.badge color="primary" variant="light" size="sm">
                                    {{ $ticket->ticketCategory?->name ?? $ticket->category?->name ?? 'General' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($ticket->priority)
                                    <x-ui.badge 
                                        size="sm" 
                                        :color="$ticket->priority->color ?? 'secondary'"
                                        :featherIcon="$ticket->priority->icon ?: 'flag'"
                                    >
                                        {{ $ticket->priority->name }}
                                    </x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($ticket->status)
                                    <x-ui.badge 
                                        size="sm" 
                                        :color="$ticket->status->color ?? 'primary'"
                                        :featherIcon="$ticket->status->icon ?: 'disc'"
                                    >
                                        {{ $ticket->status->name }}
                                    </x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($ticket->slaStatus)
                                    <x-ui.badge 
                                        size="sm" 
                                        :color="$ticket->slaStatus->color ?? ($isOverdue ? 'danger' : 'success')"
                                        :featherIcon="$ticket->slaStatus->icon ?: ($isOverdue ? 'alert-circle' : 'circle-check')"
                                    >
                                        {{ $ticket->slaStatus->name }}
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge 
                                        size="sm" 
                                        :color="$isOverdue ? 'danger' : 'success'"
                                        :featherIcon="$isOverdue ? 'alert-circle' : 'circle-check'"
                                    >
                                        {{ $isOverdue ? 'SLA Risk' : 'On Track' }}
                                    </x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                                    </span>
                                    @if($ticket->assignedTo?->department)
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                            {{ $ticket->assignedTo->department->name }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-end">
                                <div x-data="{
                                    open: false,
                                    dropdownStyle: '',
                                    toggle(e) {
                                        this.open = !this.open;
                                        if (this.open) {
                                            const rect = e.currentTarget.getBoundingClientRect();
                                            const menuHeight = 120;
                                            const spaceBelow = window.innerHeight - rect.bottom;
                                            const top = spaceBelow < menuHeight ? Math.max(10, rect.top - menuHeight) : (rect.bottom + 4);
                                            const left = Math.max(10, rect.right - 160);
                                            this.dropdownStyle = `position: fixed; top: ${top}px; left: ${left}px; z-index: 9999; display: block;`;
                                        }
                                    }
                                }" @click.away="open = false" @scroll.window="open = false" class="inline-block text-left">
                                    <button type="button" @click="toggle($event)" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                    </button>

                                    <div x-show="open"
                                        x-transition
                                        :style="open ? dropdownStyle : 'display: none;'"
                                        class="w-40 rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                        style="display: none;">
                                        
                                        <a href="{{ route('tickets.show', $ticket) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                                            View Details
                                        </a>

                                        <a href="{{ route('tickets.edit', $ticket) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-gray-400"></i>
                                            Edit
                                        </a>

                                        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                        <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                Delete Ticket
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-1.5">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No tickets found</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Start by creating your first support ticket.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Per page:</span>
                <select onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();"
                    class="h-8 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                </select>
            </div>
            <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
