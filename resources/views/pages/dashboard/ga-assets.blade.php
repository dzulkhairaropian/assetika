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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Chart 1: GA Asset Status Distribution (Donut)
        @if(count($statusChart['data']) > 0)
        const statusOptions = {
            series: @json($statusChart['data']),
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: 'donut',
                height: 320,
            },
            labels: @json($statusChart['labels']),
            colors: @json($statusChart['colors']),
            legend: {
                position: 'bottom',
                fontFamily: "Outfit",
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true },
                            value: {
                                show: true,
                                fontSize: '20px',
                                fontWeight: 'bold'
                            },
                            total: {
                                show: true,
                                label: 'Total Assets',
                                formatter: function (w) {
                                    return {{ $totalAssets }}
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false }
        };
        const statusChart = new ApexCharts(document.querySelector("#gaStatusChart"), statusOptions);
        statusChart.render();
        @endif

        // Chart 2: Assets by Category (Bar)
        @if(count($categoryChart['data']) > 0)
        const categoryOptions = {
            series: [{
                name: 'Total Qty',
                data: @json($categoryChart['data'])
            }],
            chart: {
                type: 'bar',
                height: 320,
                fontFamily: "Outfit, sans-serif",
                toolbar: { show: false }
            },
            colors: ['#465FFF'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    horizontal: false,
                    columnWidth: '40%',
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($categoryChart['labels']),
                labels: { style: { fontFamily: 'Outfit' } }
            },
            yaxis: {
                labels: { style: { fontFamily: 'Outfit' } }
            }
        };
        const categoryChart = new ApexCharts(document.querySelector("#gaCategoryChart"), categoryOptions);
        categoryChart.render();
        @endif

        // Chart 3: Spend Timeline (Line/Area)
        @if(count($purchaseChart['data']) > 0)
        const purchaseOptions = {
            series: [{
                name: 'Investment Value',
                data: @json($purchaseChart['data'])
            }],
            chart: {
                type: 'area',
                height: 320,
                fontFamily: "Outfit, sans-serif",
                toolbar: { show: false }
            },
            colors: ['#12B76A'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: @json($purchaseChart['labels']),
                labels: { style: { fontFamily: 'Outfit' } }
            },
            yaxis: {
                labels: {
                    style: { fontFamily: 'Outfit' },
                    formatter: function (value) {
                        return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            }
        };
        const purchaseChart = new ApexCharts(document.querySelector("#gaSpendTimelineChart"), purchaseOptions);
        purchaseChart.render();
        @endif
    });
</script>
@endif
@endpush
