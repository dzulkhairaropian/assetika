@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <x-common.page-breadcrumb pageTitle="IT Support Dashboard" />
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-gray-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                        Hi, {{ $userName }}! 👋
                        <span class="block text-sm font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $randomGreeting }}</span>
                    </h2>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="plus" class="w-4 h-4"></i> New Ticket
                    </a>
                    <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-750 transition">
                        <i data-feather="list" class="w-4 h-4"></i> All Tickets
                    </a>
                </div>
            </div>
        </div>

        <!-- Metrics Widgets Row 1 -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            @php $totalTicketsTrendColor = $totalTicketsTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Total Tickets"
                :value="$totalTickets"
                :trend="$totalTicketsTrend"
                :trendColor="$totalTicketsTrendColor"
                description="All support tickets ever created"
            />

            @php $openTicketsTrendColor = $openTicketsTrend <= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Open Tickets"
                :value="$openTickets"
                :trend="$openTicketsTrend"
                :trendColor="$openTicketsTrendColor"
                description="Active tickets needing attention"
            />

            @php $resolvedTrendColor = $resolvedTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Resolved"
                :value="$resolved"
                :trend="$resolvedTrend"
                :trendColor="$resolvedTrendColor"
                description="Closed tickets this month"
            />

            @php $unassignedTrendColor = $unassignedTrend <= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Unassigned"
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
                :value="$myTickets"
                description="{{ $myOpenTickets }} open"
            />
            @php $avgResolution = round($avgResolution, 1); @endphp
            <x-common.stat-card
                title="Avg Resolution Time"
                value="{{ $avgResolution }} hrs"
                description="Time from open to closed"
            />
            @php $rate = $totalTickets > 0 ? round(($resolved / $totalTickets) * 100, 1) : 0; @endphp
            <x-common.stat-card
                title="Resolution Rate"
                value="{{ $rate }}%"
                description="Tickets resolved vs total"
            />
        </div>

        <!-- Charts Row 1: Trend + Status -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Ticket Trend (Last 6 Months)</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Monthly ticket creation volume</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketTrendChart" class="h-80"></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Tickets by Status</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Current distribution across ticket lifecycle</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketStatusChart" class="h-80"></div>
            </div>
        </div>

        <!-- Charts Row 2: Category + Priority + Type -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-3 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Top Categories</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Most ticket-prone categories</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketCategoryChart" class="h-72"></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">By Priority</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Urgency breakdown</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketPriorityChart" class="h-72"></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">By Type</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Ticket classification</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketTypeChart" class="h-72"></div>
            </div>
        </div>

        <!-- Charts Row 3: Requesters + Workload -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Top Requesters</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Users with most ticket submissions</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketRequesterChart" class="h-72"></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">IT Staff Workload</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Open tickets per assigned staff</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div class="space-y-3">
                    @forelse($workload as $row)
                        @php $maxWl = $workload->max('count') ?: 1; $pct = ($row->count / $maxWl) * 100; @endphp
                        <div class="group">
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                        <i data-feather="user" class="w-3 h-3"></i>
                                    </span>
                                    {{ $staffNames[$row->assigned_to_id] ?? 'Unknown' }}
                                </span>
                                <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 shrink-0">{{ $row->count }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-400 transition-all duration-700 ease-out" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800 mb-3">
                                <i data-feather="users" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No workload data</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Assign tickets to see staff load</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Unassigned + Problem Assets Row -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <x-common.dashboard-table
                title="Unassigned Tickets"
                description="Open tickets without IT staff"
                :headers="['Ticket', 'Category', 'Priority', 'Date']"
            >
                @forelse($unassignedTickets as $ticket)
                    <tr>
                        <td class="py-3 pl-5 sm:pl-6 pr-4">
                            <p class="font-medium text-brand-600 text-theme-sm dark:text-brand-400">#{{ $ticket->request_id }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($ticket->requester_name, 20) }}</p>
                        </td>
                        <td class="py-3 pr-4 text-sm text-gray-500 dark:text-gray-400">{{ $ticket->category?->name ?? '-' }}</td>
                        <td class="py-3 pr-4">
                            <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">{{ $ticket->priority?->name ?? '-' }}</span>
                        </td>
                        <td class="py-3 pr-5 sm:pr-6 text-xs text-gray-400 dark:text-gray-500">{{ $ticket->created_at->format('M d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-400 dark:text-gray-500">All tickets assigned</td>
                    </tr>
                @endforelse
            </x-common.dashboard-table>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Top Problem Assets</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Assets with most ticket reports</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                @php $paMax = $problemAssets->max('count') ?: 1; @endphp
                <div class="space-y-3">
                    @forelse($problemAssets as $row)
                        @php
                            $asset = $assetDetails[$row->asset_id] ?? null;
                            $paPct = min(100, round(($row->count / $paMax) * 100));
                        @endphp
                        <div class="group">
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                        <i data-feather="hard-drive" class="w-3 h-3"></i>
                                    </span>
                                    <span class="truncate">
                                        <span class="block">{{ $asset?->asset_tag ?? 'Unknown' }}</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $asset?->device_name ?? '-' }}</span>
                                    </span>
                                </span>
                                <span class="rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400 shrink-0">{{ $row->count }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-error-500 to-error-400 transition-all duration-700 ease-out" style="width: {{ $paPct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800 mb-3">
                                <i data-feather="monitor" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No asset ticket data</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Asset reports will appear here</p>
                        </div>
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
                        <td class="py-3.5 pl-5 sm:pl-6 pr-4">
                            <p class="font-medium text-brand-600 text-theme-sm dark:text-brand-400">#{{ $ticket->request_id }}</p>
                        </td>
                        <td class="py-3.5 pr-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-semibold text-xs shrink-0">{{ strtoupper(substr($ticket->requester_name ?? 'N', 0, 1)) }}</div>
                                <span class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->requester_name }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 pr-4">
                            <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->category?->name ?? '-' }}</p>
                        </td>
                        <td class="py-3.5 pr-4">
                            @php
                                $pColor = $ticket->priority?->color ?? 'secondary';
                            @endphp
                            <x-ui.badge 
                                size="sm" 
                                :color="$pColor"
                                :featherIcon="$ticket->priority?->icon ?? 'alert-circle'"
                            >
                                {{ $ticket->priority?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="py-3.5 pr-4">
                            @php
                                $sColor = $ticket->status?->color ?? 'secondary';
                            @endphp
                            <x-ui.badge 
                                size="sm" 
                                :color="$sColor"
                                :featherIcon="$ticket->status?->icon ?? 'disc'"
                            >
                                {{ $ticket->status?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="py-3.5 pr-4">
                            <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</p>
                        </td>
                        <td class="py-3.5 pr-5 sm:pr-6">
                            <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->created_at->format('M d, H:i') }}</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i data-feather="file-text" class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3"></i>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">No tickets yet</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Start by creating your first support ticket</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-common.dashboard-table>
        </div>

    </div>
@endsection

@push('scripts')
{{-- ApexCharts bundled via Vite in app.js --}}
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