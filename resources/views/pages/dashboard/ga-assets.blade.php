@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <x-common.page-breadcrumb pageTitle="General Affairs Dashboard" />
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-gray-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                        Hi, {{ $userName }}! 👋
                        <span class="block text-sm font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $randomGreeting }}</span>
                    </h2>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('ga-assets.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="plus" class="w-4 h-4"></i> Add GA Asset
                    </a>
                    <a href="{{ route('ga-assets.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-750 transition">
                        <i data-feather="briefcase" class="w-4 h-4"></i> GA Inventory
                    </a>
                </div>
            </div>
        </div>

        <!-- Metrics Widgets -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            <x-common.stat-card
                title="Total GA Assets"
                :value="number_format($totalAssets)"
                description="Total physical GA assets in inventory"
            />

            <x-common.stat-card
                title="Total Asset Value"
                value="Rp {{ number_format($totalAssetValue, 0, ',', '.') }}"
                description="Capitalized investment value for GA"
            />

            <x-common.stat-card
                title="Active Assignments"
                :value="number_format($activeAssignments)"
                description="Assets currently checked out or in use"
            />

            <x-common.stat-card
                title="Pending Maintenance"
                :value="number_format($pendingMaintenance)"
                description="Scheduled repairs and maintenance tasks"
            />
        </div>

        <!-- Charts Row 1 -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Asset Status Distribution</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Proportional breakdown of current asset statuses</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                @if($totalAssets > 0 && count($statusChart['data']) > 0)
                    <div id="gaStatusChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No asset status data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Assets by Category</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Total number of assets grouped by category</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                @if(count($categoryChart['data']) > 0)
                    <div id="gaCategoryChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No asset category data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Charts Row 2: Spend Timeline -->
        <div class="col-span-12">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">GA Asset Investment Timeline (by Purchase Date)</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Monthly spend timeline tracking historical GA asset acquisitions</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                @if(count($purchaseChart['data']) > 0)
                    <div id="gaSpendTimelineChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No purchase timeline data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activities Tables -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <!-- Recent Assignments -->
            <x-common.dashboard-table
                title="Recent Assignments"
                description="Latest GA asset assignments and their current status"
                :headers="['Asset', 'Assigned To', 'Date', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="View GA Inventory"
            >
                @forelse($recentAssignments as $assignment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-5 py-3 sm:px-6">
                            <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $assignment->asset->name ?? 'Unknown' }}</span>
                            <span class="block text-gray-500 text-theme-xs dark:text-gray-400 mt-0.5">{{ $assignment->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-5 py-3 sm:px-6">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-semibold text-xs shrink-0">{{ strtoupper(substr($assignment->user->name ?? 'N', 0, 1)) }}</div>
                                <span class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $assignment->user->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-5 py-3 sm:px-6">
                            @if($assignment->returned_at)
                                <x-ui.badge color="success" variant="light" size="sm">Returned</x-ui.badge>
                            @else
                                <x-ui.badge color="primary" variant="light" size="sm">Active</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 sm:px-6 text-center text-sm text-gray-500 dark:text-gray-400">No recent assignments found</td></tr>
                @endforelse
            </x-common.dashboard-table>

            <!-- Recent Maintenance Logs -->
            <x-common.dashboard-table
                title="Recent Maintenance"
                description="Latest scheduled repairs and maintenance activities"
                :headers="['Asset', 'Scheduled', 'Cost', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="View GA Maintenance"
            >
                @forelse($recentMaintenance as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-5 py-3 sm:px-6">
                            <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $log->asset->name ?? 'Unknown' }}</span>
                            <span class="block text-gray-500 text-theme-xs dark:text-gray-400 mt-0.5">{{ $log->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $log->scheduled_date ? $log->scheduled_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-5 py-3 sm:px-6 text-gray-800 text-theme-sm dark:text-white/90 font-semibold">Rp {{ number_format($log->cost, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 sm:px-6">
                            @if($log->completed_date)
                                <x-ui.badge color="success" variant="light" size="sm">Completed</x-ui.badge>
                            @else
                                <x-ui.badge color="warning" variant="light" size="sm">Scheduled</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 sm:px-6 text-center text-sm text-gray-500 dark:text-gray-400">No maintenance logs found</td></tr>
                @endforelse
            </x-common.dashboard-table>
        </div>

    </div>
@endsection

@push('scripts')
@if($totalAssets > 0)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mountCanvas = (selector) => {
            const container = document.querySelector(selector);
            if (!container) return null;
            container.innerHTML = '<canvas></canvas>';
            return container.querySelector('canvas');
        };

        // Chart 1: GA Asset Status Distribution (Doughnut)
        @if(count($statusChart['data']) > 0)
        const statusCanvas = mountCanvas("#gaStatusChart");
        if (statusCanvas) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: @json($statusChart['labels']),
                    datasets: [{
                        data: @json($statusChart['data']),
                        backgroundColor: @json($statusChart['colors']),
                        borderWidth: 2,
                        borderColor: 'transparent'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    onHover: (event, activeElements, chart) => {
                        chart.draw();
                    },
                    plugins: {
                        centerText: {
                            label: 'Total Assets',
                            value: {{ $totalAssets }}
                        },
                        legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, font: { family: 'Outfit, sans-serif' } } }
                    }
                }
            });
        }
        @endif

        // Chart 2: Assets by Category (Bar)
        @if(count($categoryChart['data']) > 0)
        const catCanvas = mountCanvas("#gaCategoryChart");
        if (catCanvas) {
            new Chart(catCanvas, {
                type: 'bar',
                data: {
                    labels: @json($categoryChart['labels']),
                    datasets: [{
                        label: 'Total Qty',
                        data: @json($categoryChart['data']),
                        backgroundColor: '#465fff',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { border: { dash: [4, 4] }, grid: { color: 'rgba(226, 232, 240, 0.6)' }, ticks: { precision: 0 } }
                    }
                }
            });
        }
        @endif

        // Chart 3: Spend Timeline (Crisp Sharp Line)
        @if(count($purchaseChart['data']) > 0)
        const purchaseCanvas = mountCanvas("#gaSpendTimelineChart");
        if (purchaseCanvas) {
            new Chart(purchaseCanvas, {
                type: 'line',
                data: {
                    labels: @json($purchaseChart['labels']),
                    datasets: [{
                        label: 'Investment Value',
                        data: @json($purchaseChart['data']),
                        fill: false,
                        borderColor: '#12b76a',
                        borderWidth: 2,
                        tension: 0,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#12b76a',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#12b76a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Outfit, sans-serif' },
                            bodyFont: { family: 'Outfit, sans-serif' },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: (context) => ' Rp ' + Number(context.raw).toLocaleString('id-ID')
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, border: { display: true, color: '#e2e8f0' } },
                        y: {
                            border: { dash: [4, 4], display: false },
                            grid: { color: 'rgba(226, 232, 240, 0.6)' },
                            ticks: {
                                callback: (val) => 'Rp ' + (val / 1000000).toFixed(0) + 'M'
                            }
                        }
                    }
                }
            });
        }
        @endif
    });
</script>
@endif
@endpush
