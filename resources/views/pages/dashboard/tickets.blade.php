@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <x-common.page-breadcrumb pageTitle="Ticket Dashboard" />
            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Hi, {{ $userName }}! 👋
                        <span class="block text-xs font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $randomGreeting }}</span>
                    </h2>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="plus" class="w-3.5 h-3.5"></i> New Ticket
                    </a>
                    <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="list" class="w-3.5 h-3.5"></i> All Tickets
                    </a>
                </div>
            </div>
        </div>

        <!-- Metrics Widgets Row 1 -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
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
                description="Active tickets needing attention"
            />

            @php $resolvedTrendColor = $resolvedTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Resolved"
                icon="check-circle"
                :value="$resolved"
                :trend="$resolvedTrend"
                :trendColor="$resolvedTrendColor"
                description="Closed tickets this month"
            />

            @php $unassignedTrendColor = $unassignedTrend <= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Unassigned"
                icon="user-x"
                :value="$unassigned"
                :trend="$unassignedTrend"
                :trendColor="$unassignedTrendColor"
                description="Tickets without IT staff assigned"
            />
        </div>

        <!-- Quick Stats Row -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-3 min-w-0">
            <x-common.stat-card
                title="My Tickets"
                icon="user"
                :value="$myTickets"
                description="{{ $myOpenTickets }} open tickets"
            />
            @php $avgResolution = round($avgResolution, 1); @endphp
            <x-common.stat-card
                title="Avg Resolution Time"
                icon="clock"
                value="{{ $avgResolution }} hrs"
                description="Time from open to closed"
            />
            @php $rate = $totalTickets > 0 ? round(($resolved / $totalTickets) * 100, 1) : 0; @endphp
            <x-common.stat-card
                title="Resolution Rate"
                icon="activity"
                value="{{ $rate }}%"
                description="Tickets resolved vs total"
            />
        </div>

        <!-- Charts Row 1: Trend + Status -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ticket Trend (Last 6 Months)</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Monthly ticket creation volume</p>
                </div>
                <div id="ticketTrendChart" class="h-64"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tickets by Status</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Current distribution across lifecycle</p>
                </div>
                <div id="ticketStatusChart" class="h-64 flex items-center justify-center"></div>
            </div>
        </div>

        <!-- Charts Row 2: Category + Priority + Type (3 per row) -->
        <div class="col-span-12 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 min-w-0">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Categories</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Most frequent ticket categories</p>
                </div>
                <div id="ticketCategoryChart" class="h-64 flex items-center justify-center"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">By Priority</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Urgency breakdown</p>
                </div>
                <div id="ticketPriorityChart" class="h-64 flex items-center justify-center"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden md:col-span-2 xl:col-span-1">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">By Type</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Ticket classification</p>
                </div>
                <div id="ticketTypeChart" class="h-64 flex items-center justify-center"></div>
            </div>
        </div>

        <!-- Charts Row 3: Requesters + Workload -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Requesters</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Users with most submissions</p>
                </div>
                <div id="ticketRequesterChart" class="h-64 flex items-center justify-center"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">IT Staff Workload</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Open tickets per assigned staff</p>
                </div>
                <div class="space-y-2.5">
                    @forelse($workload as $row)
                        @php $maxWl = $workload->max('count') ?: 1; $pct = ($row->count / $maxWl) * 100; @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300">
                                    {{ $staffNames[$row->assigned_to_id] ?? 'Unknown' }}
                                </span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $row->count }}</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-brand-500 transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 py-4 text-center">No assigned tickets</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Unassigned + Problem Assets Row -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <x-common.dashboard-table
                title="Unassigned Tickets"
                description="Open tickets without IT staff"
                :headers="['Ticket', 'Category', 'Priority', 'Date']"
            >
                @forelse($unassignedTickets as $ticket)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-brand-600 dark:text-brand-400">#{{ $ticket->request_id }}</p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ Str::limit($ticket->requester_name, 20) }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ $ticket->category?->name ?? '-' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge 
                                size="sm" 
                                :color="$ticket->priority?->color ?? 'warning'"
                                :featherIcon="$ticket->priority?->icon ?? 'alert-circle'"
                            >
                                {{ $ticket->priority?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $ticket->created_at->format('M d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">All tickets assigned</td>
                    </tr>
                @endforelse
            </x-common.dashboard-table>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Problem Assets</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Assets with most ticket reports</p>
                </div>
                @php $paMax = $problemAssets->max('count') ?: 1; @endphp
                <div class="space-y-2.5">
                    @forelse($problemAssets as $row)
                        @php
                            $asset = $assetDetails[$row->asset_id] ?? null;
                            $paPct = min(100, round(($row->count / $paMax) * 100));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300">
                                    {{ $asset?->asset_tag ?? 'Unknown' }} ({{ $asset?->device_name ?? '-' }})
                                </span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $row->count }}</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-red-500 transition-all duration-500" style="width: {{ $paPct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 py-4 text-center">No asset ticket data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Tickets Table -->
        <div class="col-span-12 min-w-0">
            <x-common.dashboard-table
                title="Recent Tickets"
                description="Latest support ticket activity"
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
                            {{ $ticket->category?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge 
                                size="sm" 
                                :color="$ticket->priority?->color ?? 'secondary'"
                                :featherIcon="$ticket->priority?->icon ?? 'alert-circle'"
                            >
                                {{ $ticket->priority?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge 
                                size="sm" 
                                :color="$ticket->status?->color ?? 'primary'"
                                :featherIcon="$ticket->status?->icon ?? 'disc'"
                            >
                                {{ $ticket->status?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                            {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $ticket->created_at->format('M d, H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-xs text-gray-400">No support tickets found</td>
                    </tr>
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
