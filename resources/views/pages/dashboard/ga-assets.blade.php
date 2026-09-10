@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">General Affair & Facilities Dashboard</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Management of GA physical assets, vehicle fleet, room reservations, and facility maintenance.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('ga-assets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.97] transition-all duration-150 ease-out">
                <i data-lucide="plus" class="w-4 h-4"></i> Add GA Asset
            </a>
            <a href="{{ route('ga-assets.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
                <i data-lucide="briefcase" class="w-4 h-4"></i> Inventory List
            </a>
        </div>
    </div>

    <!-- 1. Core GA KPI Cards Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-common.stat-card
            title="Total GA Assets"
            icon="hard-drive"
            :value="number_format($totalAssets)"
            description="Total physical GA assets"
        />

        <x-common.stat-card
            title="Total Capital Value"
            icon="dollar-sign"
            value="Rp {{ number_format($totalAssetValue, 0, ',', '.') }}"
            description="Capitalized valuation"
        />

        <x-common.stat-card
            title="Active Assignments"
            icon="user-check"
            :value="number_format($activeAssignments)"
            description="Assets currently checked out"
        />

        <x-common.stat-card
            title="Pending Maintenance"
            icon="wrench"
            :value="number_format($pendingMaintenance)"
            description="Scheduled repairs"
        />
    </div>

    <!-- 2. Fleet & Facilities KPI Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-common.stat-card
            title="Vehicles Fleet"
            icon="truck"
            :value="number_format($totalVehicles)"
            description="Cars, vans & delivery bikes"
        />

        <x-common.stat-card
            title="Tax Due Alerts"
            icon="alert-triangle"
            :value="number_format($upcomingVehicleTaxes)"
            :trend="1"
            trendColor="warning"
            description="Vehicle taxes expiring in 30 days"
        />

        <x-common.stat-card
            title="Active Reservations"
            icon="calendar"
            :value="number_format($activeReservations)"
            description="Room & vehicle schedules"
        />

        <x-common.stat-card
            title="Maintenance Spend"
            icon="activity"
            value="Rp {{ number_format($monthlyMaintenanceSpend, 0, ',', '.') }}"
            description="Total maintenance cost this month"
        />
    </div>

    <!-- 3. GA Charts Grid (3 Donut Charts) -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Status Breakdown</h3>
                <p class="text-xs text-gray-400">GA Asset availability</p>
            </div>
            <div id="gaStatusChart" class="h-64 flex items-center justify-center"></div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Assets by Category</h3>
                <p class="text-xs text-gray-400">Facilities vs Furniture</p>
            </div>
            <div id="gaCategoryChart" class="h-64 flex items-center justify-center"></div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 md:col-span-2 xl:col-span-1">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Asset Condition</h3>
                <p class="text-xs text-gray-400">Physical rating</p>
            </div>
            <div id="gaConditionChart" class="h-64 flex items-center justify-center"></div>
        </div>
    </div>

    <!-- 4. Spend & Maintenance Timeline Charts -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Investment Spend Timeline</h3>
                <p class="text-xs text-gray-400">Monthly expenditure</p>
            </div>
            <div id="gaSpendTimelineChart" class="h-64"></div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Maintenance Cost by Category</h3>
                <p class="text-xs text-gray-400">Repair expenditure</p>
            </div>
            <div id="gaMaintCategoryChart" class="h-64"></div>
        </div>
    </div>

    <!-- 5. Data Tables Grid (2x2) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-common.dashboard-table
                title="Recent Assignments"
                description="Latest GA asset assignments to staff"
                :headers="['Asset', 'Assigned To', 'Date', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="View GA Inventory"
            >
                @forelse($recentAssignments as $assignment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $assignment->asset->name ?? 'Unknown' }}</span>
                            <span class="block text-[11px] text-gray-400">{{ $assignment->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-700 dark:text-gray-300">
                            {{ $assignment->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-4 py-2.5">
                            @if($assignment->returned_at)
                                <x-ui.badge color="success" variant="light" size="sm" featherIcon="rotate-ccw">Returned</x-ui.badge>
                            @else
                                <x-ui.badge color="primary" variant="light" size="sm" featherIcon="user-check">Active</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No recent assignments found</td></tr>
                @endforelse
            </x-common.dashboard-table>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-common.dashboard-table
                title="Recent Maintenance Logs"
                description="Latest scheduled repairs & servicing"
                :headers="['Asset', 'Scheduled', 'Cost', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="View GA Maintenance"
            >
                @forelse($recentMaintenance as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $log->asset->name ?? 'Unknown' }}</span>
                            <span class="block text-[11px] text-gray-400">{{ $log->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->scheduled_date ? $log->scheduled_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white whitespace-nowrap">Rp {{ number_format($log->cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5">
                            @if($log->completed_date)
                                <x-ui.badge color="success" variant="light" size="sm" featherIcon="check-circle">Completed</x-ui.badge>
                            @else
                                <x-ui.badge color="warning" variant="light" size="sm" featherIcon="clock">Scheduled</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No maintenance logs found</td></tr>
                @endforelse
            </x-common.dashboard-table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
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
            red: '#ef4444',
        };

        const formatRupiah = (val) => 'Rp ' + Number(val).toLocaleString('id-ID');

        const createDoughnutConfig = (labels, data, colors, centerLabel = 'Total') => ({
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    centerText: {
                        label: centerLabel,
                        value: data.reduce((a, b) => Number(a) + Number(b), 0)
                    },
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, pointStyle: 'circle', font: { family: 'Outfit, sans-serif', size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                        bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                        padding: { top: 8, bottom: 8, left: 12, right: 12 },
                        cornerRadius: 8
                    }
                }
            }
        });

        // 1. Status Donut Chart
        const statusCanvas = mountCanvas("#gaStatusChart");
        if (statusCanvas && @json($statusChart['data'])?.length) {
            new Chart(statusCanvas, createDoughnutConfig(
                @json($statusChart['labels']),
                @json($statusChart['data']),
                @json($statusChart['colors']) || [chartColors.green, chartColors.blue, chartColors.orange, chartColors.red],
                'Status'
            ));
        }

        // 2. Category Donut Chart
        const catCanvas = mountCanvas("#gaCategoryChart");
        if (catCanvas && @json($categoryChart['data'])?.length) {
            new Chart(catCanvas, createDoughnutConfig(
                @json($categoryChart['labels']),
                @json($categoryChart['data']),
                [chartColors.blue, chartColors.purple, chartColors.cyan, chartColors.green, chartColors.orange],
                'Categories'
            ));
        }

        // 3. Condition Donut Chart
        const condCanvas = mountCanvas("#gaConditionChart");
        if (condCanvas && @json($conditionChart['data'])?.length) {
            new Chart(condCanvas, createDoughnutConfig(
                @json($conditionChart['labels']),
                @json($conditionChart['data']),
                @json($conditionChart['colors']),
                'Condition'
            ));
        }

        // 4. Investment Spend Timeline Line Chart
        const purchaseCanvas = mountCanvas("#gaSpendTimelineChart");
        if (purchaseCanvas && @json($purchaseChart['data'])?.length) {
            new Chart(purchaseCanvas, {
                type: 'line',
                data: {
                    labels: @json($purchaseChart['labels']),
                    datasets: [{
                        label: 'Acquisition Spend',
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
                                label: (context) => ' Spend: ' + formatRupiah(context.raw)
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

        // 5. Maintenance Cost by Category Bar Chart
        const maintCatCanvas = mountCanvas("#gaMaintCategoryChart");
        if (maintCatCanvas && @json($maintCategoryChart['data'])?.length) {
            new Chart(maintCatCanvas, {
                type: 'bar',
                data: {
                    labels: @json($maintCategoryChart['labels']),
                    datasets: [{
                        label: 'Maintenance Cost',
                        data: @json($maintCategoryChart['data']),
                        backgroundColor: chartColors.blue,
                        borderRadius: 4
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
                                label: (context) => ' Cost: ' + formatRupiah(context.raw)
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

        if (window.createIcons && window.lucideIcons) {
            window.createIcons({ icons: window.lucideIcons });
        }
    });
</script>
@endpush
