@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <x-common.page-breadcrumb pageTitle="IT Support Dashboard" />
            <h2 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white/90">
                Hi, {{ $userName }}!
                <span class="block text-base font-normal text-gray-500 dark:text-gray-400">{{ $randomGreeting }}</span>
            </h2>
        </div>

        <!-- Metrics Widgets Row 1 -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Tickets</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $totalTickets }}</h4>
                    </div>
                    @php $trendColor = $totalTicketsTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        @if($totalTicketsTrend >= 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ abs($totalTicketsTrend) }}%
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">All support tickets ever created</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Open Tickets</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $openTickets }}</h4>
                    </div>
                    @php $trendColor = $openTicketsTrend <= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        @if($openTicketsTrend >= 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ abs($openTicketsTrend) }}%
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Active tickets needing attention</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Resolved</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $resolved }}</h4>
                    </div>
                    @php $trendColor = $resolvedTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        @if($resolvedTrend >= 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ abs($resolvedTrend) }}%
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Closed tickets this month</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Unassigned</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $unassigned }}</h4>
                    </div>
                    @php $trendColor = $unassignedTrend <= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        @if($unassignedTrend >= 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ abs($unassignedTrend) }}%
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Tickets without IT staff assigned</p>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-3 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">My Tickets</p>
                <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $myTickets }}</h4>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">{{ $myOpenTickets }} open</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Avg Resolution Time</p>
                <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ round($avgResolution, 1) }} hrs</h4>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Time from open to closed</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Resolution Rate</p>
                @php $rate = $totalTickets > 0 ? round(($resolved / $totalTickets) * 100, 1) : 0; @endphp
                <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $rate }}%</h4>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Tickets resolved vs total</p>
            </div>
        </div>

        <!-- Charts Row 1: Trend + Status -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ticket Trend (Last 6 Months)</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Monthly ticket creation volume</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketTrendChart" class="h-80"></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Tickets by Status</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Current distribution across ticket lifecycle</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketStatusChart" class="h-80"></div>
            </div>
        </div>

        <!-- Charts Row 2: Category + Priority + Type -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-3 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Categories</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Most ticket-prone categories</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketCategoryChart" class="h-72"></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">By Priority</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Urgency breakdown</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketPriorityChart" class="h-72"></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">By Type</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Ticket classification</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketTypeChart" class="h-72"></div>
            </div>
        </div>

        <!-- Charts Row 3: Requesters + Workload -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Requesters</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Users with most ticket submissions</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div id="ticketRequesterChart" class="h-72"></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">IT Staff Workload</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Open tickets per assigned staff</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div class="space-y-3">
                    @forelse($workload as $row)
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white text-theme-sm truncate">{{ $staffNames[$row->assigned_to_id] ?? 'Unknown' }}</p>
                                <div class="mt-1.5 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                                    @php $maxWl = $workload->max('count') ?: 1; $pct = ($row->count / $maxWl) * 100; @endphp
                                    <div class="h-2 rounded-full bg-brand-500" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            <span class="rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 shrink-0">{{ $row->count }}</span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No workload data</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Assign tickets to see staff load</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Unassigned + Problem Assets Row -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 pt-5 sm:px-6 sm:pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Unassigned Tickets</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Open tickets without IT staff</p>
                </div>
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($unassignedTickets as $ticket)
                                <tr>
                                    <td class="py-3 pl-5 sm:pl-6 pr-4">
                                        <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">#{{ $ticket->request_id }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($ticket->requester_name, 20) }}</p>
                                    </td>
                                    <td class="py-3 pr-4 text-sm text-gray-500 dark:text-gray-400">{{ $ticket->category?->name }}</td>
                                    <td class="py-3 pr-4">
                                        <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">{{ $ticket->priority?->name ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 pr-5 sm:pr-6 text-right text-xs text-gray-400 dark:text-gray-500">{{ $ticket->created_at->format('M d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-400 dark:text-gray-500">All tickets assigned</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Problem Assets</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Assets with most ticket reports</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                <div class="space-y-3">
                    @forelse($problemAssets as $row)
                        @php $asset = $assetDetails[$row->asset_id] ?? null; @endphp
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-100 p-3 dark:border-gray-800">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white text-theme-sm truncate">{{ $asset?->asset_tag ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $asset?->device_name ?? '-' }}</p>
                            </div>
                            <span class="rounded-full bg-error-50 px-3 py-1 text-sm font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400 shrink-0">{{ $row->count }}</span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No asset ticket data</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Asset reports will appear here</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Tickets Table -->
        <div class="col-span-12 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 pt-5 sm:px-6 sm:pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Tickets</h3>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Latest support ticket activity</p>
                    </div>
                    <a href="/tickets" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">
                        View All <i data-feather="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-white/[0.02]">
                            <th class="py-3 pl-5 sm:pl-6 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">ID</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requester</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Category</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Priority</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Assigned To</span></th>
                            <th class="py-3 pr-5 sm:pr-6 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Created</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recentTickets as $ticket)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                                <td class="py-3.5 pl-5 sm:pl-6 pr-4"><p class="font-medium text-brand-600 text-theme-sm dark:text-brand-400">#{{ $ticket->request_id }}</p></td>
                                <td class="py-3.5 pr-4"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->requester_name }}</p></td>
                                <td class="py-3.5 pr-4"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->category?->name ?? '-' }}</p></td>
                                <td class="py-3.5 pr-4">
                                    @php
                                        $pColor = $ticket->priority?->color ?? 'gray';
                                        $pClasses = [
                                            'danger' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                            'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                            'success' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                            'info' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
                                            'gray' => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                                        ];
                                    @endphp
                                    <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $pClasses[$pColor] ?? $pClasses['gray'] }}">{{ $ticket->priority?->name ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 pr-4">
                                    @php
                                        $sColor = $ticket->status?->color ?? 'gray';
                                        $sClasses = [
                                            'info' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
                                            'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                            'success' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                            'danger' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                            'gray' => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                                        ];
                                    @endphp
                                    <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $sClasses[$sColor] ?? $sClasses['gray'] }}">{{ $ticket->status?->name ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 pr-4"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</p></td>
                                <td class="py-3.5 pr-5 sm:pr-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->created_at->format('M d, H:i') }}</p></td>
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
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fontFamily = "Outfit, sans-serif";

        // Trend Area Chart
        const trendOptions = {
            series: [{ name: 'Tickets', data: @json($trendChart['data']) }],
            chart: { fontFamily, type: 'area', height: 320, toolbar: { show: false } },
            colors: ['#465FFF'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: @json($trendChart['labels']), labels: { style: { fontFamily } } },
            yaxis: { labels: { style: { fontFamily } } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
        };
        new ApexCharts(document.querySelector("#ticketTrendChart"), trendOptions).render();

        // Status Donut
        const statusOptions = {
            series: @json($chartStatusData['data']),
            chart: { fontFamily, type: 'donut', height: 320 },
            labels: @json($chartStatusData['labels']),
            colors: @json($chartStatusData['colors']),
            legend: { position: 'bottom', fontFamily },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true, fontSize: '20px', fontWeight: 'bold' }, total: { show: true, label: 'Total', formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) } } } } },
            dataLabels: { enabled: false },
        };
        new ApexCharts(document.querySelector("#ticketStatusChart"), statusOptions).render();

        // Category Donut
        const categoryOptions = {
            series: @json($categoryChart['data']),
            chart: { fontFamily, type: 'donut', height: 288 },
            labels: @json($categoryChart['labels']),
            colors: ['#465FFF', '#00F5D4', '#FF9F1C', '#FF5400', '#7209B7'],
            legend: { position: 'bottom', fontFamily },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true }, total: { show: true, label: 'Total' } } } } },
            dataLabels: { enabled: false },
        };
        new ApexCharts(document.querySelector("#ticketCategoryChart"), categoryOptions).render();

        // Priority Horizontal Bar
        const priorityOptions = {
            series: [{ name: 'Tickets', data: @json($priorityChart['data']) }],
            chart: { fontFamily, type: 'bar', height: 288, toolbar: { show: false } },
            colors: ['#F79009'],
            plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '60%' } },
            xaxis: { categories: @json($priorityChart['labels']), labels: { style: { fontFamily } } },
            yaxis: { labels: { style: { fontFamily } } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
        };
        new ApexCharts(document.querySelector("#ticketPriorityChart"), priorityOptions).render();

        // Type Donut
        const typeOptions = {
            series: @json($typeChart['data']),
            chart: { fontFamily, type: 'donut', height: 288 },
            labels: @json($typeChart['labels']),
            colors: ['#00F5D4', '#7209B7', '#465FFF', '#FF5400', '#FF9F1C'],
            legend: { position: 'bottom', fontFamily },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true }, total: { show: true, label: 'Total' } } } } },
            dataLabels: { enabled: false },
        };
        new ApexCharts(document.querySelector("#ticketTypeChart"), typeOptions).render();

        // Requester Horizontal Bar
        const requesterOptions = {
            series: [{ name: 'Tickets', data: @json($requesterChart['data']) }],
            chart: { fontFamily, type: 'bar', height: 288, toolbar: { show: false } },
            colors: ['#12B76A'],
            plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '60%' } },
            xaxis: { categories: @json($requesterChart['labels']), labels: { style: { fontFamily } } },
            yaxis: { labels: { style: { fontFamily } } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
        };
        new ApexCharts(document.querySelector("#ticketRequesterChart"), requesterOptions).render();
    });
</script>
@endpush
