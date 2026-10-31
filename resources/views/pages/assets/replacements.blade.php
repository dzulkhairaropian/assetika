@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Hardware Replacement Planner" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => 'Replacement Planner']]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Forecast equipment lifecycle, identify aging fleets (≥4y), and plan hardware refresh cycles.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Device Tabs + Filters Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                @php
                    $deviceTabs = [
                        ['key' => 'laptop',  'label' => 'Laptops',      'icon' => 'laptop'],
                        ['key' => 'desktop', 'label' => 'Desktops',     'icon' => 'monitor'],
                        ['key' => 'display', 'label' => 'Displays',     'icon' => 'tv'],
                        ['key' => 'all',     'label' => 'All Hardware', 'icon' => 'layers'],
                    ];
                @endphp
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <nav class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-track]:bg-transparent">
                        @foreach($deviceTabs as $dTab)
                            @php
                                $isActive = $deviceType === $dTab['key'];
                                $tabUrl = route('assets.replacements', array_merge(request()->except(['page', 'type']), ['type' => $dTab['key']]));
                            @endphp
                            <a href="{{ $tabUrl }}"
                               class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all duration-150 {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                                <i data-lucide="{{ $dTab['icon'] }}" class="w-3.5 h-3.5"></i>
                                {{ $dTab['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-gray-400 text-[11px] mr-1">Priority:</span>
                        <a href="{{ route('assets.replacements', array_merge(request()->except(['page', 'priority']), [])) }}"
                           class="rounded-full px-2.5 py-1 text-[11px] font-medium transition {{ empty($priorityFilter) ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                            All ({{ number_format($totalUnits) }})
                        </a>
                        <a href="{{ route('assets.replacements', array_merge(request()->except(['page', 'priority']), ['priority' => 'critical'])) }}"
                           class="rounded-full px-2.5 py-1 text-[11px] font-medium transition {{ $priorityFilter === 'critical' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-400' }}">
                            Critical ({{ number_format($criticalUnits) }})
                        </a>
                        <a href="{{ route('assets.replacements', array_merge(request()->except(['page', 'priority']), ['priority' => 'high'])) }}"
                           class="rounded-full px-2.5 py-1 text-[11px] font-medium transition {{ $priorityFilter === 'high' ? 'bg-orange-600 text-white' : 'bg-orange-50 text-orange-700 hover:bg-orange-100 dark:bg-orange-950/40 dark:text-orange-400' }}">
                            High ({{ number_format($highUnits) }})
                        </a>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('assets.replacements') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        <input type="hidden" name="type" value="{{ $deviceType }}">
                        @if(request('priority')) <input type="hidden" name="priority" value="{{ request('priority') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, device, serial, user..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('company_id')) $activeCount++;
                            if (request('department_id')) $activeCount++;
                            if (request('category_id')) $activeCount++;
                        @endphp

                        <div class="relative" @click.away="filterOpen = false">
                            <button type="button" @click="filterOpen = !filterOpen"
                                class="relative inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                Filters
                                @if($activeCount > 0)
                                    <span class="inline-flex items-center justify-center min-w-[18px] h-4 px-1 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                                @endif
                            </button>

                            <div x-show="filterOpen" x-transition
                                class="absolute left-0 top-full z-50 mt-2 w-80 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                style="display: none;">
                                <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="space-y-3 text-left">
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Company</label>
                                        <select name="company_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Companies</option>
                                            @foreach($companies as $comp)
                                                <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Department</label>
                                        <select name="department_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Departments</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if($deviceType !== 'laptop')
                                        <div>
                                            <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Category</label>
                                            <select name="category_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                                <option value="">All Categories</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('assets.replacements', ['type' => $deviceType]) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Reset
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(request()->filled('search') || $activeCount > 0)
                            <a href="{{ route('assets.replacements', ['type' => $deviceType]) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('assets.replacements.export', request()->all()) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition-all">
                            <i data-lucide="download" class="w-3.5 h-3.5 text-gray-500"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Asset Tag & Device</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Hardware & Specification</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Priority</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Age & Acquisition</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Assigned Holder</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Company & Dept</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Est. Cost</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            @php
                                $ageYears = $asset->age_years ?? 4.0;
                                $priority = $asset->replacement_priority ?? 'High';
                                $isCritical = $priority === 'Critical';
                                $holder = $asset->currentCheckout?->checkedOutToUser?->name ?? $asset->assignedToUser?->name ?? '-';
                                $acquisitionDate = $asset->release_date ?? $asset->purchase_date;

                                if ($isCritical) {
                                    $rowBg = 'bg-red-50/40 dark:bg-red-500/5 hover:bg-red-100/40 dark:hover:bg-red-500/10';
                                } else {
                                    $rowBg = 'hover:bg-gray-50/80 dark:hover:bg-white/[0.02]';
                                }
                            @endphp
                            <tr class="transition-colors duration-150 {{ $rowBg }}">
                                {{-- Asset Tag & Device --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <a href="{{ route('assets.show', $asset) }}" class="font-bold font-mono text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400">
                                            {{ $asset->asset_tag }}
                                        </a>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">
                                            {{ $asset->device_name ?: 'Unnamed Device' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Model & Specification --}}
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $asset->product?->brand?->name ?? '-' }} {{ $asset->product?->model ?? '' }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 truncate max-w-xs font-mono" title="{{ $asset->specification }}">
                                            {{ $asset->specification ?? 'S/N: ' . ($asset->serial_number ?? '-') }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Priority --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($isCritical)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-[11px] font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                            <i data-lucide="alert-circle" class="w-3 h-3 text-red-600 dark:text-red-400"></i>
                                            Critical (≥{{ ($asset->useful_life_years ?? 4) + 1 }}y / Service)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-0.5 text-[11px] font-semibold text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">
                                            <i data-lucide="clock" class="w-3 h-3 text-orange-600 dark:text-orange-400"></i>
                                            High (≥{{ $asset->useful_life_years ?? 4 }}y)
                                        </span>
                                    @endif
                                </td>

                                {{-- Age & Acquisition --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $ageYears }}y <span class="text-xs font-normal text-gray-400">(Tgt: {{ $asset->useful_life_years ?? 4 }}y)</span></span>
                                        <span class="text-[11px] text-gray-400 font-mono">
                                            {{ $acquisitionDate ? $acquisitionDate->format('d M Y') : 'Unknown Date' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Holder --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <span class="text-gray-900 dark:text-white font-medium">{{ $holder }}</span>
                                </td>

                                {{-- Company & Dept --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $asset->department?->company?->name ?? '-' }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">{{ $asset->department?->name ?? '-' }}</span>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <x-ui.badge :color="optional($asset->status)->color" variant="light" size="sm">
                                        {{ optional($asset->status)->name ?? 'Unknown' }}
                                    </x-ui.badge>
                                </td>

                                {{-- Cost --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <span class="font-mono text-gray-700 dark:text-gray-300">
                                        {{ $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 0, ',', '.') : 'Rp 15.000.000' }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('assets.show', $asset) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                                           title="View Details">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                                            View
                                        </a>
                                        @if(in_array(strtolower(optional($asset->status)->name ?? ''), ['ready to deploy', 'deployed', 'borrowed']))
                                            <a href="{{ route('assets.checkout', $asset) }}"
                                               class="inline-flex items-center gap-1 rounded-lg bg-brand-50 border border-brand-200 px-2.5 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-100 dark:bg-brand-950/40 dark:text-brand-400 dark:border-brand-800/40 transition"
                                               title="Asset Movement / Handover">
                                                <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                                                Movement
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                    No assets found matching replacement threshold.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Per page:</span>
                    <select onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();"
                        class="h-8 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
