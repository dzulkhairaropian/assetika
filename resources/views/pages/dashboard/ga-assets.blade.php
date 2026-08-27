@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <x-common.page-breadcrumb pageTitle="General Affairs Dashboard" />
            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Hi, {{ $userName }}! 👋
                        <span class="block text-xs font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $randomGreeting }}</span>
                    </h2>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('ga-assets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="plus" class="w-3.5 h-3.5"></i> Add GA Asset
                    </a>
                    <a href="{{ route('ga-assets.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="briefcase" class="w-3.5 h-3.5"></i> GA Inventory
                    </a>
                </div>
            </div>
        </div>

        <!-- Metrics Widgets -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            <x-common.stat-card
                title="Total GA Assets"
                icon="hard-drive"
                :value="number_format($totalAssets)"
                description="Total physical GA assets in inventory"
            />

            <x-common.stat-card
                title="Total Asset Value"
                icon="dollar-sign"
                value="Rp {{ number_format($totalAssetValue, 0, ',', '.') }}"
                description="Capitalized investment value for GA"
            />

            <x-common.stat-card
                title="Active Assignments"
                icon="user-check"
                :value="number_format($activeAssignments)"
                description="Assets currently checked out or in use"
            />

            <x-common.stat-card
                title="Pending Maintenance"
                icon="wrench"
                :value="number_format($pendingMaintenance)"
                description="Scheduled repairs and maintenance tasks"
            />
        </div>

        <!-- Charts Row 1 -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Asset Status Distribution</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Proportional breakdown of current asset statuses</p>
                </div>
                @if($totalAssets > 0 && count($statusChart['data']) > 0)
                    <div id="gaStatusChart" class="h-64 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <p class="text-xs text-gray-400 font-medium">No asset status data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Assets by Category</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Total number of assets grouped by category</p>
                </div>
                @if(count($categoryChart['data']) > 0)
                    <div id="gaCategoryChart" class="h-64 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <p class="text-xs text-gray-400 font-medium">No asset category data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Charts Row 2: Spend Timeline -->
        <div class="col-span-12">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">GA Asset Investment Timeline</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Monthly spend timeline tracking historical acquisitions</p>
                </div>
                @if(count($purchaseChart['data']) > 0)
                    <div id="gaSpendTimelineChart" class="h-64"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <p class="text-xs text-gray-400 font-medium">No purchase timeline data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activities Tables -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <!-- Recent Assignments -->
            <x-common.dashboard-table
                title="Recent Assignments"
                description="Latest GA asset assignments"
                :headers="['Asset', 'Assigned To', 'Date', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="View GA Inventory"
            >
                @forelse($recentAssignments as $assignment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $assignment->asset->name ?? 'Unknown' }}</span>
                            <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $assignment->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-700 dark:text-gray-300">
                            {{ $assignment->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-4 py-2.5">
                            @if($assignment->returned_at)
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">Returned</span>
                            @else
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">Active</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No recent assignments found</td></tr>
                @endforelse
            </x-common.dashboard-table>

            <!-- Recent Maintenance Logs -->
            <x-common.dashboard-table
                title="Recent Maintenance"
                description="Latest scheduled repairs & maintenance"
                :headers="['Asset', 'Scheduled', 'Cost', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="View GA Maintenance"
            >
                @forelse($recentMaintenance as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $log->asset->name ?? 'Unknown' }}</span>
                            <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $log->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->scheduled_date ? $log->scheduled_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white whitespace-nowrap">Rp {{ number_format($log->cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5">
                            @if($log->completed_date)
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">Completed</span>
                            @else
                                <span class="rounded-full bg-orange-50 px-2 py-0.5 text-[11px] font-medium text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">Scheduled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No maintenance logs found</td></tr>
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

        const chartColors = {
            blue: '#3b82f6',
            green: '#22c55e',
            orange: '#f97316',
            purple: '#8b5cf6',
            cyan: '#06b6d4',
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
                        backgroundColor: [chartColors.blue, chartColors.green, chartColors.orange, chartColors.purple],
                        borderWidth: 2,
                        borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, pointStyle: 'circle', font: { family: 'Outfit, sans-serif', size: 11 } }
                        },
                        tooltip: { backgroundColor: '#0f172a', cornerRadius: 8 }
                    }
                }
            });
        }
        @endif

        // Chart 2: GA Assets by Category (Bar)
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
                        backgroundColor: chartColors.blue,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { family: 'Outfit, sans-serif', size: 11 } } },
                        y: { border: { display: false }, grid: { color: 'rgba(226, 232, 240, 0.6)', dash: [4, 4] }, ticks: { precision: 0 } }
                    }
                }
            });
        }
        @endif

        // Chart 3: Spend Timeline (Linear Line)
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
                        borderColor: chartColors.green,
                        borderWidth: 2,
                        tension: 0,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: chartColors.green,
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            cornerRadius: 8,
                            callbacks: {
                                label: (context) => ' Rp ' + Number(context.raw).toLocaleString('id-ID')
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { family: 'Outfit, sans-serif', size: 11 } } },
                        y: {
                            border: { display: false },
                            grid: { color: 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                            ticks: {
                                font: { family: 'Outfit, sans-serif', size: 11 },
                                callback: (val) => 'Rp ' + (val >= 1000000 ? (val / 1000000) + 'M' : val)
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
