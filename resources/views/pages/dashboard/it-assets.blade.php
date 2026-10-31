@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Title & Search Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">IT Assets Analytics & Control Panel</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Live monitoring of IT hardware, equipment allocation, and system alerts for {{ $userName }}.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.97] transition-all duration-150 ease-out">
                <i data-lucide="plus" class="w-4 h-4"></i> Add IT Asset
            </a>
            <a href="{{ route('assets.export') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
                <i data-lucide="download" class="w-4 h-4"></i> Export Data
            </a>
            <a href="{{ route('assets.bulk-checkout') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
                <i data-lucide="log-out" class="w-4 h-4"></i> Bulk Checkout
            </a>
        </div>
    </div>

    <!-- Critical Attention Center -->
    @php
        $hasAlerts = ($overdueLoansCount ?? 0) > 0 || $lowStockConsumables->count() > 0 || $brokenDisposal > 0 || ($expiringWarrantiesCount ?? 0) > 0 || ($expiringLicenses ?? collect())->count() > 0;
    @endphp

    @if($hasAlerts)
        <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50/80 via-white to-red-50/60 p-4 shadow-sm dark:border-amber-900/40 dark:from-amber-950/20 dark:via-gray-900 dark:to-red-950/20">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Action Required</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Items requiring IT operational attention</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if(($overdueLoansCount ?? 0) > 0)
                        <a href="{{ route('asset-transactions.index', ['tab' => 'overdue']) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-100/80 px-3 py-1.5 text-xs font-bold text-red-800 shadow-xs hover:bg-red-200 dark:border-red-900/60 dark:bg-red-950/70 dark:text-red-300 transition">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                            <span>{{ $overdueLoansCount }} Overdue Loan{{ $overdueLoansCount > 1 ? 's' : '' }}</span>
                        </a>
                    @endif
                    @if($lowStockConsumables->count() > 0)
                        <a href="{{ route('consumables.index', ['status' => 'low_stock']) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-100/80 px-3 py-1.5 text-xs font-semibold text-amber-800 shadow-xs hover:bg-amber-200 dark:border-amber-900/60 dark:bg-amber-950/70 dark:text-amber-300 transition">
                            <i data-lucide="package" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"></i>
                            <span>{{ $lowStockConsumables->count() }} Low Stock Consumable{{ $lowStockConsumables->count() > 1 ? 's' : '' }}</span>
                        </a>
                    @endif
                    @if($assetsOlderThan4Years->count() > 0)
                        <a href="{{ route('assets.replacements') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-100/80 px-3 py-1.5 text-xs font-semibold text-amber-800 shadow-xs hover:bg-amber-200 dark:border-amber-900/60 dark:bg-amber-950/70 dark:text-amber-300 transition">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"></i>
                            <span>{{ $assetsOlderThan4Years->count() }} Unit{{ $assetsOlderThan4Years->count() > 1 ? 's' : '' }} Due Refresh</span>
                        </a>
                    @endif
                    @if(($expiringWarrantiesCount ?? 0) > 0)
                        <a href="{{ route('assets.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 bg-orange-100/80 px-3 py-1.5 text-xs font-semibold text-orange-800 shadow-xs hover:bg-orange-200 dark:border-orange-900/60 dark:bg-orange-950/70 dark:text-orange-300 transition">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-orange-600 dark:text-orange-400"></i>
                            <span>{{ $expiringWarrantiesCount }} Warranty Expiring</span>
                        </a>
                    @endif
                    @if(($expiringLicenses ?? collect())->count() > 0)
                        <a href="{{ route('licenses.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-purple-200 bg-purple-100/80 px-3 py-1.5 text-xs font-semibold text-purple-800 shadow-xs hover:bg-purple-200 dark:border-purple-900/60 dark:bg-purple-950/70 dark:text-purple-300 transition">
                            <i data-lucide="key" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                            <span>{{ $expiringLicenses->count() }} Expiring License{{ $expiringLicenses->count() > 1 ? 's' : '' }}</span>
                        </a>
                    @endif
                    @if($brokenDisposal > 0)
                        <a href="{{ route('assets.index', ['status_id' => $brokenStatusId]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-100/80 px-3 py-1.5 text-xs font-semibold text-rose-800 shadow-xs hover:bg-rose-200 dark:border-rose-900/60 dark:bg-rose-950/70 dark:text-rose-300 transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400"></i>
                            <span>{{ $brokenDisposal }} Decommission Alert</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Bento Primary Row: Hero + High Priority Operational KPIs -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <!-- Hero Card (Spans 2 columns on lg) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-brand-500/30 dark:border-gray-800 dark:bg-gray-900 lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Hardware Fleet</span>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Fleet Inventory & Deployment</h2>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                        <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                        {{ $deploymentRate }}% Deployed
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap items-baseline gap-3">
                    <span class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">{{ number_format($totalAssets) }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">total units cataloged</span>
                    @if($totalBookValue > 0)
                        <span class="ml-auto text-xs font-medium text-gray-500 dark:text-gray-400">
                            Book Value: <strong class="text-gray-900 dark:text-white">Rp {{ number_format($totalBookValue, 0, ',', '.') }}</strong>
                        </span>
                    @endif
                </div>

                <!-- Multi-segment Fleet Distribution Bar -->
                @php
                    $readyPct = $totalAssets > 0 ? round(($readyToDeploy / $totalAssets) * 100) : 0;
                    $deployedPct = $totalAssets > 0 ? round(($deployed / $totalAssets) * 100) : 0;
                    $servicePct = $totalAssets > 0 ? round(($service / $totalAssets) * 100) : 0;
                    $brokenPct = $totalAssets > 0 ? round(($brokenDisposal / $totalAssets) * 100) : 0;
                @endphp
                <div class="mt-4">
                    <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div style="width: {{ $deployedPct }}%" class="bg-brand-500 transition-all duration-500" title="Deployed: {{ $deployed }} ({{ $deployedPct }}%)"></div>
                        <div style="width: {{ $readyPct }}%" class="bg-emerald-500 transition-all duration-500" title="Ready: {{ $readyToDeploy }} ({{ $readyPct }}%)"></div>
                        <div style="width: {{ $servicePct }}%" class="bg-amber-500 transition-all duration-500" title="In Service: {{ $service }} ({{ $servicePct }}%)"></div>
                        <div style="width: {{ $brokenPct }}%" class="bg-red-500 transition-all duration-500" title="Broken / Scrap: {{ $brokenDisposal }} ({{ $brokenPct }}%)"></div>
                    </div>
                </div>
            </div>

            <!-- Segmented Indicators Legend -->
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-[11px]">
                <a href="{{ route('assets.index', ['status_id' => $deployedStatusId]) }}" class="group flex items-center gap-1.5 hover:text-brand-600 transition">
                    <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">Deployed</span>
                    <strong class="text-gray-900 dark:text-white">{{ $deployed }}</strong>
                </a>
                <a href="{{ route('assets.index', ['status_id' => $readyStatusId]) }}" class="group flex items-center gap-1.5 hover:text-emerald-600 transition">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">Ready</span>
                    <strong class="text-gray-900 dark:text-white">{{ $readyToDeploy }}</strong>
                </a>
                <a href="{{ route('assets.index', ['status_id' => $serviceStatusId]) }}" class="group flex items-center gap-1.5 hover:text-amber-600 transition">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">Service</span>
                    <strong class="text-gray-900 dark:text-white">{{ $service }}</strong>
                </a>
                <a href="{{ route('assets.index', ['status_id' => $brokenStatusId]) }}" class="group flex items-center gap-1.5 hover:text-red-600 transition">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">Broken</span>
                    <strong class="text-gray-900 dark:text-white">{{ $brokenDisposal }}</strong>
                </a>
            </div>
        </div>

        @php $readyToDeployTrendColor = $readyToDeployTrend >= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="Ready to Deploy"
            icon="circle-check"
            :value="$readyToDeploy"
            :trend="$readyToDeployTrend"
            :trendColor="$readyToDeployTrendColor"
            description="Available for immediate setup"
            color="success"
            :url="route('assets.index', ['status_id' => $readyStatusId])"
        />

        @php
            $loanDesc = ($overdueLoansCount ?? 0) > 0
                ? "{$overdueLoansCount} overdue loan" . ($overdueLoansCount > 1 ? 's' : '')
                : 'All loans on schedule';
            $loanTrendColor = ($overdueLoansCount ?? 0) > 0 ? 'red' : 'emerald';
        @endphp
        <x-common.small-box
            title="Active Loans"
            icon="calendar-clock"
            :value="$activeLoansCount ?? 0"
            :trend="($overdueLoansCount ?? 0) > 0 ? $overdueLoansCount : 0"
            :trendColor="$loanTrendColor"
            :description="$loanDesc"
            :color="($overdueLoansCount ?? 0) > 0 ? 'danger' : 'info'"
            :url="route('asset-transactions.index', ['tab' => ($overdueLoansCount ?? 0) > 0 ? 'overdue' : 'active'])"
        />
    </div>

    <!-- Secondary Operational KPI Row (Clean 4-column symmetry) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-common.small-box
            title="Hardware Components"
            icon="cpu"
            :value="$totalComponentUnits"
            :trend="0"
            trendColor="emerald"
            description="{{ $installedComponents }} installed • {{ $readyComponents }} in stock"
            color="brand"
            :url="route('components.index')"
        />

        @php
            $lowStockTrend = $lowStockConsumables->count() > 0 ? 1 : -1;
            $lowStockTrendColor = $lowStockTrend <= 0 ? 'emerald' : 'red';
        @endphp
        <x-common.small-box
            title="Consumables Stock"
            icon="package"
            :value="$totalConsumables"
            :trend="$lowStockTrend"
            :trendColor="$lowStockTrendColor"
            description="{{ $lowStockConsumables->count() }} low stock item{{ $lowStockConsumables->count() > 1 ? 's' : '' }}"
            color="danger"
            :url="route('consumables.index')"
        />

        @php
            $agingTrend = $assetsOlderThan4Years->count() > 0 ? 1 : -1;
            $agingTrendColor = $agingTrend <= 0 ? 'emerald' : 'red';
        @endphp
        <x-common.small-box
            title="Aging Hardware (4+ Yrs)"
            icon="clock"
            :value="$assetsOlderThan4Years->count()"
            :trend="$agingTrend"
            :trendColor="$agingTrendColor"
            description="Reaching replacement lifecycle"
            color="warning"
            :url="route('assets.replacements')"
        />

        @php $readyForSaleTrendColor = $readyForSaleTrend >= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="Ready for Sale"
            icon="shopping-bag"
            :value="$readyForSale"
            :trend="$readyForSaleTrend"
            :trendColor="$readyForSaleTrendColor"
            description="Approved for liquidation & sale"
            color="success"
            :url="route('assets.index', ['status_id' => $readyForSaleStatusId])"
        />
    </div>

    <!-- Main Grid: Row 1 - Donut & Pie Charts (Max 3 per row) -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

        <!-- Chart 1: Status Distribution (ChartPieLegend Style) -->
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between"
             x-data="{ activeStatusCat: 'all', categoryLabel: 'All Categories', updateStatusChart(catId, label) { this.activeStatusCat = catId; this.categoryLabel = label; if (window.updateStatusChartJS) { window.updateStatusChartJS(catId); } } }">
            <div class="flex items-center justify-between gap-3 p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Status Breakdown</h3>
                    <p class="text-xs text-gray-400">Availability ratio</p>
                </div>
                <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                    <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition-all duration-150 ease-out">
                        <span x-text="categoryLabel">All Categories</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }"></i>
                    </button>
                    <div x-show="dropdownOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-1.5 origin-top-right rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-50 flex flex-col gap-0.5 max-h-60 overflow-y-auto w-max"
                         style="display: none;">
                        <button @click="updateStatusChart('all', 'All Categories'); dropdownOpen = false;" class="px-3 py-1.5 text-xs text-left font-medium text-gray-700 hover:bg-gray-50 rounded-lg dark:text-gray-300 dark:hover:bg-gray-900 transition-colors">All Categories</button>
                        @foreach($categories as $category)
                            <button @click="updateStatusChart('{{ $category->id }}', '{{ $category->name }}'); dropdownOpen = false;" class="px-3 py-1.5 text-xs text-left font-medium text-gray-700 hover:bg-gray-50 rounded-lg dark:text-gray-300 dark:hover:bg-gray-900 transition-colors">{{ $category->name }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    @if($totalAssets > 0)
                        <div id="assetStatusChart" class="h-48 w-full flex items-center justify-center"></div>
                    @else
                        <div class="flex items-center justify-center h-48 text-xs text-gray-400">No asset status data available</div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                    @foreach($chartData['labels'] as $index => $label)
                        @php
                            $count = $chartData['data'][$index] ?? 0;
                            $color = $chartData['colors'][$index] ?? '#98a2b3';
                        @endphp
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            <span class="font-bold text-gray-900 dark:text-white">({{ number_format($count) }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Chart 2: Department Distribution (ChartPieLegend Style) -->
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between"
             x-data="{ activeDeptCat: 'all', categoryLabel: 'All Categories', updateDeptChart(catId, label) { this.activeDeptCat = catId; this.categoryLabel = label; if (window.updateDeptChartJS) { window.updateDeptChartJS(catId); } } }">
            <div class="flex items-center justify-between gap-5 p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Assets by Department</h3>
                    <p class="text-xs text-gray-400">Top allocated departments</p>
                </div>
                <a href="{{ route('departments.index') }}" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">View All</a>
            </div>

            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    @if(count($deptChartData['data']) > 0)
                        <div id="deptAssetChart" class="h-48 w-full flex items-center justify-center"></div>
                    @else
                        <div class="flex items-center justify-center h-48 text-xs text-gray-400">No department asset data available</div>
                    @endif
                </div>

                @if(isset($deptChartData['labels']) && count($deptChartData['labels']) > 0)
                    @php
                        $deptColorsList = $deptChartData['colors'] ?? ['#465fff', '#12b76a', '#f79009', '#8b5cf6', '#06b6d4', '#ec4899', '#f43f5e', '#14b8a6', '#6366f1', '#d97706'];
                    @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($deptChartData['labels'] as $index => $label)
                            @php
                                $count = $deptChartData['data'][$index] ?? 0;
                                $color = $deptColorsList[$index % count($deptColorsList)];
                            @endphp
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">({{ number_format($count) }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Chart 3: Component Allocation Doughnut Chart (ChartPieLegend Style) -->
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between">
            <div class="flex items-center justify-between gap-5 p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Components Allocation</h3>
                    <p class="text-xs text-gray-400">Stock & installation status ratio</p>
                </div>
                <a href="{{ route('components.index') }}" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">View All</a>
            </div>

            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    @if(($totalComponentUnits ?? 0) > 0)
                        <div id="componentPieChart" class="h-48 w-full flex items-center justify-center"></div>
                    @else
                        <div class="flex items-center justify-center h-48 text-xs text-gray-400">No component unit data available</div>
                    @endif
                </div>

                @if(isset($componentChartData['labels']) && count($componentChartData['labels']) > 0)
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($componentChartData['labels'] as $index => $label)
                            @php
                                $count = $componentChartData['data'][$index] ?? 0;
                                $color = $componentChartData['colors'][$index] ?? '#465fff';
                            @endphp
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">({{ number_format($count) }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Main Grid: Row 2 - Dedicated Bar Chart Row (Same dimension & width as Status Breakdown) -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Chart 1: Hardware Lifecycle & Age -->
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Hardware Lifecycle & Age</h3>
                    <p class="text-xs text-gray-400">Inventory unit count by age bracket</p>
                </div>
            </div>
            @if(count($assetAgeDistribution['data']) > 0)
                <div id="assetAgeChart" class="h-64"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No lifecycle data available</div>
            @endif
        </div>

        <!-- Chart 2: Top Hardware Vendors by Volume -->
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top Hardware Vendors</h3>
                    <p class="text-xs text-gray-400">Inventory volume by brand / vendor</p>
                </div>
            </div>
            @if(count($vendorChartData['data']) > 0)
                <div id="vendorAssetChart" class="h-64"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No vendor volume data available</div>
            @endif
        </div>
    </div>

    <!-- Unified Operations & Flow Trends Hub -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        
        <!-- Hub 1: Fleet Operations & Service Hub (Checkouts vs Maintenance) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{
                activeTab: 'checkouts',
                timeframe: '30days',
                switchTab(tab) {
                    this.activeTab = tab;
                    this.$nextTick(() => {
                        window.dispatchEvent(new Event('resize'));
                        if (tab === 'checkouts' && window.checkoutCheckinTLChartInstance) window.checkoutCheckinTLChartInstance.resize();
                        if (tab === 'maintenance' && window.maintenanceTimelineChartInstance) window.maintenanceTimelineChartInstance.resize();
                    });
                },
                setTimeframe(tf) {
                    this.timeframe = tf;
                    if (this.activeTab === 'checkouts' && window.updateCheckoutCheckinTimelineTimeframe) window.updateCheckoutCheckinTimelineTimeframe(tf);
                    if (this.activeTab === 'maintenance' && window.updateMaintenanceTimelineTimeframe) window.updateMaintenanceTimelineTimeframe(tf);
                }
             }">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Activity Hub</span>
                    </div>
                    <div class="flex items-center gap-1 mt-1">
                        <button type="button" @click="switchTab('checkouts')"
                            :class="activeTab === 'checkouts' ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300 font-bold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                            class="rounded-lg px-2.5 py-1 text-sm font-semibold transition">
                            Checkouts & Returns
                        </button>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                        <button type="button" @click="switchTab('maintenance')"
                            :class="activeTab === 'maintenance' ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300 font-bold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                            class="rounded-lg px-2.5 py-1 text-sm font-semibold transition">
                            Maintenance & Service
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-950 border border-gray-200/50 dark:border-gray-800">
                    <button type="button" @click="setTimeframe('12months')" :class="timeframe === '12months' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2 py-1 transition-all">12m</button>
                    <button type="button" @click="setTimeframe('30days')" :class="timeframe === '30days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2 py-1 transition-all">30d</button>
                    <button type="button" @click="setTimeframe('7days')" :class="timeframe === '7days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2 py-1 transition-all">7d</button>
                    <button type="button" @click="setTimeframe('24hours')" :class="timeframe === '24hours' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2 py-1 transition-all">24h</button>
                </div>
            </div>

            <div x-show="activeTab === 'checkouts'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @if(isset($checkoutCheckinTimelineChartData['labels']) && count($checkoutCheckinTimelineChartData['labels']) > 0)
                    <div id="checkoutCheckinTimelineChart" class="h-64"></div>
                @else
                    <div class="flex items-center justify-center h-64 text-xs text-gray-400">No checkout/checkin timeline data available</div>
                @endif
            </div>

            <div x-show="activeTab === 'maintenance'" style="display: none;" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @if(isset($maintenanceTimelineChartData['data']) && count($maintenanceTimelineChartData['data']) > 0)
                    <div id="maintenanceTimelineChart" class="h-64"></div>
                @else
                    <div class="flex items-center justify-center h-64 text-xs text-gray-400">No maintenance timeline data available</div>
                @endif
            </div>
        </div>

        <!-- Hub 2: Resources & Acquisition Hub (Purchases vs Consumables) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{
                activeTab: 'purchases',
                timeframe: '30days',
                switchTab(tab) {
                    this.activeTab = tab;
                    this.$nextTick(() => {
                        window.dispatchEvent(new Event('resize'));
                        if (tab === 'purchases' && window.purchaseTimelineChartInstance) window.purchaseTimelineChartInstance.resize();
                        if (tab === 'consumables' && window.consumableTLChartInstance) window.consumableTLChartInstance.resize();
                    });
                },
                setTimeframe(tf) {
                    this.timeframe = tf;
                    if (this.activeTab === 'purchases' && window.updatePurchaseTimelineTimeframe) window.updatePurchaseTimelineTimeframe(tf);
                    if (this.activeTab === 'consumables' && window.updateConsumableTimelineTimeframe) window.updateConsumableTimelineTimeframe(tf);
                }
             }">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Inflow & Usage</span>
                    </div>
                    <div class="flex items-center gap-1 mt-1">
                        <button type="button" @click="switchTab('purchases')"
                            :class="activeTab === 'purchases' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 font-bold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                            class="rounded-lg px-2.5 py-1 text-sm font-semibold transition">
                            Asset Acquisitions
                        </button>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                        <button type="button" @click="switchTab('consumables')"
                            :class="activeTab === 'consumables' ? 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300 font-bold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                            class="rounded-lg px-2.5 py-1 text-sm font-semibold transition">
                            Consumable Distribution
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-950 border border-gray-200/50 dark:border-gray-800">
                    <button type="button" @click="setTimeframe('12months')" :class="timeframe === '12months' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2 py-1 transition-all">12m</button>
                    <button type="button" @click="setTimeframe('30days')" :class="timeframe === '30days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2 py-1 transition-all">30d</button>
                    <button type="button" @click="setTimeframe('7days')" :class="timeframe === '7days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2 py-1 transition-all">7d</button>
                    <button type="button" @click="setTimeframe('24hours')" :class="timeframe === '24hours' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2 py-1 transition-all">24h</button>
                </div>
            </div>

            <div x-show="activeTab === 'purchases'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @if(count($purchaseChartData['data']) > 0)
                    <div id="purchaseTimelineChart" class="h-64"></div>
                @else
                    <div class="flex items-center justify-center h-64 text-xs text-gray-400">No purchase timeline data available</div>
                @endif
            </div>

            <div x-show="activeTab === 'consumables'" style="display: none;" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @if(count($consumableTimelineChartData['data']) > 0)
                    <div id="consumableTimelineChart" class="h-64"></div>
                @else
                    <div class="flex items-center justify-center h-64 text-xs text-gray-400">No consumable timeline data available</div>
                @endif
            </div>
        </div>

    </div>

    <!-- Data Tables Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Asset Checkouts Table -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-common.dashboard-table
                title="Recent Asset Checkouts"
                description="Latest IT asset assignments to users"
                :headers="['Item', 'Status', 'To User', 'Date']"
                :footerLink="$recentCheckouts->isNotEmpty() ? route('assets.index') : null"
                footerText="View All Assets"
            >
                @forelse($recentCheckouts as $checkout)
                    <tr x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ optional($checkout->asset)->asset_tag ?? optional($checkout->asset)->name ?? 'Unknown' }}</span>
                            <span class="block text-[11px] text-gray-400">
                                {{ optional(optional(optional($checkout->asset)->product)->brand)->name ?? '' }}
                                @if(optional(optional($checkout->asset)->product)->name)
                                    • {{ $checkout->asset->product->name }}
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-2.5">
                            @php
                                $stName = optional(optional($checkout->asset)->status)->name ?? 'Deployed';
                                $stColor = optional(optional($checkout->asset)->status)->color ?? 'primary';
                                $stIcon = optional(optional($checkout->asset)->status)->icon ?? 'user-check';
                            @endphp
                            <div class="flex flex-col gap-1 items-start">
                                <x-ui.badge :color="$stColor" variant="light" size="sm" :featherIcon="$stIcon">{{ $stName }}</x-ui.badge>
                                @if($checkout->isOverdue())
                                    <span class="inline-flex items-center gap-1 rounded-md bg-red-100 px-1.5 py-0.5 text-[9px] font-bold text-red-700 dark:bg-red-950/40 dark:text-red-400">
                                        <i data-lucide="alert-circle" class="w-2.5 h-2.5"></i>
                                        Overdue ({{ $checkout->overdueDays() }}d)
                                    </span>
                                @elseif($checkout->expected_return_date)
                                    <span class="inline-flex items-center gap-1 text-[10px] text-blue-600 dark:text-blue-400">
                                        Due {{ $checkout->expected_return_date->format('d M') }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="block text-gray-700 dark:text-gray-300 font-medium">{{ $checkout->checkedOutToUser->name ?? 'N/A' }}</span>
                            <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ optional(optional($checkout->checkedOutToUser)->department)->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $checkout->checked_out_at->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No recent asset checkouts</td></tr>
                @endforelse
            </x-common.dashboard-table>
        </div>

        <!-- Aging Assets Table -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-common.dashboard-table
                title="Aging Assets (4+ Years)"
                description="Hardware reaching end-of-life cycle"
                :headers="['Item', 'Status', 'User', 'Acquisition']"
                :footerLink="$assetsOlderThan4Years->isNotEmpty() ? route('assets.replacements') : null"
                footerText="View Replacement Planner"
            >
                @forelse($assetsOlderThan4Years as $asset)
                    <tr x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $asset->asset_tag ?? $asset->name }}</span>
                            <span class="block text-[11px] text-gray-400">
                                {{ optional(optional($asset->product)->brand)->name ?? '' }}
                                @if(optional($asset->product)->name)
                                    • {{ $asset->product->name }}
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-2.5">
                            @php
                                $stName = optional($asset->status)->name ?? 'Aged';
                                $stColor = optional($asset->status)->color ?? 'warning';
                                $stIcon = optional($asset->status)->icon ?? 'clock';
                            @endphp
                            <x-ui.badge :color="$stColor" variant="light" size="sm" :featherIcon="$stIcon">{{ $stName }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            @php
                                $assignedUser = $asset->checkedOutToUser ?? optional($asset->currentCheckout)->checkedOutToUser;
                            @endphp
                            <span class="block text-gray-700 dark:text-gray-300 font-medium">{{ $assignedUser->name ?? 'Unassigned' }}</span>
                            @if($assignedUser && optional($assignedUser->department)->name)
                                <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $assignedUser->department->name }}</span>
                            @elseif(optional($asset->department)->name)
                                <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $asset->department->name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            @if($asset->purchase_date)
                                {{ $asset->purchase_date->format('M d, Y') }}
                            @else
                                {{ $asset->created_at->format('M d, Y') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No assets older than 4 years</td></tr>
                @endforelse
            </x-common.dashboard-table>
        </div>

        <!-- Recent Component Installations Table -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 md:col-span-2 xl:col-span-1">
            <x-common.dashboard-table
                title="Installed Component Units"
                description="Hardware parts (RAM, SSD, GPU) attached to assets"
                :headers="['Component', 'Installed Asset', 'Category']"
                :footerLink="route('components.index', ['tab' => 'installed'])"
                footerText="View All Components"
            >
                @forelse($recentComponentInstallations as $comp)
                    <tr x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())">
                        <td class="px-4 py-2.5">
                            <span class="block font-semibold font-mono text-gray-900 dark:text-white">{{ $comp->component_tag ?? '-' }}</span>
                            <a href="{{ route('components.show', $comp) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                {{ $comp->name }}
                            </a>
                        </td>
                        <td class="px-4 py-2.5">
                            @if($comp->assignedAsset)
                                <a href="{{ route('assets.show', $comp->assignedAsset) }}" class="font-bold font-mono text-xs text-brand-600 hover:underline dark:text-brand-400">
                                    {{ $comp->assignedAsset->asset_tag }}
                                </a>
                                <span class="block text-[10px] text-gray-400">{{ $comp->assignedAsset->product?->name ?? $comp->assignedAsset->device_name }}</span>
                            @else
                                <span class="text-gray-400 italic text-[11px]">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 whitespace-nowrap">
                            <x-ui.badge color="brand" variant="light" size="sm" featherIcon="cpu">
                                {{ $comp->category?->name ?? 'Part' }}
                            </x-ui.badge>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">No components installed yet</td></tr>
                @endforelse
            </x-common.dashboard-table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@if($totalAssets > 0)
<script>
	    window.chartData = {
		        totalAssets: {{ $totalAssets }},
		        totalComponentUnits: {{ $totalComponentUnits ?? 0 }},
		        componentChartData: @json($componentChartData ?? []),
		        chartData: @json($chartData),
	        chartDataByCategory: @json($chartDataByCategory),
	        deptChartData: @json($deptChartData),
	        deptDataByCategory: @json($deptDataByCategory),
	        productDataByCategory: @json($productDataByCategory),
	        assetAgeDistribution: @json($assetAgeDistribution),
	        vendorChartData: @json($vendorChartData),
	        consumableChartData: @json($consumableChartData),
        consumableDataByCategory: @json($consumableDataByCategory),
	        consumableTimelineChartData: @json($consumableTimelineChartData),
	        consumableTimelineByCategory: @json($consumableTimelineByCategory),
	        consumableTimeframes: @json($consumableTimeframes ?? []),
		        purchaseChartData: @json($purchaseChartData),
		        purchaseDataByCategory: @json($purchaseDataByCategory),
		        purchaseTimeframes: @json($purchaseTimeframes ?? []),
			        checkoutCheckinTimelineChartData: @json($checkoutCheckinTimelineChartData ?? []),
			        checkoutCheckinTimeframes: @json($checkoutCheckinTimeframes ?? []),
			        maintenanceTimelineChartData: @json($maintenanceTimelineChartData ?? []),
			        maintenanceTimeframes: @json($maintenanceTimeframes ?? [])
    };
</script>
@vite('resources/js/dashboard-charts.js')
@endif
@endpush
