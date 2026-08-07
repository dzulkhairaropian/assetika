@php
    $statusColors = [
        'info' => '#465FFF',
        'warning' => '#F79009',
        'success' => '#12B76A',
        'danger' => '#F04438',
        'gray' => '#667085',
    ];

    $priorityClasses = [
        'danger' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
        'success' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
        'gray' => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
    ];

    $slaColors = [
        'FULFILLED' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
        'WARNING' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
        'BREACHED' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        'PAUSED' => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
    ];
@endphp

<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
        <x-metric-card label="Total Tickets" value="{{ $metrics['total'] }}" icon="ticket" color="brand" />
        <x-metric-card label="Open Tickets" value="{{ $metrics['open'] }}" icon="open" color="warning" />
        <x-metric-card label="Breached SLA" value="{{ $metrics['breached'] }}" icon="warning" color="error" />
        <x-metric-card label="Avg Resolution" value="{{ $metrics['avg_resolution'] }} hrs" icon="clock" color="success" />
    </div>

    <div class="mt-6 grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 xl:col-span-7">
            <div class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ticket Trend</h3>
                <div id="chartTrend" data-months="{{ json_encode($trend->keys()) }}" data-counts="{{ json_encode($trend->values()) }}" class="flex min-h-[300px] flex-col"></div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-5">
            <div class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Tickets by Status</h3>
                <div id="chartStatus" data-labels="{{ json_encode($ticketsByStatus->keys()->map(fn ($id) => $statuses[$id]?->name ?? 'Unknown')) }}" data-counts="{{ json_encode($ticketsByStatus->values()) }}" data-colors="{{ json_encode($ticketsByStatus->keys()->map(fn ($id) => $statusColors[$statuses[$id]?->color ?? 'gray'] ?? $statusColors['gray'])) }}" class="flex min-h-[300px] flex-col"></div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Top Categories</h3>
            <div id="chartCategory" data-labels="{{ json_encode($ticketsByCategory->keys()->map(fn ($id) => $categoryNames[$id] ?? 'Unknown')) }}" data-counts="{{ json_encode($ticketsByCategory->values()) }}" class="flex min-h-[280px] flex-col"></div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Top Departments</h3>
            <div id="chartDepartment" data-labels="{{ json_encode($ticketsByDepartment->keys()->map(fn ($id) => $departmentNames[$id] ?? 'Unknown')) }}" data-counts="{{ json_encode($ticketsByDepartment->values()) }}" class="flex min-h-[280px] flex-col"></div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Top Requesters</h3>
            <div id="chartRequester" data-labels="{{ json_encode($ticketsByUser->keys()->map(fn ($id) => $userNames[$id] ?? 'Unknown')) }}" data-counts="{{ json_encode($ticketsByUser->values()) }}" class="flex min-h-[280px] flex-col"></div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-3 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">System Info</h3>
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex justify-between"><span>Total Users</span><span class="font-medium text-gray-900 dark:text-white">{{ $systemInfo['total_users'] }}</span></div>
                <div class="flex justify-between"><span>Total Assets</span><span class="font-medium text-gray-900 dark:text-white">{{ $systemInfo['total_assets'] }}</span></div>
                <div class="flex justify-between"><span>Last Sync</span><span class="font-medium text-gray-900 dark:text-white">{{ $systemInfo['last_sync'] }}</span></div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-2">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">IT Staff Workload</h3>
            <div class="space-y-3">
                @forelse($workload as $row)
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $itStaffNames[$row->assigned_to_id] ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Assigned tickets</p>
                        </div>
                        <span class="rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $row->count }}</span>
                    </div>
                @empty
                    <x-empty-state icon="user" title="No workload data" description="Assigned ticket data will appear here." />
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2 md:gap-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Unassigned Tickets</h3>
            </div>
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($recentTickets->whereNull('assigned_to_id') as $ticket)
                            <tr>
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">#{{ $ticket->request_id }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->requester_name }}</p>
                                </td>
                                <td class="py-3 pr-4 text-sm text-gray-500 dark:text-gray-400">{{ $ticket->category?->name }}</td>
                                <td class="py-3 text-right">
                                    <span class="rounded-full bg-warning-50 px-2 py-0.5 text-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">Open</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Problem Assets</h3>
            </div>
            <div class="space-y-3">
                @forelse($problematicAssets as $row)
                    @php $asset = $assetDetails[$row->asset_id] ?? null; @endphp
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-100 p-3 dark:border-gray-800">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $asset?->asset_tag ?? 'Unknown Asset' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $asset?->device_name ?? $asset?->product_number ?? 'No device name' }}</p>
                        </div>
                        <span class="rounded-full bg-error-50 px-3 py-1 text-sm font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">{{ $row->count }}</span>
                    </div>
                @empty
                    <x-empty-state icon="ticket" title="No asset ticket data" description="Asset issue frequency will appear here." />
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Tickets</h3>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-y border-gray-100 dark:border-gray-800">
                        <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">ID</span></th>
                        <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requester</span></th>
                        <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Category</span></th>
                        <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Priority</span></th>
                        <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</span></th>
                        <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">SLA</span></th>
                        <th class="py-3 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Created</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="py-3 pr-4"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">#{{ $ticket->request_id }}</p></td>
                            <td class="py-3 pr-4"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->requester_name }}</p></td>
                            <td class="py-3 pr-4"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->category?->name }}</p></td>
                            <td class="py-3 pr-4"><span class="rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $priorityClasses[$ticket->priority?->color] ?? $priorityClasses['gray'] }}">{{ $ticket->priority?->name }}</span></td>
                            <td class="py-3 pr-4"><span class="rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $statusColors[$ticket->status?->color] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400' }}">{{ $ticket->status?->name }}</span></td>
                            <td class="py-3 pr-4"><span class="rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $slaColors[$ticket->slaStatus?->code ?? 'FULFILLED'] }}">{{ $ticket->slaStatus?->name ?? 'Fulfilled' }}</span></td>
                            <td class="py-3"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->created_at->format('M d, H:i') }}</p></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8"><x-empty-state icon="ticket" title="No tickets yet" description="Start by creating first ticket. Metrics, charts, and activity will appear here." action-label="Create Ticket" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
