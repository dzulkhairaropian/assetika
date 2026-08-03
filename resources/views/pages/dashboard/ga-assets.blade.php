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
                <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </div>
                <div class="flex items-end justify-between mt-5">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total GA Assets</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($totalAssets) }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <svg class="fill-current" width="10" height="10" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62393C5.70193 1.47072 5.90135 1.37432 6.12329 1.37432C6.1236 1.37432 6.12391 1.37432 6.12422 1.37432C6.31631 1.37415 6.50845 1.44731 6.65505 1.59381L9.65514 4.5918C9.94814 4.88459 9.94831 5.35947 9.65552 5.65246C9.36273 5.94546 8.88785 5.94562 8.59486 5.65283L6.87329 3.93247L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93578L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65248C2.3017 5.35949 2.30185 4.88462 2.59484 4.59182L5.56462 1.62393Z" fill="currentColor"/>
                        </svg>
                        +2.5%
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Total physical GA assets in inventory</p>
            </div>

            <!-- Total Investment Value -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <div class="flex items-end justify-between mt-5">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Asset Value</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <svg class="fill-current" width="10" height="10" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62393C5.70193 1.47072 5.90135 1.37432 6.12329 1.37432C6.1236 1.37432 6.12391 1.37432 6.12422 1.37432C6.31631 1.37415 6.50845 1.44731 6.65505 1.59381L9.65514 4.5918C9.94814 4.88459 9.94831 5.35947 9.65552 5.65246C9.36273 5.94546 8.88785 5.94562 8.59486 5.65283L6.87329 3.93247L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93578L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65248C2.3017 5.35949 2.30185 4.88462 2.59484 4.59182L5.56462 1.62393Z" fill="currentColor"/>
                        </svg>
                        +12.4%
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Capitalized investment value for GA</p>
            </div>

            <!-- Active Assignments -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                </div>
                <div class="flex items-end justify-between mt-5">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Active Assignments</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($activeAssignments) }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <svg class="fill-current" width="10" height="10" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62393C5.70193 1.47072 5.90135 1.37432 6.12329 1.37432C6.1236 1.37432 6.12391 1.37432 6.12422 1.37432C6.31631 1.37415 6.50845 1.44731 6.65505 1.59381L9.65514 4.5918C9.94814 4.88459 9.94831 5.35947 9.65552 5.65246C9.36273 5.94546 8.88785 5.94562 8.59486 5.65283L6.87329 3.93247L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93578L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65248C2.3017 5.35949 2.30185 4.88462 2.59484 4.59182L5.56462 1.62393Z" fill="currentColor"/>
                        </svg>
                        +8.1%
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Assets currently checked out or in use</p>
            </div>

            <!-- Pending Maintenance -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                </div>
                <div class="flex items-end justify-between mt-5">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Pending Maintenance</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($pendingMaintenance) }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-error-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                        <svg class="fill-current" width="10" height="10" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.31462 10.3761C5.45194 10.5293 5.65136 10.6257 5.87329 10.6257C5.8736 10.6257 5.8739 10.6257 5.87421 10.6257C6.0663 10.6259 6.25845 10.5527 6.40505 10.4062L9.40514 7.4082C9.69814 7.11541 9.69831 6.64054 9.40552 6.34754C9.11273 6.05454 8.63785 6.05438 8.34486 6.34717L6.62329 8.06753L6.62329 1.875C6.62329 1.46079 6.28751 1.125 5.87329 1.125C5.45908 1.125 5.12329 1.46079 5.12329 1.875L5.12329 8.06422L3.40516 6.34719C3.11218 6.05439 2.6373 6.05454 2.3445 6.34752C2.0517 6.64051 2.05185 7.11538 2.34484 7.40818L5.31462 10.3761Z" fill="currentColor"/>
                        </svg>
                        -5%
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Scheduled repairs and maintenance tasks</p>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Chart 1: GA Asset Status Distribution -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 sm:px-6 sm:pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Asset Status Distribution</h3>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Proportional breakdown of current asset statuses</p>
                <div class="max-w-full overflow-x-auto custom-scrollbar mt-4">
                    @if($totalAssets > 0 && count($statusChart['data']) > 0)
                        <div id="gaStatusChart" class="h-80"></div>
                    @else
                        <div class="flex flex-col items-center justify-center h-80 text-center">
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No asset status data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chart 2: GA Assets by Category -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 sm:px-6 sm:pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Assets by Category</h3>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Total number of assets grouped by category</p>
                <div class="max-w-full overflow-x-auto custom-scrollbar mt-4">
                    @if(count($categoryChart['data']) > 0)
                        <div id="gaCategoryChart" class="h-80"></div>
                    @else
                        <div class="flex flex-col items-center justify-center h-80 text-center">
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No asset category data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Spend Timeline -->
        <div class="col-span-12">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    GA Asset Investment Timeline (by Purchase Date)
                </h3>
                <p class="mt-1 mb-4 text-theme-sm text-gray-500 dark:text-gray-400">Monthly spend timeline tracking historical GA asset acquisitions</p>
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
            <!-- Recent Assignments -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pb-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-4 px-5 pt-5 sm:px-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Assignments</h3>
                </div>
                <div class="max-w-full overflow-x-auto h-80 overflow-y-auto scrollbar-custom relative">
                    <table class="w-full">
                        <thead class="border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Asset</th>
                                <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Assigned To</th>
                                <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Date</th>
                                <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                            @forelse($recentAssignments as $assignment)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $assignment->asset->name ?? 'Unknown' }}</span>
                                            <span class="text-xs text-gray-500">{{ $assignment->asset->asset_code ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-bold text-xs">
                                                {{ strtoupper(substr($assignment->user->name ?? 'N', 0, 1)) }}
                                            </div>
                                            <span class="text-gray-700 text-theme-sm dark:text-gray-350 font-medium">{{ $assignment->user->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        @if($assignment->returned_at)
                                            <span class="inline-flex items-center rounded-md bg-success-550/10 px-2 py-1 text-xs font-semibold text-success-700 dark:text-success-400 dark:bg-success-500/15">Returned</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-brand-50 px-2 py-1 text-xs font-semibold text-brand-700 dark:text-brand-400 dark:bg-brand-500/15">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No recent assignments found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Maintenance Logs -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pb-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-4 px-5 pt-5 sm:px-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Maintenance</h3>
                </div>
                <div class="max-w-full overflow-x-auto h-80 overflow-y-auto scrollbar-custom relative">
                    <table class="w-full">
                        <thead class="border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Asset</th>
                                <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Scheduled</th>
                                <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Cost</th>
                                <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                            @forelse($recentMaintenance as $log)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $log->asset->name ?? 'Unknown' }}</span>
                                            <span class="text-xs text-gray-500">{{ $log->asset->asset_code ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $log->scheduled_date ? $log->scheduled_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-gray-700 text-theme-sm dark:text-gray-300 font-semibold">
                                        Rp {{ number_format($log->cost, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        @if($log->completed_date)
                                            <span class="inline-flex items-center rounded-md bg-success-500/10 px-2 py-1 text-xs font-semibold text-success-700 dark:text-success-400 dark:bg-success-500/15">Completed</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-warning-500/10 px-2 py-1 text-xs font-semibold text-warning-700 dark:text-orange-400 dark:bg-warning-500/15">Scheduled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No maintenance logs found</p>
                                    </td>
                                </tr>
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
