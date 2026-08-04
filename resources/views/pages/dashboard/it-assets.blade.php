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
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Low Stock Consumables</p>
                            <h3 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $lowStockConsumables->count() }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                            <i data-lucide="package-x" class="h-5 w-5"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Items at or below minimum threshold.</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Broken / Disposal Alerts</p>
                            <h3 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $brokenDisposal }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                            <i data-lucide="circle-off" class="h-5 w-5"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Assets marked as broken or retired.</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Aging Assets</p>
                            <h3 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $assetsOlderThan4Years->count() }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            <i data-lucide="hourglass" class="h-5 w-5"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Assets approaching lifecycle replacement.</p>
                </div>
            </div>
        @endif


        <!-- Metrics Widgets -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            <!-- Total Assets -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between" style="font-family: Outfit, sans-serif; min-height: 140px; border-radius: 16px;">
                <div class="flex items-start justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    </div>
                    @php $trendColor = $totalAssetsTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        {{ $totalAssetsTrend >= 0 ? '▲ +' : '▼ ' }}{{ $totalAssetsTrend }}%
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Assets</h3>
                    <h4 class="mt-1 text-2xl font-bold text-gray-850 dark:text-white/95">{{ $totalAssets }}</h4>
                    <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">Total registered IT hardware assets</p>
                </div>
            </div>

            <!-- Ready to Deploy -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between" style="font-family: Outfit, sans-serif; min-height: 140px; border-radius: 16px;">
                <div class="flex items-start justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-success-50 text-success-650 dark:bg-success-500/10 dark:text-success-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    @php $trendColor = $readyToDeployTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        {{ $readyToDeployTrend >= 0 ? '▲ +' : '▼ ' }}{{ $readyToDeployTrend }}%
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Ready to Deploy</h3>
                    <h4 class="mt-1 text-2xl font-bold text-gray-850 dark:text-white/95">{{ $readyToDeploy }}</h4>
                    <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">Available assets for deployment</p>
                </div>
            </div>

            <!-- Deployed -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between" style="font-family: Outfit, sans-serif; min-height: 140px; border-radius: 16px;">
                <div class="flex items-start justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-info-50 text-info-600 dark:bg-info-500/10 dark:text-info-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    </div>
                    @php $trendColor = $deployedTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        {{ $deployedTrend >= 0 ? '▲ +' : '▼ ' }}{{ $deployedTrend }}%
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Deployed</h3>
                    <h4 class="mt-1 text-2xl font-bold text-gray-850 dark:text-white/95">{{ $deployed }}</h4>
                    <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">IT assets currently assigned to users</p>
                </div>
            </div>

            <!-- In Service -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between" style="font-family: Outfit, sans-serif; min-height: 140px; border-radius: 16px;">
                <div class="flex items-start justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    @php $trendColor = $serviceTrend >= 0 ? 'success' : 'error'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
                        {{ $serviceTrend >= 0 ? '▲ +' : '▼ ' }}{{ $serviceTrend }}%
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">In Service</h3>
                    <h4 class="mt-1 text-2xl font-bold text-gray-850 dark:text-white/95">{{ $service }}</h4>
                    <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">Assets currently under maintenance</p>
                </div>
            </div>
        </div>

        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2 min-w-0">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Top Location</p>
                <div class="mt-3 space-y-2">
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
                <div class="mt-3 space-y-2">
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
            <!-- Chart 1: Asset Status Distribution -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        Asset Status Distribution
                    </h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Breakdown of IT assets by their operational availability status</p>
                </div>
                @if($totalAssets > 0)
                    <div id="assetStatusChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No asset status data available</p>
                    </div>
                @endif
            </div>

            <!-- Chart 2: Assets by Department -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Assets by Department (Top 5)
                    </h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Departments with the highest volume of allocated IT devices</p>
                </div>
                @if(count($deptChartData['data']) > 0)
                    <div id="deptAssetChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No department asset data available</p>
                    </div>
                @endif
            </div>

            <!-- Chart 3: Most Used Products -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden"
                 x-data="{
                     activeCategory: 'all',
                     categoryLabel: 'All Categories',
                     updateProductChart(catId, label) {
                         this.activeCategory = catId;
                         this.categoryLabel = label;
                         const newData = (window.productChartDataMap && window.productChartDataMap[catId]) || { labels: [], data: [] };
                         if (window.productChartInstance) {
                             if (newData.data.length === 0) {
                                 window.productChartInstance.updateOptions({
                                     series: [0],
                                     labels: ['No Data']
                                 });
                             } else {
                                 window.productChartInstance.updateOptions({
                                     series: newData.data,
                                     labels: newData.labels
                                 });
                             }
                         }
                     }
                 }">
                <div class="flex items-center justify-between mb-4 min-w-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Most Used Products (Top 5)
                        </h3>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Popular hardware models deployed within the chosen category</p>
                    </div>

                    <!-- Category Dropdown Select -->
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen"
                                class="inline-flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-850 transition">
                            <span x-text="categoryLabel">All Categories</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="dropdownOpen"
                             x-transition
                             class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 z-[9999] grid grid-flow-col gap-x-2 gap-y-0.5 w-max max-w-[90vw] overflow-x-auto"
                             style="display: none; grid-template-rows: repeat(10, minmax(0, 1fr));">
                            <button @click="updateProductChart('all', 'All Categories'); dropdownOpen = false;"
                                    :class="activeCategory === 'all' ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white'"
                                    class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition whitespace-nowrap">
                                All Categories
                            </button>
                            @foreach($categories as $category)
                                <button @click="updateProductChart('{{ $category->id }}', '{{ $category->name }}'); dropdownOpen = false;"
                                        :class="activeCategory === '{{ $category->id }}' ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white'"
                                        class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition whitespace-nowrap">
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if(count($productDataByCategory['all']['data']) > 0)
                    <div id="productAssetChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No product usage data available</p>
                    </div>
                @endif
            </div>

            <!-- Chart 5: Most Requested Consumables -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 min-w-0 overflow-hidden">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Most Requested Consumables (Top 5)
                </h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Top 5 most requested consumables across all categories</p>
                @if(count($consumableChartData['data']) > 0)
                    <div id="requestedConsumablesChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No consumable request data available</p>
                    </div>
                @endif
            </div>

            <!-- Chart 4: Monthly Asset Purchases -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 lg:col-span-2 min-w-0 overflow-hidden">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Asset Purchases Timeline (by Release Date)
                        </h3>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Filtered by release year</p>
                    </div>

                    <!-- Year Dropdown Select -->
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen"
                                class="inline-flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-850 transition">
                            <span>Year: {{ $purchaseChartYear }}</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="dropdownOpen"
                             x-transition
                             class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 z-[9999] flex flex-col gap-0.5 w-max"
                             style="display: none;">
                            @foreach($purchaseChartYears as $year)
                                <a href="{{ request()->fullUrlWithQuery(['purchase_year' => $year]) }}"
                                   class="flex items-center rounded-lg px-3 py-2 text-xs font-medium transition {{ (string) $purchaseChartYear === (string) $year ? 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                                    {{ $year }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if(count($purchaseChartData['data']) > 0)
                    <div id="purchaseTimelineChart" class="h-80"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-center">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No purchase timeline data available</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-span-12 xl:col-span-6 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pb-4 dark:border-gray-800 dark:bg-white/[0.03] h-full flex flex-col">

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 pt-5 sm:px-6 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Consumables Stock
                    </h3>

                    <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        {{ number_format($totalConsumables) }} Units Total
                    </span>
                </div>

                {{-- Content --}}
                <div class="h-80 overflow-y-auto scrollbar-custom px-5 sm:px-6 pr-2">
                    <div class="space-y-3">
                        @forelse($consumablesBreakdown as $item)
                            @php
                                $stockPercent = $item->quantity > 0 ? min(100, max(8, ($item->quantity / ($item->low_threshold > 0 ? $item->low_threshold * 3 : 10)) * 100)) : 0;
                                $colorClass = 'bg-success-500';
                                $badgeColor = 'success';
                                $statusText = 'In Stock';
                                if ($item->quantity <= 0) {
                                    $colorClass = 'bg-error-500';
                                    $badgeColor = 'error';
                                    $statusText = 'Out of Stock';
                                } elseif ($item->quantity <= $item->low_threshold) {
                                    $colorClass = 'bg-warning-500';
                                    $badgeColor = 'warning';
                                    $statusText = 'Low Stock';
                                }
                            @endphp
                            <div class="flex flex-col gap-1.5 rounded-xl border border-gray-100 p-3 dark:border-gray-850 dark:bg-white/[0.01]">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <span class="block text-sm font-semibold text-gray-800 dark:text-white/90 truncate">
                                            {{ optional($item->product)->name }}
                                        </span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 truncate block">
                                            {{ optional(optional($item->product)->brand)->name ?? 'No Brand' }} • {{ optional(optional($item->product)->category)->name ?? 'No Category' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-sm font-bold text-gray-800 dark:text-white/90">{{ $item->quantity }} items</span>
                                        <x-ui.badge :color="$badgeColor" variant="light" size="sm">{{ $statusText }}</x-ui.badge>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 dark:bg-gray-800">
                                    <div class="{{ $colorClass }} h-1.5 rounded-full transition-all" style="width: {{ $stockPercent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3 dark:bg-gray-800">
                                    <i data-lucide="package" class="w-6 h-6 text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">No consumables in inventory</p>
                                <p class="text-xs text-gray-400 mt-1">Add consumables to track levels.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Footer --}}
                @if($consumablesBreakdown->isNotEmpty())
                    <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800 text-center px-5 sm:px-6">
                        <a href="{{ route('consumables.index') }}"
                           class="text-xs font-semibold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-1">
                            Manage All Consumables
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                @endif

            </div>
        </div>

        <!-- Recent Activities (Assets) -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] flex flex-col min-w-0">
                <div>
                    <div class="flex items-center justify-between mb-4 px-5 pt-5 sm:px-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Asset Checkouts</h3>
                        <a href="{{ route('assets.index') }}" class="text-xs font-bold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-0.5">
                            View Assets <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <div class="flex-1 overflow-y-auto overflow-x-auto scrollbar-custom relative min-h-0 pr-1">
                        <table class="w-full table-fixed">
                            <thead class="border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[30%]">Item</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[15%]">Qty</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[35%]">To User</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[20%]">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                @forelse($recentCheckouts as $checkout)
                                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                        <td class="px-4 py-3.5">
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400 truncate">{{ $checkout->asset->name ?? 'Unknown' }}</span>
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate block mt-0.5">
                                                {{ optional(optional($checkout->asset)->product)->brand->name ?? 'No Brand' }} • {{ optional(optional($checkout->asset)->product)->name ?? 'No Product' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-brand-600 dark:text-brand-400 font-bold text-theme-sm">
                                            {{ $checkout->asset->quantity ?? 1 }} pcs
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($checkout->checkedOutToUser->name ?? 'N', 0, 1)) }}
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-gray-700 text-theme-sm dark:text-gray-350 font-semibold truncate leading-tight">{{ $checkout->checkedOutToUser->name ?? 'N/A' }}</span>
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate block mt-0.5 leading-tight">                                                {{ optional(optional($checkout->asset)->department)->name ?? 'No Dept' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-gray-500 text-theme-xs sm:text-theme-sm dark:text-gray-400">
                                            {{ $checkout->checked_out_at->format('M d, H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-2">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No recent asset checkouts</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">Asset checkouts will appear here once processed.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities (Consumables) -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pb-4 dark:border-gray-800 dark:bg-white/[0.03] h-full flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex items-center justify-between mb-4 px-5 pt-5 sm:px-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Consumable Checkouts</h3>
                        <a href="{{ route('consumable-transactions.index') }}" class="text-xs font-bold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-0.5">
                            View Transactions <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <div class="max-w-full overflow-x-auto h-80 overflow-y-auto scrollbar-custom relative pr-1">
                        <table class="w-full table-fixed">
                            <thead class="border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[30%]">Item</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[15%]">Qty</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[35%]">To User</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[20%]">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                @forelse($recentConsumableCheckouts as $transaction)
                                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                        <td class="px-4 py-3.5">
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400 truncate">{{ $transaction->consumable->product->name ?? 'Unknown' }}</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-brand-600 dark:text-brand-400 font-bold text-theme-sm">
                                            {{ $transaction->quantity }} pcs
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($transaction->checkedOutToUser->name ?? 'N', 0, 1)) }}
                                                </div>
                                                <span class="text-gray-700 text-theme-sm dark:text-gray-350 font-medium truncate">{{ $transaction->checkedOutToUser->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-gray-500 text-theme-xs sm:text-theme-sm dark:text-gray-400">
                                            {{ $transaction->transaction_date->format('M d, H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-2">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No recent consumable checkouts</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">Consumable checkouts will appear here once processed.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset > 4 Years -->
        <div class="col-span-12 xl:col-span-6 min-w-0">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pb-4 dark:border-gray-800 dark:bg-white/[0.03] h-full flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex items-center justify-between mb-4 px-5 pt-5 sm:px-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Assets Older Than 4 Years</h3>
                        <a href="{{ route('assets.index') }}" class="text-xs font-bold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-0.5">
                            View Assets <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <div class="max-w-full overflow-x-auto h-80 overflow-y-auto scrollbar-custom relative pr-1">
                        <table class="w-full table-fixed">
                            <thead class="border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[30%]">Item</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[15%]">Qty</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[35%]">To User</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start w-[20%]">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                @forelse($assetsOlderThan4Years as $asset)
                                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                        <td class="px-4 py-3.5">
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400 truncate">{{ $asset->name }}</span>
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate block mt-0.5">
                                                {{ optional(optional($asset->product)->brand)->name ?? 'No Brand' }} • {{ optional($asset->product)->name ?? 'No Product' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-brand-600 dark:text-brand-400 font-bold text-theme-sm">
                                            {{ $asset->quantity }} pcs
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($asset->checkedOutToUser->name ?? 'N', 0, 1)) }}
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-gray-700 text-theme-sm dark:text-gray-350 font-semibold truncate leading-tight">{{ $asset->checkedOutToUser->name ?? 'N/A' }}</span>
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate block mt-0.5 leading-tight">{{ optional($asset->department)->name ?? 'No Dept' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-gray-500 text-theme-xs sm:text-theme-sm dark:text-gray-400">
                                            {{ $asset->created_at->format('M d, H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-2">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No assets older than 4 years</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">Assets that are older than 4 years will appear here.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
        // Chart 1: Asset Status Distribution (Donut)
        const statusOptions = {
            series: @json($chartData['data']),
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: 'donut',
                height: 320,
            },
            labels: @json($chartData['labels']),
            colors: @json($chartData['colors']),
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
        const statusChart = new ApexCharts(document.querySelector("#assetStatusChart"), statusOptions);
        statusChart.render();

        // Chart 2: Assets by Department (Donut)
        const deptOptions = {
            series: @json($deptChartData['data']),
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: 'donut',
                height: 320,
            },
            labels: @json($deptChartData['labels']),
            colors: ['#465FFF', '#00F5D4', '#FF9F1C', '#FF5400', '#7209B7'],
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
                                label: 'Assets',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
        const deptChart = new ApexCharts(document.querySelector("#deptAssetChart"), deptOptions);
        deptChart.render();

        // Chart 3: Most Used Products (Donut)
        const productOptions = {
            series: @json($productDataByCategory['all']['data']),
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: 'donut',
                height: 320,
            },
            labels: @json($productDataByCategory['all']['labels']),
            colors: ['#00F5D4', '#7209B7', '#465FFF', '#FF5400', '#FF9F1C'],
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
                                label: 'Total Used',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
        window.productChartInstance = new ApexCharts(document.querySelector("#productAssetChart"), productOptions);
        window.productChartInstance.render();

        // Chart 5: Most Requested Consumables (Donut)
        @if(count($consumableChartData['data']) > 0)
        const consumableOptions = {
            series: @json($consumableChartData['data']),
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: 'donut',
                height: 320,
            },
            labels: @json($consumableChartData['labels']),
            colors: ['#7209B7', '#FF5400', '#FF9F1C', '#465FFF', '#00F5D4'],
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
                                label: 'Total Items',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
        const consumableChart = new ApexCharts(document.querySelector("#requestedConsumablesChart"), consumableOptions);
        consumableChart.render();
        @endif

        // Chart 4: Monthly Purchases Timeline (Area/Line)
        const purchaseOptions = {
            series: [{
                name: 'Assets Released',
                data: @json($purchaseChartData['data'])
            }],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: 'area',
                height: 320,
                toolbar: { show: false }
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
            xaxis: {
                categories: @json($purchaseChartData['labels']),
                labels: {
                    style: {
                        fontFamily: 'Outfit, sans-serif'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontFamily: 'Outfit, sans-serif'
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        };
        const purchaseChart = new ApexCharts(document.querySelector("#purchaseTimelineChart"), purchaseOptions);
        purchaseChart.render();
    });
</script>
@endif
@endpush





