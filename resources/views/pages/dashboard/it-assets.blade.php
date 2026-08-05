@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <x-common.page-breadcrumb pageTitle="IT Assets Dashboard" />
            <h2 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white/90">
                Hi, {{ $userName }}!
                <span class="block text-base font-normal text-gray-500 dark:text-gray-400">{{ $randomGreeting }}</span>
            </h2>
        </div>

        <!-- Alerts Summary -->
        @if($lowStockConsumables->isNotEmpty() || $assetsInService->isNotEmpty() || $assetsOlderThan4Years->isNotEmpty())
            <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Low Stock Consumables</p>
                            <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $lowStockConsumables->count() }}</h4>
                        </div>
                        @php $lowStockTrend = $lowStockConsumables->count() > 0 ? 1 : -1; @endphp
                        @php $trendColor = $lowStockTrend <= 0 ? 'success' : 'error'; @endphp
                        <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                            @if($lowStockTrend > 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ $lowStockTrend }}%
                        </span>
                    </div>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Items at or below minimum threshold.</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Broken / Disposal Alerts</p>
                            <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $brokenDisposal }}</h4>
                        </div>
                        @php $brokenTrend = $brokenDisposal > 0 ? 1 : -1; @endphp
                        @php $trendColor = $brokenTrend <= 0 ? 'success' : 'error'; @endphp
                        <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                            @if($brokenTrend > 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ $brokenTrend }}%
                        </span>
                    </div>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Assets marked as broken or retired.</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Aging Assets</p>
                            <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $assetsOlderThan4Years->count() }}</h4>
                        </div>
                        @php $agingTrend = $assetsOlderThan4Years->count() > 0 ? 1 : -1; @endphp
                        @php $trendColor = $agingTrend <= 0 ? 'success' : 'error'; @endphp
                        <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                            @if($agingTrend > 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ $agingTrend }}%
                        </span>
                    </div>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Assets approaching lifecycle replacement.</p>
                </div>
            </div>
        @endif

        <!-- Metrics Widgets -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Assets</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $totalAssets }}</h4>
                    </div>
                    @php $trendColor = $totalAssetsTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        @if($totalAssetsTrend >= 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ $totalAssetsTrend }}%
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Total registered IT hardware assets</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Ready to Deploy</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $readyToDeploy }}</h4>
                    </div>
                    @php $trendColor = $readyToDeployTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        @if($readyToDeployTrend >= 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ $readyToDeployTrend }}%
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Available assets for deployment</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Deployed</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $deployed }}</h4>
                    </div>
                    @php $trendColor = $deployedTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        @if($deployedTrend >= 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ $deployedTrend }}%
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">IT assets currently assigned to users</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">In Service</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $service }}</h4>
                    </div>
                    @php $trendColor = $serviceTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        @if($serviceTrend >= 0)<i data-feather="trending-up" class="w-3 h-3"></i>+@else<i data-feather="trending-down" class="w-3 h-3"></i>@endif{{ $serviceTrend }}%
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Assets currently under maintenance</p>
            </div>
        </div>

        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Top Location</p>
                <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Asset distribution across physical locations</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-3"></div>
                <div class="space-y-2">
                    @forelse($assetsByLocation as $item)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="truncate text-gray-700 dark:text-gray-300">{{ optional($item->location)->name ?? 'Unassigned' }}</span>
                            <span class="font-bold text-gray-800 dark:text-white/90">{{ $item->total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No location data.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Top Categories</p>
                <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Most common IT asset categories in inventory</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-3"></div>
                <div class="space-y-2">
                    @forelse($assetsByCategory as $item)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="truncate text-gray-700 dark:text-gray-300">{{ optional(optional($item->product)->category)->name ?? 'Uncategorized' }}</span>
                            <span class="font-bold text-gray-800 dark:text-white/90">{{ $item->total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No category data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Asset Status Distribution</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Breakdown of IT assets by their operational availability status</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                @if($totalAssets > 0)
                    <div id="assetStatusChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No asset status data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Assets by Department (Top 5)</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Departments with the highest volume of allocated IT devices</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                @if(count($deptChartData['data']) > 0)
                    <div id="deptAssetChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No department asset data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden"
                 x-data="{ activeCategory: 'all', categoryLabel: 'All Categories', updateProductChart(catId, label) { this.activeCategory = catId; this.categoryLabel = label; const newData = (window.productChartDataMap && window.productChartDataMap[catId]) || { labels: [], data: [] }; if (window.productChartInstance) { window.productChartInstance.updateOptions({ series: newData.data.length ? newData.data : [0], labels: newData.data.length ? newData.labels : ['No Data'] }); } } }">
                <div class="flex items-center justify-between mb-1 min-w-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Most Used Products (Top 5)</h3>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Popular hardware models deployed within the chosen category</p>
                    </div>
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-850 transition">
                            <span x-text="categoryLabel">All Categories</span>
                            <i data-feather="chevron-down" class="w-4 h-4" :class="{ 'rotate-180': dropdownOpen }"></i>
                        </button>
                        <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 z-[9999] grid grid-flow-col gap-x-2 gap-y-0.5 w-max max-w-[90vw] overflow-x-auto" style="display: none; grid-template-rows: repeat(10, minmax(0, 1fr));">
                            <button @click="updateProductChart('all', 'All Categories'); dropdownOpen = false;" :class="activeCategory === 'all' ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white'" class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition whitespace-nowrap">All Categories</button>
                            @foreach($categories as $category)
                                <button @click="updateProductChart('{{ $category->id }}', '{{ $category->name }}'); dropdownOpen = false;" :class="activeCategory === '{{ $category->id }}' ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white'" class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition whitespace-nowrap">{{ $category->name }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-800 mb-4"></div>
                @if(count($productDataByCategory['all']['data']) > 0)
                    <div id="productAssetChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No product usage data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Most Requested Consumables (Top 5)</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Top 5 most requested consumables across all categories</p>
                <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                @if(count($consumableChartData['data']) > 0)
                    <div id="requestedConsumablesChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No consumable request data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 lg:col-span-2 min-w-0 overflow-hidden">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-1">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Asset Purchases Timeline (by Release Date)</h3>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Filtered by release year</p>
                    </div>
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-850 transition">
                            <span>Year: {{ $purchaseChartYear }}</span>
                            <i data-feather="chevron-down" class="w-4 h-4" :class="{ 'rotate-180': dropdownOpen }"></i>
                        </button>
                        <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 z-[9999] flex flex-col gap-0.5 w-max" style="display: none;">
                            @foreach($purchaseChartYears as $year)
                                <a href="{{ request()->fullUrlWithQuery(['purchase_year' => $year]) }}" class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition {{ (string) $purchaseChartYear === (string) $year ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white' }}">{{ $year }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-800 mb-4"></div>
                @if(count($purchaseChartData['data']) > 0)
                    <div id="purchaseTimelineChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No purchase timeline data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Consumables Stock -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 pt-5 sm:px-6 mb-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Consumables Stock</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Current stock levels for all consumable items</p>
                </div>
                <div class="max-w-full overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Item</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Qty</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($consumablesBreakdown as $item)
                                @php
                                    $statusText = 'In Stock'; $badgeColor = 'success';
                                    if ($item->quantity <= 0) { $badgeColor = 'error'; $statusText = 'Out of Stock'; }
                                    elseif ($item->quantity <= $item->low_threshold) { $badgeColor = 'warning'; $statusText = 'Low Stock'; }
                                @endphp
                                <tr>
                                    <td class="px-5 py-3 sm:px-6">
                                        <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ optional($item->product)->name }}</span>
                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400 mt-0.5">{{ optional(optional($item->product)->brand)->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $item->quantity }}</td>
                                    <td class="px-5 py-3 sm:px-6">
                                        <span class="rounded-full bg-{{ $badgeColor }}-50 px-2 py-0.5 text-theme-xs font-medium text-{{ $badgeColor }}-700 dark:bg-{{ $badgeColor }}-500/15 dark:text-{{ $badgeColor }}-500">{{ $statusText }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-10 sm:px-6 text-center text-sm text-gray-500 dark:text-gray-400">No consumables in inventory</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($consumablesBreakdown->isNotEmpty())
                    <div class="border-t border-gray-100 py-3 dark:border-gray-800 text-center px-5 sm:px-6">
                        <a href="{{ route('consumables.index') }}" class="text-xs font-semibold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-1">Manage All Consumables <i data-feather="arrow-right" class="w-3.5 h-3.5"></i></a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Asset Checkouts -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 pt-5 sm:px-6 mb-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Asset Checkouts</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Latest IT asset assignments to users</p>
                </div>
                <div class="max-w-full overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Item</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Qty</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">To User</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($recentCheckouts as $checkout)
                                <tr>
                                    <td class="px-5 py-3 sm:px-6">
                                        <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $checkout->asset->asset_code ?? $checkout->asset->name ?? 'Unknown' }}</span>
                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400 mt-0.5">{{ optional(optional($checkout->asset)->product)->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $checkout->asset->quantity ?? 1 }} pcs</td>
                                    <td class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-semibold text-xs shrink-0">{{ strtoupper(substr($checkout->checkedOutToUser->name ?? 'N', 0, 1)) }}</div>
                                            <span class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $checkout->checkedOutToUser->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $checkout->checked_out_at->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 sm:px-6 text-center text-sm text-gray-500 dark:text-gray-400">No recent asset checkouts</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentCheckouts->isNotEmpty())
                    <div class="border-t border-gray-100 py-3 dark:border-gray-800 text-center px-5 sm:px-6">
                        <a href="{{ route('assets.index') }}" class="text-xs font-semibold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-1">View All Assets <i data-feather="arrow-right" class="w-3.5 h-3.5"></i></a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Consumable Checkouts -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 pt-5 sm:px-6 mb-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Consumable Checkouts</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Latest consumable distribution transactions</p>
                </div>
                <div class="max-w-full overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Item</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Qty</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">To User</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($recentConsumableCheckouts as $transaction)
                                <tr>
                                    <td class="px-5 py-3 sm:px-6 font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $transaction->consumable->product->name ?? 'Unknown' }}</td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $transaction->quantity }} pcs</td>
                                    <td class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-semibold text-xs shrink-0">{{ strtoupper(substr($transaction->checkedOutToUser->name ?? 'N', 0, 1)) }}</div>
                                            <span class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $transaction->checkedOutToUser->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $transaction->transaction_date->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 sm:px-6 text-center text-sm text-gray-500 dark:text-gray-400">No recent consumable checkouts</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentConsumableCheckouts->isNotEmpty())
                    <div class="border-t border-gray-100 py-3 dark:border-gray-800 text-center px-5 sm:px-6">
                        <a href="{{ route('consumable-transactions.index') }}" class="text-xs font-semibold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-1">View All Transactions <i data-feather="arrow-right" class="w-3.5 h-3.5"></i></a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Assets Older Than 4 Years -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 pt-5 sm:px-6 mb-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Assets Older Than 4 Years</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Aging assets approaching lifecycle replacement</p>
                </div>
                <div class="max-w-full overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Item</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Qty</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">To User</p></th>
                                <th class="px-5 py-3 sm:px-6 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($assetsOlderThan4Years as $asset)
                                <tr>
                                    <td class="px-5 py-3 sm:px-6">
                                        <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $asset->asset_code ?? $asset->name }}</span>
                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400 mt-0.5">{{ optional(optional($asset->product)->brand)->name ?? '-' }} • {{ optional($asset->product)->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $asset->quantity }} pcs</td>
                                    <td class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-semibold text-xs shrink-0">{{ strtoupper(substr($asset->checkedOutToUser->name ?? 'N', 0, 1)) }}</div>
                                            <span class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $asset->checkedOutToUser->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ $asset->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 sm:px-6 text-center text-sm text-gray-500 dark:text-gray-400">No assets older than 4 years</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($assetsOlderThan4Years->isNotEmpty())
                    <div class="border-t border-gray-100 py-3 dark:border-gray-800 text-center px-5 sm:px-6">
                        <a href="{{ route('assets.index') }}" class="text-xs font-semibold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-1">View All Assets <i data-feather="arrow-right" class="w-3.5 h-3.5"></i></a>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@if($totalAssets > 0)
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    window.productChartDataMap = @json($productDataByCategory);
    document.addEventListener('DOMContentLoaded', () => {
        const statusOptions = {
            series: @json($chartData['data']),
            chart: { fontFamily: "Outfit, sans-serif", type: 'donut', height: 320 },
            labels: @json($chartData['labels']),
            colors: @json($chartData['colors']),
            legend: { position: 'bottom', fontFamily: "Outfit" },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true, fontSize: '20px', fontWeight: 'bold' }, total: { show: true, label: 'Total Assets', formatter: function (w) { return {{ $totalAssets }} } } } } } },
            dataLabels: { enabled: false }
        };
        new ApexCharts(document.querySelector("#assetStatusChart"), statusOptions).render();

        const deptOptions = {
            series: @json($deptChartData['data']),
            chart: { fontFamily: "Outfit, sans-serif", type: 'donut', height: 320 },
            labels: @json($deptChartData['labels']),
            colors: ['#465FFF', '#00F5D4', '#FF9F1C', '#FF5400', '#7209B7'],
            legend: { position: 'bottom', fontFamily: "Outfit" },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true, fontSize: '20px', fontWeight: 'bold' }, total: { show: true, label: 'Assets', formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); } } } } } },
            dataLabels: { enabled: false }
        };
        new ApexCharts(document.querySelector("#deptAssetChart"), deptOptions).render();

        const productOptions = {
            series: @json($productDataByCategory['all']['data']),
            chart: { fontFamily: "Outfit, sans-serif", type: 'donut', height: 320 },
            labels: @json($productDataByCategory['all']['labels']),
            colors: ['#00F5D4', '#7209B7', '#465FFF', '#FF5400', '#FF9F1C'],
            legend: { position: 'bottom', fontFamily: "Outfit" },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true, fontSize: '20px', fontWeight: 'bold' }, total: { show: true, label: 'Total Used', formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); } } } } } },
            dataLabels: { enabled: false }
        };
        window.productChartInstance = new ApexCharts(document.querySelector("#productAssetChart"), productOptions);
        window.productChartInstance.render();

        @if(count($consumableChartData['data']) > 0)
        const consumableOptions = {
            series: @json($consumableChartData['data']),
            chart: { fontFamily: "Outfit, sans-serif", type: 'donut', height: 320 },
            labels: @json($consumableChartData['labels']),
            colors: ['#7209B7', '#FF5400', '#FF9F1C', '#465FFF', '#00F5D4'],
            legend: { position: 'bottom', fontFamily: "Outfit" },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true, fontSize: '20px', fontWeight: 'bold' }, total: { show: true, label: 'Total Items', formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); } } } } } },
            dataLabels: { enabled: false }
        };
        new ApexCharts(document.querySelector("#requestedConsumablesChart"), consumableOptions).render();
        @endif

        const purchaseOptions = {
            series: [{ name: 'Assets Released', data: @json($purchaseChartData['data']) }],
            chart: { fontFamily: "Outfit, sans-serif", type: 'area', height: 320, toolbar: { show: false } },
            colors: ['#465FFF'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: @json($purchaseChartData['labels']), labels: { style: { fontFamily: 'Outfit, sans-serif' } } },
            yaxis: { labels: { style: { fontFamily: 'Outfit, sans-serif' } } },
            dataLabels: { enabled: false }
        };
        new ApexCharts(document.querySelector("#purchaseTimelineChart"), purchaseOptions).render();
    });
</script>
@endif
@endpush
