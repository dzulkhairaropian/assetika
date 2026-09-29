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

    <!-- Main Grid of KPI Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php $totalAssetsTrendColor = $totalAssetsTrend >= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="Total Registered Assets"
            icon="hard-drive"
            :value="$totalAssets"
            :trend="$totalAssetsTrend"
            :trendColor="$totalAssetsTrendColor"
            description="Total IT hardware inventory"
            color="brand"
            :url="route('assets.index')"
        />

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

        @php $deployedTrendColor = $deployedTrend >= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="Active Deployed"
            icon="user-check"
            :value="$deployed"
            :trend="$deployedTrend"
            :trendColor="$deployedTrendColor"
            description="Assigned to employees & offices"
            color="info"
            :url="route('assets.index', ['status_id' => $deployedStatusId])"
        />

        @php $serviceTrendColor = $serviceTrend >= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="In Repair / Service"
            icon="wrench"
            :value="$service"
            :trend="$serviceTrend"
            :trendColor="$serviceTrendColor"
            description="Undergoing maintenance"
            color="warning"
            :url="route('assets.index', ['status_id' => $serviceStatusId])"
        />
    </div>

    <!-- Secondary Alert & Operational KPI Row -->
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
            title="Low Stock Consumables"
            icon="package"
            :value="$lowStockConsumables->count()"
            :trend="$lowStockTrend"
            :trendColor="$lowStockTrendColor"
            description="Items below minimum threshold"
            color="danger"
            :url="route('consumables.index', ['status' => 'low_stock'])"
        />

        @php
            $brokenTrend = $brokenDisposal > 0 ? 1 : -1;
            $brokenTrendColor = $brokenTrend <= 0 ? 'emerald' : 'red';
        @endphp
        <x-common.small-box
            title="Broken / Disposal Alerts"
            icon="alert-triangle"
            :value="$brokenDisposal"
            :trend="$brokenTrend"
            :trendColor="$brokenTrendColor"
            description="Assets marked for decommissioning"
            color="danger"
            :url="route('assets.index', ['status_id' => $brokenStatusId])"
        />

        @php
            $agingTrend = $assetsOlderThan4Years->count() > 0 ? 1 : -1;
            $agingTrendColor = $agingTrend <= 0 ? 'emerald' : 'red';
        @endphp
        <x-common.small-box
            title="Aging Assets (4+ Yrs)"
            icon="clock"
            :value="$assetsOlderThan4Years->count()"
            :trend="$agingTrend"
            :trendColor="$agingTrendColor"
            description="Hardware nearing end of lifecycle"
            color="warning"
            :url="route('assets.index')"
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
                        $deptColorsList = ['#3b82f6', '#22c55e', '#f97316', '#8b5cf6', '#06b6d4', '#ec4899', '#f59e0b'];
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

    <!-- Timeline & Purchases Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        
        <!-- Timeline Chart: Consumable Usage -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900" x-data="{ timeframe: '30days' }">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Consumable Usage Timeline</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">Distribution trend by timeframe</span>
                </div>

                <div class="flex items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800">
                    <button @click="timeframe = '12months'; if(window.updateConsumableTimelineTimeframe) window.updateConsumableTimelineTimeframe('12months')" :class="timeframe === '12months' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">12 months</button>
                    <button @click="timeframe = '30days'; if(window.updateConsumableTimelineTimeframe) window.updateConsumableTimelineTimeframe('30days')" :class="timeframe === '30days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">30 days</button>
                    <button @click="timeframe = '7days'; if(window.updateConsumableTimelineTimeframe) window.updateConsumableTimelineTimeframe('7days')" :class="timeframe === '7days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">7 days</button>
                    <button @click="timeframe = '24hours'; if(window.updateConsumableTimelineTimeframe) window.updateConsumableTimelineTimeframe('24hours')" :class="timeframe === '24hours' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">24 hours</button>
                </div>
            </div>
            @if(count($consumableTimelineChartData['data']) > 0)
                <div id="consumableTimelineChart" class="h-64"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No consumable timeline data available</div>
            @endif
        </div>

        <!-- Timeline Chart: Asset Purchases -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900" x-data="{ timeframe: '30days' }">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Asset Acquisition Timeline</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">Historical acquisition volume</span>
                </div>

                <div class="flex items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800">
                    <button @click="timeframe = '12months'; if(window.updatePurchaseTimelineTimeframe) window.updatePurchaseTimelineTimeframe('12months')" :class="timeframe === '12months' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">12 months</button>
                    <button @click="timeframe = '30days'; if(window.updatePurchaseTimelineTimeframe) window.updatePurchaseTimelineTimeframe('30days')" :class="timeframe === '30days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">30 days</button>
                    <button @click="timeframe = '7days'; if(window.updatePurchaseTimelineTimeframe) window.updatePurchaseTimelineTimeframe('7days')" :class="timeframe === '7days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">7 days</button>
                    <button @click="timeframe = '24hours'; if(window.updatePurchaseTimelineTimeframe) window.updatePurchaseTimelineTimeframe('24hours')" :class="timeframe === '24hours' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">24 hours</button>
                </div>
            </div>
            @if(count($purchaseChartData['data']) > 0)
                <div id="purchaseTimelineChart" class="h-64"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No purchase timeline data available</div>
            @endif
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
                            <x-ui.badge :color="$stColor" variant="light" size="sm" :featherIcon="$stIcon">{{ $stName }}</x-ui.badge>
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
                :footerLink="$assetsOlderThan4Years->isNotEmpty() ? route('assets.index') : null"
                footerText="View All Assets"
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
	        purchaseTimeframes: @json($purchaseTimeframes ?? [])
    };
</script>
@vite('resources/js/dashboard-charts.js')
@endif
@endpush
