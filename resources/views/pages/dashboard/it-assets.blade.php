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
                <i data-feather="plus" class="w-4 h-4"></i> Add IT Asset
            </a>
            <a href="{{ route('assets.export') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
                <i data-feather="download" class="w-4 h-4"></i> Export Data
            </a>
            <a href="{{ route('assets.bulk-checkout') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
                <i data-feather="log-out" class="w-4 h-4"></i> Bulk Checkout
            </a>
        </div>
    </div>

    <!-- Main Grid of KPI Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @php $totalAssetsTrendColor = $totalAssetsTrend >= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="Total Registered Assets"
            icon="hard-drive"
            :value="$totalAssets"
            :trend="$totalAssetsTrend"
            :trendColor="$totalAssetsTrendColor"
            description="Total IT hardware inventory"
        />

        @php $readyToDeployTrendColor = $readyToDeployTrend >= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="Ready to Deploy"
            icon="check-circle"
            :value="$readyToDeploy"
            :trend="$readyToDeployTrend"
            :trendColor="$readyToDeployTrendColor"
            description="Available for immediate setup"
        />

        @php $deployedTrendColor = $deployedTrend >= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="Active Deployed"
            icon="user-check"
            :value="$deployed"
            :trend="$deployedTrend"
            :trendColor="$deployedTrendColor"
            description="Assigned to employees & offices"
        />

        @php $serviceTrendColor = $serviceTrend >= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="In Repair / Service"
            icon="wrench"
            :value="$service"
            :trend="$serviceTrend"
            :trendColor="$serviceTrendColor"
            description="Undergoing maintenance"
        />

        @php $readyForSaleTrendColor = $readyForSaleTrend >= 0 ? 'success' : 'error'; @endphp
        <x-common.stat-card
            title="Ready for Sale"
            icon="shopping-bag"
            :value="$readyForSale"
            :trend="$readyForSaleTrend"
            :trendColor="$readyForSaleTrendColor"
            description="Approved & prepared for liquidation"
        />
    </div>

    <!-- Alert KPI Row (If any critical issues) -->
    @if($lowStockConsumables->isNotEmpty() || $assetsInService->isNotEmpty() || $assetsOlderThan4Years->isNotEmpty() || $brokenDisposal > 0)
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            @php
                $lowStockTrend = $lowStockConsumables->count() > 0 ? 1 : -1;
                $lowStockTrendColor = $lowStockTrend <= 0 ? 'success' : 'error';
            @endphp
            <x-common.stat-card
                title="Low Stock Consumables"
                icon="package"
                :value="$lowStockConsumables->count()"
                :trend="$lowStockTrend"
                :trendColor="$lowStockTrendColor"
                description="Items below minimum stock threshold."
            />

            @php
                $brokenTrend = $brokenDisposal > 0 ? 1 : -1;
                $brokenTrendColor = $brokenTrend <= 0 ? 'success' : 'error';
            @endphp
            <x-common.stat-card
                title="Broken / Disposal Alerts"
                icon="alert-triangle"
                :value="$brokenDisposal"
                :trend="$brokenTrend"
                :trendColor="$brokenTrendColor"
                description="Assets marked for decommissioning."
            />

            @php
                $agingTrend = $assetsOlderThan4Years->count() > 0 ? 1 : -1;
                $agingTrendColor = $agingTrend <= 0 ? 'success' : 'error';
            @endphp
            <x-common.stat-card
                title="Aging Assets (4+ Yrs)"
                icon="clock"
                :value="$assetsOlderThan4Years->count()"
                :trend="$agingTrend"
                :trendColor="$agingTrendColor"
                description="Hardware nearing end of lifecycle."
            />
        </div>
    @endif

    <!-- Main Grid: Charts & Analytics Breakdown -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        <!-- Chart 1: Status Distribution -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{ activeStatusCat: 'all', categoryLabel: 'All Categories', updateStatusChart(catId, label) { this.activeStatusCat = catId; this.categoryLabel = label; if (window.updateStatusChartJS) { window.updateStatusChartJS(catId); } } }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Status Breakdown</h3>
                    <p class="text-xs text-gray-400">Availability ratio</p>
                </div>
                <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                    <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition-all duration-150 ease-out">
                        <span x-text="categoryLabel">All Categories</span>
                        <i data-feather="chevron-down" class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }"></i>
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
            @if($totalAssets > 0)
                <div id="assetStatusChart" class="h-64 flex items-center justify-center"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No asset status data available</div>
            @endif
        </div>

        <!-- Chart 2: Department Distribution -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{ activeDeptCat: 'all', categoryLabel: 'All Categories', updateDeptChart(catId, label) { this.activeDeptCat = catId; this.categoryLabel = label; if (window.updateDeptChartJS) { window.updateDeptChartJS(catId); } } }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Assets by Department</h3>
                    <p class="text-xs text-gray-400">Top allocated departments</p>
                </div>
            </div>
            @if(count($deptChartData['data']) > 0)
                <div id="deptAssetChart" class="h-64 flex items-center justify-center"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No department asset data available</div>
            @endif
        </div>

        <!-- Chart 3: Most Used Products -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{ activeCategory: 'all', categoryLabel: 'All Categories', updateProductChart(catId, label) { this.activeCategory = catId; this.categoryLabel = label; if (window.updateProductChartJS) { window.updateProductChartJS(catId); } } }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Most Used Hardware</h3>
                    <p class="text-xs text-gray-400">Top 5 hardware models</p>
                </div>
            </div>
            @if(count($productDataByCategory['all']['data']) > 0)
                <div id="productAssetChart" class="h-64 flex items-center justify-center"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No product usage data available</div>
            @endif
        </div>

    </div>

    <!-- Timeline & Purchases Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        
        <!-- Timeline Chart: Consumable Usage -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Consumable Usage Timeline</h3>
                    <p class="text-xs text-gray-400">Distribution trend by month</p>
                </div>
            </div>
            @if(count($consumableTimelineChartData['data']) > 0)
                <div id="consumableTimelineChart" class="h-64"></div>
            @else
                <div class="flex items-center justify-center h-64 text-xs text-gray-400">No consumable timeline data available</div>
            @endif
        </div>

        <!-- Timeline Chart: Asset Purchases -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Asset Acquisition Timeline</h3>
                    <p class="text-xs text-gray-400">Historical acquisition volume</p>
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
                            <span class="block text-[11px] text-gray-400">{{ optional(optional(optional($checkout->asset))->product)->name ?? '-' }}</span>
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
                            <span class="block text-[11px] text-gray-400">{{ optional(optional($asset->product)->brand)->name ?? '-' }}</span>
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
    </div>

</div>
@endsection

@push('scripts')
@if($totalAssets > 0)
<script>
    window.chartData = {
        totalAssets: {{ $totalAssets }},
        chartData: @json($chartData),
        chartDataByCategory: @json($chartDataByCategory),
        deptChartData: @json($deptChartData),
        deptDataByCategory: @json($deptDataByCategory),
        productDataByCategory: @json($productDataByCategory),
        consumableChartData: @json($consumableChartData),
        consumableDataByCategory: @json($consumableDataByCategory),
        consumableTimelineChartData: @json($consumableTimelineChartData),
        consumableTimelineByCategory: @json($consumableTimelineByCategory),
        purchaseChartData: @json($purchaseChartData),
        purchaseDataByCategory: @json($purchaseDataByCategory)
    };
</script>
@vite('resources/js/dashboard-charts.js')
@endif
@endpush
