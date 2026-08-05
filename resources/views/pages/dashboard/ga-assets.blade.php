@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6">

        <!-- Dashboard Header -->
        <div class="col-span-12">
            <x-common.page-breadcrumb pageTitle="General Affairs Dashboard" />
        </div>

        <!-- Metrics Widgets -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-4 md:gap-6">
            <!-- Total GA Assets -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-end justify-between mt-0">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total GA Assets</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($totalAssets) }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <i data-feather="trending-up" class="w-3 h-3"></i>
                        +2.5%
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Total physical GA assets in inventory</p>
            </div>

            <!-- Total Investment Value -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-end justify-between mt-0">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Asset Value</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <i data-feather="trending-up" class="w-3 h-3"></i>
                        +2.5%
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Capitalized investment value for GA</p>
            </div>

            <!-- Active Assignments -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-end justify-between mt-0">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Active Assignments</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($activeAssignments) }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <i data-feather="trending-up" class="w-3 h-3"></i>
                        +8.1%
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Assets currently checked out or in use</p>
            </div>

            <!-- Pending Maintenance -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-end justify-between mt-0">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Pending Maintenance</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($pendingMaintenance) }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-error-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                        <i data-feather="trending-down" class="w-3 h-3"></i>
                        -5%
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Scheduled repairs and maintenance tasks</p>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Asset Status Distribution</h3>
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

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Assets by Category</h3>
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
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">GA Asset Investment Timeline (by Purchase Date)</h3>
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
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
            
            <!-- Recent Assignments -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 pt-5 sm:px-6 mb-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Assignments</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Latest GA asset assignments and their current status</p>
                </div>
                <div class="max-w-full overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Assigned To</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($recentAssignments as $assignment)
                                <tr>
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
                                            <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">Returned</span>
                                        @else
                                            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 sm:px-6 text-center text-sm text-gray-500 dark:text-gray-400">No recent assignments found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Maintenance Logs -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 pt-5 sm:px-6 mb-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Maintenance</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Latest scheduled repairs and maintenance activities</p>
                </div>
                <div class="max-w-full overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Scheduled</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Cost</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($recentMaintenance as $log)
                                <tr>
                                    <td class="px-5 py-3 sm:px-6">
                                        <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $log->asset->name ?? 'Unknown' }}</span>
                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400 mt-0.5">{{ $log->asset->asset_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $log->scheduled_date ? $log->scheduled_date->format('M d, Y') : 'N/A' }}</td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-800 text-theme-sm dark:text-white/90 font-semibold">Rp {{ number_format($log->cost, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 sm:px-6">
                                        @if($log->completed_date)
                                            <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">Completed</span>
                                        @else
                                            <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-orange-400">Scheduled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 sm:px-6 text-center text-sm text-gray-500 dark:text-gray-400">No maintenance logs found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

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
            dataLabels: {
                enabled: false
            }
        };
        const statusChart = new ApexCharts(document.querySelector("#gaStatusChart"), statusOptions);
        statusChart.render();
        @endif

        // Chart 2: GA Assets by Category (Pie/Donut)
        @if(count($categoryChart['data']) > 0)
        const categoryOptions = {
            series: @json($categoryChart['data']),
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: 'pie',
                height: 320,
            },
            labels: @json($categoryChart['labels']),
            legend: {
                position: 'bottom',
                fontFamily: "Outfit",
            },
            dataLabels: {
                enabled: true
            }
        };
        const categoryChart = new ApexCharts(document.querySelector("#gaCategoryChart"), categoryOptions);
        categoryChart.render();
        @endif

        // Chart 3: General Affairs Spend Timeline (Area Chart)
        @if(count($purchaseChart['data']) > 0)
        const spendTimelineOptions = {
            series: [{
                name: 'Total Spend',
                data: @json($purchaseChart['data'])
            }],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: 'area',
                height: 320,
                toolbar: { show: false },
            },
            colors: ['#465FFF'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($purchaseChart['labels']),
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                    }
                }
            }
        };
        const spendTimelineChart = new ApexCharts(document.querySelector("#gaSpendTimelineChart"), spendTimelineOptions);
        spendTimelineChart.render();
        @endif
    });
</script>
@endif
@endpush
