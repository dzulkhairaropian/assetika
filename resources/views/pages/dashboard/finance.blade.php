@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Financial & Valuation Control Panel</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Real-time asset valuation, capital spend trends, and straight-line depreciation metrics.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('assets.export') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
                <i data-lucide="download" class="w-4 h-4"></i> Export Valuation
            </a>
            <a href="{{ route('depreciations.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.97] transition-all duration-150 ease-out">
                <i data-lucide="settings" class="w-4 h-4"></i> Depreciation Config
            </a>
        </div>
    </div>

    <!-- 1. Key Financial KPI Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-common.small-box
            title="Total Purchase Cost"
            icon="dollar-sign"
            value="Rp {{ number_format($totalPurchaseCost, 0, ',', '.') }}"
            description="Initial acquisition cost of all assets"
            color="brand"
            :url="route('assets.index')"
        />

        <x-common.small-box
            title="Current Book Value"
            icon="trending-up"
            value="Rp {{ number_format($totalBookValue, 0, ',', '.') }}"
            description="Remaining net value after depreciation"
            color="success"
            :url="route('depreciations.index')"
        />

        <x-common.small-box
            title="Total Depreciated"
            icon="trending-down"
            value="Rp {{ number_format($totalDepreciated, 0, ',', '.') }}"
            description="Accumulated value loss to date"
            color="danger"
            :url="route('depreciations.index')"
        />

        <x-common.small-box
            title="Avg Asset Cost"
            icon="pie-chart"
            value="Rp {{ number_format($avgAssetCost, 0, ',', '.') }}"
            description="Average cost per registered unit"
            color="info"
            :url="route('assets.index')"
        />
    </div>

    <!-- 2. Financial Analytics Charts Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- Spend Timeline Chart (Spans 2 columns) -->
        <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Asset Acquisition Spend Timeline</h3>
                    <p class="text-xs text-gray-400">Monthly capital expenditure on IT assets</p>
                </div>

                <!-- Year Selector -->
                <div class="relative shrink-0" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                    <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                        <i data-lucide="calendar" class="w-3 h-3 text-brand-500"></i>
                        <span>Year: {{ $selectedYear }}</span>
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </button>
                    <div x-show="dropdownOpen" class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-50 flex flex-col gap-0.5 w-max max-h-56 overflow-y-auto" style="display: none;">
                        @foreach($purchaseYears as $year)
                            <a href="{{ request()->fullUrlWithQuery(['purchase_year' => $year]) }}"
                               class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ (string) $selectedYear === (string) $year ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 font-bold' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400' }}">
                                {{ $year }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            @if(array_sum($spendTimelineChartData['data']) > 0)
                <div id="financeSpendTimelineChart" class="h-64"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No purchase spend data for {{ $selectedYear }}</div>
            @endif
        </div>

        <!-- Cost by Category (Donut) -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Cost by Category</h3>
                <p class="text-xs text-gray-400">Top 5 categories by initial spend</p>
            </div>
            @if(array_sum($categoryChartData['data']) > 0)
                <div id="financeCategoryChart" class="h-64 flex items-center justify-center"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No category spend data available</div>
            @endif
        </div>

        <!-- Cost by Department (Donut - Full width below or span) -->
        <div class="lg:col-span-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Asset Valuation by Department</h3>
                <p class="text-xs text-gray-400">Top departments by total allocated asset capital</p>
            </div>
            @if(array_sum($departmentChartData['data']) > 0)
                <div id="financeDeptChart" class="h-64 flex items-center justify-center"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No department spend data available</div>
            @endif
        </div>

    </div>

    <!-- 3. Asset Valuation Data Tables -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-common.dashboard-table
                title="Highest Value Assets"
                description="Top IT assets by initial purchase cost"
                :headers="['Asset Code / Name', 'Category', 'Purchase Cost', 'Book Value']"
                :footerLink="route('assets.index')"
                footerText="View All Assets"
            >
                @forelse($recentHighValueAssets as $asset)
                    <tr x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $asset->asset_code ?? $asset->asset_tag ?? $asset->name }}</span>
                            <span class="block text-[11px] text-gray-400">{{ optional(optional($asset->product)->brand)->name ?? '-' }} • {{ optional($asset->product)->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                            {{ optional(optional($asset->product)->category)->name ?? 'Uncategorized' }}
                        </td>
                        <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                            Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            Rp {{ number_format($asset->book_value ?? $asset->purchase_cost, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No asset cost data available</td></tr>
                @endforelse
            </x-common.dashboard-table>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-common.dashboard-table
                title="Fully Depreciated Assets"
                description="Assets with zero remaining book value"
                :headers="['Asset Code / Name', 'Purchase Date', 'Cost', 'Status']"
                :footerLink="route('assets.index')"
                footerText="View All Assets"
            >
                @forelse($fullyDepreciatedAssets as $asset)
                    <tr x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $asset->asset_code ?? $asset->asset_tag ?? $asset->name }}</span>
                            <span class="block text-[11px] text-gray-400">{{ optional(optional($asset->product)->brand)->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            {{ $asset->purchase_date ? $asset->purchase_date->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge color="danger" variant="light" size="sm" featherIcon="alert-circle">Fully Depreciated</x-ui.badge>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No fully depreciated assets found</td></tr>
                @endforelse
            </x-common.dashboard-table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    window.financeChartData = {
        spendTimeline: @json($spendTimelineChartData),
        categoryData: @json($categoryChartData),
        departmentData: @json($departmentChartData),
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { spendTimeline, categoryData, departmentData } = window.financeChartData || {};

        const chartColors = {
            blue: '#3b82f6',
            green: '#22c55e',
            orange: '#f97316',
            purple: '#8b5cf6',
            cyan: '#06b6d4',
        };

        const formatRupiah = (val) => 'Rp ' + Number(val).toLocaleString('id-ID');

        // 1. Spend Timeline Line Chart
        const timelineCanvas = document.querySelector('#financeSpendTimelineChart');
        if (timelineCanvas && spendTimeline && spendTimeline.data?.length) {
            timelineCanvas.innerHTML = '<canvas></canvas>';
            const ctx = timelineCanvas.querySelector('canvas');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: spendTimeline.labels,
                    datasets: [{
                        label: 'Acquisition Spend',
                        data: spendTimeline.data,
                        borderColor: chartColors.blue,
                        borderWidth: 2,
                        tension: 0,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: chartColors.blue,
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
                            padding: { top: 8, bottom: 8, left: 12, right: 12 },
                            cornerRadius: 8,
                            callbacks: {
                                label: (ctx) => 'Spend: ' + formatRupiah(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { family: 'Outfit, sans-serif', size: 11 } } },
                        y: {
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

        // 2. Category Donut Chart
        const catCanvas = document.querySelector('#financeCategoryChart');
        if (catCanvas && categoryData && categoryData.data?.length) {
            catCanvas.innerHTML = '<canvas></canvas>';
            const ctx = catCanvas.querySelector('canvas');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.labels,
                    datasets: [{
                        data: categoryData.data,
                        backgroundColor: [chartColors.blue, chartColors.green, chartColors.orange, chartColors.purple, chartColors.cyan],
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
                        tooltip: {
                            backgroundColor: '#0f172a',
                            cornerRadius: 8,
                            callbacks: { label: (ctx) => ctx.label + ': ' + formatRupiah(ctx.raw) }
                        }
                    }
                }
            });
        }

        // 3. Department Donut Chart
        const deptCanvas = document.querySelector('#financeDeptChart');
        if (deptCanvas && departmentData && departmentData.data?.length) {
            deptCanvas.innerHTML = '<canvas></canvas>';
            const ctx = deptCanvas.querySelector('canvas');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: departmentData.labels,
                    datasets: [{
                        data: departmentData.data,
                        backgroundColor: [chartColors.purple, chartColors.blue, chartColors.cyan, chartColors.green, chartColors.orange],
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
                        tooltip: {
                            backgroundColor: '#0f172a',
                            cornerRadius: 8,
                            callbacks: { label: (ctx) => ctx.label + ': ' + formatRupiah(ctx.raw) }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
