@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-gray-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <x-common.page-breadcrumb pageTitle="IT Assets Dashboard" />
                    <h2 class="mt-3 text-2xl font-bold text-gray-800 dark:text-white">
                        Hi, {{ $userName }}! 👋
                        <span class="block text-sm font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $randomGreeting }}</span>
                    </h2>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="plus" class="w-4 h-4"></i> Add Asset
                    </a>
                    <a href="{{ route('assets.bulk-checkout') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-750 transition">
                        <i data-feather="log-out" class="w-4 h-4"></i> Checkout
                    </a>
                </div>
            </div>
        </div>

        <!-- Alerts Summary -->
        @if($lowStockConsumables->isNotEmpty() || $assetsInService->isNotEmpty() || $assetsOlderThan4Years->isNotEmpty() || $brokenDisposal > 0)
            <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-3">
                @php
                    $lowStockTrend = $lowStockConsumables->count() > 0 ? 1 : -1;
                    $lowStockTrendColor = $lowStockTrend <= 0 ? 'success' : 'error';
                @endphp
                <x-common.stat-card
                    title="Low Stock Consumables"
                    :value="$lowStockConsumables->count()"
                    :trend="$lowStockTrend"
                    :trendColor="$lowStockTrendColor"
                    description="Items at or below minimum threshold."
                />

                @php
                    $brokenTrend = $brokenDisposal > 0 ? 1 : -1;
                    $brokenTrendColor = $brokenTrend <= 0 ? 'success' : 'error';
                @endphp
                <x-common.stat-card
                    title="Broken / Disposal Alerts"
                    :value="$brokenDisposal"
                    :trend="$brokenTrend"
                    :trendColor="$brokenTrendColor"
                    description="Assets marked as broken or retired."
                />

                @php
                    $agingTrend = $assetsOlderThan4Years->count() > 0 ? 1 : -1;
                    $agingTrendColor = $agingTrend <= 0 ? 'success' : 'error';
                @endphp
                <x-common.stat-card
                    title="Aging Assets"
                    :value="$assetsOlderThan4Years->count()"
                    :trend="$agingTrend"
                    :trendColor="$agingTrendColor"
                    description="Assets approaching lifecycle replacement."
                />
            </div>
        @endif

        <!-- Metrics Widgets -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            @php $totalAssetsTrendColor = $totalAssetsTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Total Assets"
                :value="$totalAssets"
                :trend="$totalAssetsTrend"
                :trendColor="$totalAssetsTrendColor"
                description="Total registered IT hardware assets"
            />

            @php $readyToDeployTrendColor = $readyToDeployTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Ready to Deploy"
                :value="$readyToDeploy"
                :trend="$readyToDeployTrend"
                :trendColor="$readyToDeployTrendColor"
                description="Available assets for deployment"
            />

            @php $deployedTrendColor = $deployedTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Deployed"
                :value="$deployed"
                :trend="$deployedTrend"
                :trendColor="$deployedTrendColor"
                description="IT assets currently assigned to users"
            />

            @php $serviceTrendColor = $serviceTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="In Service"
                :value="$service"
                :trend="$serviceTrend"
                :trendColor="$serviceTrendColor"
                description="Assets currently under maintenance"
            />
        </div>

        <!-- Financial & Asset Value Overview Card -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-common.stat-card
                title="Total Purchase Cost"
                value="Rp {{ number_format($totalPurchaseCost, 0, ',', '.') }}"
                description="Sum of initial asset acquisition costs"
            />
            <x-common.stat-card
                title="Current Book Value"
                value="Rp {{ number_format($totalBookValue, 0, ',', '.') }}"
                description="Net asset value after straight-line depreciation"
            />
            <x-common.stat-card
                title="Total Depreciated"
                value="Rp {{ number_format($totalDepreciated, 0, ',', '.') }}"
                description="Accumulated depreciation loss to date"
            />
        </div>

        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            @php
                $locationMax = $assetsByLocation->max('total') ?: 1;
                $categoryMax = $assetsByCategory->max('total') ?: 1;
            @endphp

            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Location</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Asset distribution across physical locations</p>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-800 mb-4"></div>
                <div class="space-y-3">
                    @forelse($assetsByLocation as $item)
                        @php
                            $locationName = optional($item->location)->name ?? 'Unassigned';
                            $locationPercent = min(100, round(($item->total / $locationMax) * 100));
                        @endphp
                        <div class="group">
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                        <i data-feather="map-pin" class="w-3 h-3"></i>
                                    </span>
                                    {{ $locationName }}
                                </span>
                                <span class="font-bold text-gray-800 dark:text-white/90">{{ $item->total }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-400 transition-all duration-700 ease-out" style="width: {{ $locationPercent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800 mb-3">
                                <i data-feather="map" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No location data.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Categories</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Most common IT asset categories in inventory</p>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-800 mb-4"></div>
                <div class="space-y-3">
                    @forelse($assetsByCategory as $item)
                        @php
                            $categoryName = optional(optional($item->product)->category)->name ?? 'Uncategorized';
                            $categoryPercent = min(100, round(($item->total / $categoryMax) * 100));
                        @endphp
                        <div class="group">
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                        <i data-feather="layers" class="w-3 h-3"></i>
                                    </span>
                                    {{ $categoryName }}
                                </span>
                                <span class="font-bold text-gray-800 dark:text-white/90">{{ $item->total }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-success-500 to-success-400 transition-all duration-700 ease-out" style="width: {{ $categoryPercent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800 mb-3">
                                <i data-feather="grid" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No category data.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Asset Status Distribution</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Breakdown of IT assets by their operational availability status</p>
                <div class="border-t border-gray-100 dark:border-gray-850 my-4"></div>
                @if($totalAssets > 0)
                    <div id="assetStatusChart" class="h-80 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No asset status data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Assets by Department (Top 5)</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Departments with the highest volume of allocated IT devices</p>
                <div class="border-t border-gray-100 dark:border-gray-850 my-4"></div>
                @if(count($deptChartData['data']) > 0)
                    <div id="deptAssetChart" class="h-80 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No department asset data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs"
                 x-data="{ activeCategory: 'all', categoryLabel: 'All Categories', updateProductChart(catId, label) { this.activeCategory = catId; this.categoryLabel = label; const newData = (window.productChartDataMap && window.productChartDataMap[catId]) || { labels: [], data: [] }; if (window.productChartInstance) { window.productChartInstance.updateOptions({ series: newData.data.length ? newData.data : [0], labels: newData.data.length ? newData.labels : ['No Data'] }); } } }">
                <div class="flex items-center justify-between mb-1 min-w-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Most Used Products (Top 5)</h3>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Popular hardware models deployed within the chosen category</p>
                    </div>
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-850 transition">
                            <span x-text="categoryLabel">All Categories</span>
                            <i data-feather="chevron-down" class="w-4 h-4" :class="{ 'rotate-180': dropdownOpen }"></i>
                        </button>
                        <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] grid grid-flow-col gap-x-2 gap-y-0.5 w-max max-w-[90vw] overflow-x-auto" style="display: none; grid-template-rows: repeat(10, minmax(0, 1fr));">
                            <button @click="updateProductChart('all', 'All Categories'); dropdownOpen = false;" :class="activeCategory === 'all' ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white'" class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition whitespace-nowrap">All Categories</button>
                            @foreach($categories as $category)
                                <button @click="updateProductChart('{{ $category->id }}', '{{ $category->name }}'); dropdownOpen = false;" :class="activeCategory === '{{ $category->id }}' ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white'" class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition whitespace-nowrap">{{ $category->name }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-850 mb-4"></div>
                @if(count($productDataByCategory['all']['data']) > 0)
                    <div id="productAssetChart" class="h-80 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No product usage data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 min-w-0 overflow-hidden shadow-theme-xs">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Most Requested Consumables (Top 5)</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Top 5 most requested consumables across all categories</p>
                <div class="border-t border-gray-100 dark:border-gray-850 my-4"></div>
                @if(count($consumableChartData['data']) > 0)
                    <div id="requestedConsumablesChart" class="h-80 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No consumable request data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6 lg:col-span-2 min-w-0 overflow-hidden shadow-theme-xs">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-1">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Asset Purchases Timeline</h3>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Filtered by release year</p>
                    </div>
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-850 transition">
                            <span>Year: {{ $purchaseChartYear }}</span>
                            <i data-feather="chevron-down" class="w-4 h-4" :class="{ 'rotate-180': dropdownOpen }"></i>
                        </button>
                        <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 w-max" style="display: none;">
                            @foreach($purchaseChartYears as $year)
                                <a href="{{ request()->fullUrlWithQuery(['purchase_year' => $year]) }}" class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition {{ (string) $purchaseChartYear === (string) $year ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white' }}">{{ $year }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-850 mb-4"></div>
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
            <x-common.dashboard-table
                title="Consumables Stock"
                description="Current stock levels for all consumable items"
                :headers="['Item', 'Qty', 'Status']"
                :footerLink="$consumablesBreakdown->isNotEmpty() ? route('consumables.index') : null"
                footerText="Manage All Consumables"
            >
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
            </x-common.dashboard-table>
        </div>

        <!-- Recent Asset Checkouts -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <x-common.dashboard-table
                title="Recent Asset Checkouts"
                description="Latest IT asset assignments to users"
                :headers="['Item', 'Qty', 'To User', 'Date']"
                :footerLink="$recentCheckouts->isNotEmpty() ? route('assets.index') : null"
                footerText="View All Assets"
            >
                @forelse($recentCheckouts as $checkout)
                    <tr>
                        <td class="px-5 py-3 sm:px-6">
                            <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ optional($checkout->asset)->asset_code ?? optional($checkout->asset)->name ?? 'Unknown' }}</span>
                            <span class="block text-gray-500 text-theme-xs dark:text-gray-400 mt-0.5">{{ optional(optional(optional($checkout->asset))->product)->name ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3 sm:px-6 text-gray-500 text-theme-sm dark:text-gray-400">{{ optional($checkout->asset)->quantity ?? 1 }} pcs</td>
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
            </x-common.dashboard-table>
        </div>

        <!-- Recent Consumable Checkouts -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <x-common.dashboard-table
                title="Recent Consumable Checkouts"
                description="Latest consumable distribution transactions"
                :headers="['Item', 'Qty', 'To User', 'Date']"
                :footerLink="$recentConsumableCheckouts->isNotEmpty() ? route('consumable-transactions.index') : null"
                footerText="View All Transactions"
            >
                @forelse($recentConsumableCheckouts as $transaction)
                    <tr>
                        <td class="px-5 py-3 sm:px-6 font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ optional(optional($transaction->consumable)->product)->name ?? 'Unknown' }}</td>
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
            </x-common.dashboard-table>
        </div>

        <!-- Assets Older Than 4 Years -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <x-common.dashboard-table
                title="Assets Older Than 4 Years"
                description="Aging assets approaching lifecycle replacement"
                :headers="['Item', 'Qty', 'To User', 'Date']"
                :footerLink="$assetsOlderThan4Years->isNotEmpty() ? route('assets.index') : null"
                footerText="View All Assets"
            >
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
            </x-common.dashboard-table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@if($totalAssets > 0)
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    window.chartData = {
        totalAssets: {{ $totalAssets }},
        chartData: @json($chartData),
        deptChartData: @json($deptChartData),
        productDataByCategory: @json($productDataByCategory),
        consumableChartData: @json($consumableChartData),
        purchaseChartData: @json($purchaseChartData)
    };
</script>
@vite('resources/js/dashboard-charts.js')
@endif
@endpush
