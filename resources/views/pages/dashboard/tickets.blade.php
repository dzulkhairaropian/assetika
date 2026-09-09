@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support & Ticket Operations Center</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Live tracking of service desk tickets, SLA response times, and staff workload.</p>
        </div>
	        <div class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
	            <!-- Filter Options Toggle Button -->
	            <div class="relative">
	                <button type="button" @click="filterOpen = !filterOpen"
	                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
	                    <i data-feather="filter" class="w-4 h-4 text-gray-500"></i>
	                    <span>Filter Options</span>
	                    @if($activeFiltersCount > 0)
	                        <span class="flex h-4 w-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white ml-0.5">{{ $activeFiltersCount }}</span>
	                    @endif
	                </button>

	                <!-- Filter Options Dropdown Panel -->
	                <div x-show="filterOpen" @click.away="filterOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
	                    class="absolute right-0 top-full z-50 mt-2 origin-top-right w-80 sm:w-96 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
	                    style="display: none;">
	                    <form action="{{ route('ticket-dashboard') }}" method="GET">
	                        <div class="mb-3 flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2.5">
	                            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-800 dark:text-white">Filter Options</h4>
	                            <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
	                                <i data-feather="x" class="w-4 h-4"></i>
	                            </button>
	                        </div>

	                        <div class="space-y-3 text-left">
	                            <div>
	                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
	                                <select name="status_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
	                                    <option value="">All Statuses</option>
	                                    @foreach($statuses as $status)
	                                        <option value="{{ $status->id }}" {{ (string) $filterStatusId === (string) $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
	                                    @endforeach
	                                </select>
	                            </div>
	                            <div>
	                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Priority</label>
	                                <select name="priority_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
	                                    <option value="">All Priorities</option>
	                                    @foreach($priorities as $priority)
	                                        <option value="{{ $priority->id }}" {{ (string) $filterPriorityId === (string) $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
	                                    @endforeach
	                                </select>
	                            </div>
	                        </div>

	                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
	                            <a href="{{ route('ticket-dashboard') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Clear All</a>
	                            <button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 active:scale-[0.97] transition-all duration-150">Apply</button>
	                        </div>
	                    </form>
	                </div>
	            </div>

	            <a href="{{ route('tickets.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
	                <i data-feather="download" class="w-4 h-4 text-gray-500"></i> Export
	            </a>
	            <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.97] transition-all duration-150 ease-out">
	                <i data-feather="plus" class="w-4 h-4"></i> New Ticket
	            </a>
	        </div>
    </div>

    <!-- 1. Key Metrics Cards Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php $totalTicketsTrendColor = $totalTicketsTrend >= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="Total Tickets"
            icon="ticket"
            :value="$totalTickets"
            :trend="$totalTicketsTrend"
            :trendColor="$totalTicketsTrendColor"
            description="All support tickets ever created"
        />

        @php $openTicketsTrendColor = $openTicketsTrend <= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="Open Tickets"
            icon="alert-circle"
            :value="$openTickets"
            :trend="$openTicketsTrend"
            :trendColor="$openTicketsTrendColor"
            description="Active tickets needing resolution"
        />

        @php $resolvedTrendColor = $resolvedTrend >= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="Resolved Tickets"
            icon="check-circle"
            :value="$resolved"
            :trend="$resolvedTrend"
            :trendColor="$resolvedTrendColor"
            description="Closed tickets this period"
        />

        @php $unassignedTrendColor = $unassignedTrend <= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="Unassigned Tickets"
            icon="user-x"
            :value="$unassigned"
            :trend="$unassignedTrend"
            :trendColor="$unassignedTrendColor"
            description="Awaiting staff allocation"
        />
    </div>

    <!-- 2. Performance Metric Cards Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-common.stat-card
            title="My Assigned Tickets"
            icon="user"
            :value="$myTickets"
            description="{{ $myOpenTickets }} open tickets pending"
        />
        @php $avgResolution = round($avgResolution, 1); @endphp
        <x-common.stat-card
            title="Avg Resolution Time"
            icon="clock"
            value="{{ $avgResolution }} hrs"
            description="Turnaround time from open to close"
        />
        @php $rate = $totalTickets > 0 ? round(($resolved / $totalTickets) * 100, 1) : 0; @endphp
        <x-common.stat-card
            title="SLA Resolution Rate"
            icon="activity"
            value="{{ $rate }}%"
            description="Overall ticket completion efficiency"
        />
    </div>

    <!-- 3. Ticket Analytics Charts Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Ticket Volume Trend</h3>
                    <p class="text-xs text-gray-400">Monthly submissions</p>
                </div>
            </div>
            <div id="ticketTrendChart" class="h-64"></div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Status Breakdown</h3>
                    <p class="text-xs text-gray-400">Distribution across lifecycle</p>
                </div>
            </div>
            <div id="ticketStatusChart" class="h-64 flex items-center justify-center"></div>
        </div>
    </div>

    <!-- 4. Category & Priority Charts -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top Categories</h3>
                <p class="text-xs text-gray-400">By issue type</p>
            </div>
            <div id="ticketCategoryChart" class="h-64 flex items-center justify-center"></div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Priority Distribution</h3>
                <p class="text-xs text-gray-400">Urgency breakdown</p>
            </div>
            <div id="ticketPriorityChart" class="h-64 flex items-center justify-center"></div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Classification</h3>
                <p class="text-xs text-gray-400">Incident vs Request</p>
            </div>
            <div id="ticketTypeChart" class="h-64 flex items-center justify-center"></div>
        </div>
    </div>

    <!-- 5. Ticket Activity Data Table -->
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <x-common.dashboard-table
            title="Recent Support Tickets"
            description="Live feed of incoming tickets"
            :headers="['ID', 'Requester', 'Category', 'Priority', 'Status', 'Assigned To', 'Created']"
            :footerLink="route('tickets.index')"
            footerText="View All Tickets"
        >
            @forelse($recentTickets as $ticket)
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                    <td class="px-4 py-2.5">
                        <p class="font-medium text-brand-600 dark:text-brand-400">#{{ $ticket->request_id }}</p>
                    </td>
                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                        {{ $ticket->requester_name }}
                    </td>
                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                        {{ $ticket->ticketCategory?->name ?? $ticket->category?->name ?? '-' }}
                    </td>
                    <td class="px-4 py-2.5">
                        <x-ui.badge 
                            size="sm" 
                            :color="$ticket->priority?->color ?? 'secondary'"
                            :featherIcon="$ticket->priority?->icon ?: 'alert-circle'"
                        >
                            {{ $ticket->priority?->name ?? '-' }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-2.5">
                        <x-ui.badge 
                            size="sm" 
                            :color="$ticket->status?->color ?? 'primary'"
                            :featherIcon="$ticket->status?->icon ?: 'disc'"
                        >
                            {{ $ticket->status?->name ?? '-' }}
                        </x-ui.badge>
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
                    <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        {{ $ticket->created_at->format('M d, H:i') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-xs text-gray-400">No support tickets found</td></tr>
            @endforelse
        </x-common.dashboard-table>
    </div>

</div>
@endsection

@push('scripts')
<script>
    window.ticketChartData = {
        trendChart: @json($trendChart),
        chartStatusData: @json($chartStatusData),
        categoryChart: @json($categoryChart),
        priorityChart: @json($priorityChart),
        typeChart: @json($typeChart),
        requesterChart: @json($requesterChart),
    };
</script>
@vite('resources/js/ticket-charts.js')
@endpush
