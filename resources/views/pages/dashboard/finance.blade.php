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

    <!-- Critical Attention Center -->
    @php
        $fullyDepreciatedCount = $fullyDepreciatedAssets->count();
        $finHasAlerts = $fullyDepreciatedCount > 0;
    @endphp

    @if($finHasAlerts)
        <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50/80 via-white to-red-50/60 p-4 shadow-sm dark:border-amber-900/40 dark:from-amber-950/20 dark:via-gray-900 dark:to-red-950/20">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Action Required</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Items requiring financial & valuation audit</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('assets.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-100/80 px-3 py-1.5 text-xs font-bold text-red-800 shadow-xs hover:bg-red-200 dark:border-red-900/60 dark:bg-red-950/70 dark:text-red-300 transition">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                        <span>{{ $fullyDepreciatedCount }} Fully Depreciated Asset{{ $fullyDepreciatedCount > 1 ? 's' : '' }}</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Bento Primary Row: Hero + High Priority Financial KPIs -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <!-- Hero Card (Spans 2 columns on lg) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-brand-500/30 dark:border-gray-800 dark:bg-gray-900 lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Financial Valuation</span>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Capital Fleet & Book Value</h2>
                    </div>
                    @php
                        $bookValueRatio = $totalPurchaseCost > 0 ? (int) round(($totalBookValue / $totalPurchaseCost) * 100) : 0;
                        $depreciatedRatio = max(0, 100 - $bookValueRatio);
                    @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        {{ $bookValueRatio }}% Retained Value
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap items-baseline gap-3">
                    <span class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Rp {{ number_format($totalPurchaseCost, 0, ',', '.') }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">total historical acquisition cost</span>
                </div>

                <!-- Multi-segment Financial Distribution Bar -->
                <div class="mt-4">
                    <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div style="width: {{ $bookValueRatio }}%" class="bg-emerald-500 transition-all duration-500" title="Book Value: Rp {{ number_format($totalBookValue, 0, ',', '.') }} ({{ $bookValueRatio }}%)"></div>
                        <div style="width: {{ $depreciatedRatio }}%" class="bg-red-500 transition-all duration-500" title="Depreciated: Rp {{ number_format($totalDepreciated, 0, ',', '.') }} ({{ $depreciatedRatio }}%)"></div>
                    </div>
                </div>
            </div>

            <!-- Segmented Indicators Legend -->
            <div class="mt-4 grid grid-cols-2 gap-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-[11px]">
                <a href="{{ route('depreciations.index') }}" class="group flex items-center justify-between hover:text-emerald-600 transition">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-gray-500 dark:text-gray-400">Current Book Value</span>
                    </div>
                    <strong class="text-gray-900 dark:text-white">Rp {{ number_format($totalBookValue, 0, ',', '.') }}</strong>
                </a>
                <a href="{{ route('depreciations.index') }}" class="group flex items-center justify-between hover:text-red-600 transition">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                        <span class="text-gray-500 dark:text-gray-400">Total Depreciated</span>
                    </div>
                    <strong class="text-gray-900 dark:text-white">Rp {{ number_format($totalDepreciated, 0, ',', '.') }}</strong>
                </a>
            </div>
        </div>

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
    </div>

    <!-- Secondary Operational KPI Row (4-column symmetry) -->
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
            title="Avg Asset Cost"
            icon="pie-chart"
            value="Rp {{ number_format($avgAssetCost, 0, ',', '.') }}"
            description="Average cost per registered unit"
            color="info"
            :url="route('assets.index')"
        />

        <x-common.small-box
            title="Fully Depreciated"
            icon="clock"
            :value="$fullyDepreciatedCount"
            :trend="$fullyDepreciatedCount > 0 ? 1 : 0"
            :trendColor="$fullyDepreciatedCount > 0 ? 'red' : 'emerald'"
            description="Units with zero remaining book value"
            color="warning"
            :url="route('assets.index')"
        />

        <x-common.small-box
            title="Depreciation Rate"
            icon="activity"
            value="{{ $depreciatedRatio }}%"
            description="Overall capital write-off ratio"
            color="info"
            :url="route('depreciations.index')"
        />
    </div>

    <!-- 2. Financial Analytics Charts Grid -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Cost by Category (Donut) -->
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between">
            <div class="p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Cost by Category</h3>
                <p class="text-xs text-gray-400">Top 5 categories by initial spend</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    @if(array_sum($categoryChartData['data']) > 0)
                        <div id="financeCategoryChart" class="h-48 w-full flex items-center justify-center"></div>
                    @else
                        <div class="flex items-center justify-center h-48 text-xs text-gray-400">No category spend data available</div>
                    @endif
                </div>

                @if(isset($categoryChartData['labels']) && count($categoryChartData['labels']) > 0)
                    @php $finColors = ['#465fff', '#12b76a', '#f79009', '#06b6d4', '#8b5cf6']; @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($categoryChartData['labels'] as $index => $label)
                            @php
                                $val = $categoryChartData['data'][$index] ?? 0;
                                $color = $finColors[$index % count($finColors)];
                            @endphp
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">(Rp {{ number_format($val, 0, ',', '.') }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Cost by Department (Donut) -->
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between">
            <div class="p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Valuation by Department</h3>
                <p class="text-xs text-gray-400">Top departments by asset allocation capital</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    @if(array_sum($departmentChartData['data']) > 0)
                        <div id="financeDeptChart" class="h-48 w-full flex items-center justify-center"></div>
                    @else
                        <div class="flex items-center justify-center h-48 text-xs text-gray-400">No department spend data available</div>
                    @endif
                </div>

                @if(isset($departmentChartData['labels']) && count($departmentChartData['labels']) > 0)
                    @php $finDeptColors = ['#3b82f6', '#22c55e', '#f97316', '#8b5cf6', '#06b6d4', '#ec4899', '#f59e0b']; @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($departmentChartData['labels'] as $index => $label)
                            @php
                                $val = $departmentChartData['data'][$index] ?? 0;
                                $color = $finDeptColors[$index % count($finDeptColors)];
                            @endphp
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">(Rp {{ number_format($val, 0, ',', '.') }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Spend Timeline Chart -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:col-span-2 xl:col-span-1">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Acquisition Spend Timeline</h3>
                    <p class="text-xs text-gray-400">Monthly capital expenditure ({{ $selectedYear }})</p>
                </div>

                <!-- Year Selector -->
                <div class="relative shrink-0" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                    <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                        <i data-lucide="calendar" class="w-3 h-3 text-brand-500"></i>
                        <span>{{ $selectedYear }}</span>
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

        // Custom plugin to render center text in Doughnut charts (shadcn style)
        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw(chart) {
                const { ctx, chartArea } = chart;
                const centerConfig = chart.config.options.plugins?.centerText;
                if (!centerConfig || centerConfig.value === undefined) return;

                const { left, top, width, height } = chartArea;
                const centerX = left + width / 2;
                const centerY = top + height / 2;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                ctx.font = 'bold 22px Outfit, sans-serif';
                ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a';
                ctx.fillText(Number(centerConfig.value).toLocaleString(), centerX, centerY - 8);

                ctx.font = '500 12px Outfit, sans-serif';
                ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';
                ctx.fillText(centerConfig.label || 'Total', centerX, centerY + 14);

                ctx.restore();
            }
        };

        if (typeof Chart !== 'undefined') {
            Chart.register(centerTextPlugin);
        }

        // 1. Spend Timeline Line Chart (TailAdmin Pro Statistics Line Style)
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
                        fill: true,
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;
                            if (!chartArea) return 'rgba(70, 95, 255, 0.1)';
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            const isDark = document.documentElement.classList.contains('dark');
                            gradient.addColorStop(0, isDark ? 'rgba(70, 95, 255, 0.35)' : 'rgba(70, 95, 255, 0.25)');
                            gradient.addColorStop(1, 'rgba(70, 95, 255, 0.0)');
                            return gradient;
                        },
                        borderColor: '#465fff',
                        borderWidth: 2,
                        tension: 0,
                        pointBackgroundColor: '#465fff',
                        pointBorderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#465fff',
                        pointHoverBorderColor: '#ffffff'
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
                            titleColor: '#f8fafc',
                            bodyColor: '#e2e8f0',
                            titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                            bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                            padding: { top: 8, bottom: 8, left: 12, right: 12 },
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: (ctx) => 'Spend: ' + formatRupiah(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, color: document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8' }
                        },
                        y: {
                            border: { display: false },
                            grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(148, 163, 184, 0.1)' : 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                            ticks: {
                                font: { family: 'Outfit, sans-serif', size: 11 },
                                color: document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8',
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
            const totalCatSpend = categoryData.data.reduce((a, b) => Number(a) + Number(b), 0);
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.labels,
                    datasets: [{
                        data: categoryData.data,
                        backgroundColor: [chartColors.blue, chartColors.green, chartColors.orange, chartColors.purple, chartColors.cyan],
                        borderWidth: 2,
                        borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        hoverOffset: 6,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        centerText: {
                            label: 'Total',
                            value: totalCatSpend >= 1000000000 ? (totalCatSpend / 1000000000).toFixed(1) + 'B' : (totalCatSpend >= 1000000 ? (totalCatSpend / 1000000).toFixed(1) + 'M' : totalCatSpend)
                        },
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#e2e8f0',
                            titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                            bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                            padding: { top: 8, bottom: 8, left: 12, right: 12 },
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
            const totalDeptSpend = departmentData.data.reduce((a, b) => Number(a) + Number(b), 0);
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: departmentData.labels,
                    datasets: [{
                        data: departmentData.data,
                        backgroundColor: [chartColors.blue, chartColors.green, chartColors.orange, chartColors.purple, chartColors.cyan, chartColors.pink, '#f59e0b'],
                        borderWidth: 2,
                        borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        hoverOffset: 6,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        centerText: {
                            label: 'Total',
                            value: totalDeptSpend >= 1000000000 ? (totalDeptSpend / 1000000000).toFixed(1) + 'B' : (totalDeptSpend >= 1000000 ? (totalDeptSpend / 1000000).toFixed(1) + 'M' : totalDeptSpend)
                        },
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#e2e8f0',
                            titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                            bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                            padding: { top: 8, bottom: 8, left: 12, right: 12 },
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
