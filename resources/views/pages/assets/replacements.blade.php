@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Hardware Replacement Planner" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => 'Replacement Recommendations']]" />
                <p class="-mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Review, forecast, and plan equipment refresh cycles for laptops and hardware reaching 4+ year lifecycles.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('assets.replacements.export', request()->all()) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-gray-500"></i>
                    Export Proposal (Excel)
                </a>
                <a href="{{ route('assets.index', ['tab' => 'aged']) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                    <i data-lucide="list" class="w-3.5 h-3.5"></i>
                    All Aged Assets
                </a>
            </div>
        </div>

        {{-- Bento Fleet Metric Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Due for Replacement</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <i data-lucide="laptop" class="h-4 w-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ number_format($totalUnits) }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">units (≥4y)</span>
                </div>
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Hardware reaching standard end-of-life cycle</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Critical Priority</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <i data-lucide="alert-triangle" class="h-4 w-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl font-bold tracking-tight text-red-600 dark:text-red-400">{{ number_format($criticalUnits) }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">units</span>
                </div>
                <p class="mt-1 text-[11px] text-red-500/80 dark:text-red-400/80">≥5 years old or currently in broken/service state</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">High Priority</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
                        <i data-lucide="clock" class="h-4 w-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl font-bold tracking-tight text-orange-600 dark:text-orange-400">{{ number_format($highUnits) }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">units</span>
                </div>
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Active deployment reaching 4th year</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Est. Refresh Budget</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <i data-lucide="banknote" class="h-4 w-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Rp {{ number_format($totalReplacementBudget, 0, ',', '.') }}</span>
                </div>
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Baseline replacement procurement forecast</p>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Toolbar: Device Type Switcher & Search / Filter --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    {{-- Device Scope Tabs --}}
                    <nav class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-950">
                        <a href="{{ route('assets.replacements', array_merge(request()->except(['page', 'type']), ['type' => 'laptop'])) }}"
                           class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition {{ $deviceType === 'laptop' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            <i data-lucide="laptop" class="w-3.5 h-3.5"></i>
                            Laptops & Notebooks
                        </a>
                        <a href="{{ route('assets.replacements', array_merge(request()->except(['page', 'type']), ['type' => 'all'])) }}"
                           class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition {{ $deviceType === 'all' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
                            All IT Hardware
                        </a>
                    </nav>

                    {{-- Priority Filter Pills --}}
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-gray-400 text-[11px] mr-1">Priority:</span>
                        <a href="{{ route('assets.replacements', array_merge(request()->except(['page', 'priority']), [])) }}"
                           class="rounded-full px-2.5 py-1 text-[11px] font-medium transition {{ empty($priorityFilter) ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                            All
                        </a>
                        <a href="{{ route('assets.replacements', array_merge(request()->except(['page', 'priority']), ['priority' => 'critical'])) }}"
                           class="rounded-full px-2.5 py-1 text-[11px] font-medium transition {{ $priorityFilter === 'critical' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-400' }}">
                            Critical Only
                        </a>
                        <a href="{{ route('assets.replacements', array_merge(request()->except(['page', 'priority']), ['priority' => 'high'])) }}"
                           class="rounded-full px-2.5 py-1 text-[11px] font-medium transition {{ $priorityFilter === 'high' ? 'bg-orange-600 text-white' : 'bg-orange-50 text-orange-700 hover:bg-orange-100 dark:bg-orange-950/40 dark:text-orange-400' }}">
                            High Only
                        </a>
                    </div>
                </div>

                {{-- Search & Sub-Filters --}}
                <form action="{{ route('assets.replacements') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                    <input type="hidden" name="type" value="{{ $deviceType }}">
                    @if(request('priority'))
                        <input type="hidden" name="priority" value="{{ request('priority') }}">
                    @endif

                    <div class="relative flex items-center">
                        <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, device, serial, user..."
                               class="h-9 w-60 sm:w-72 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition">
                    </div>

                    <div class="relative" @click.away="filterOpen = false">
                        <button type="button" @click="filterOpen = !filterOpen"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                            Filters
                        </button>

                        <div x-show="filterOpen" x-cloak
                             class="absolute left-0 z-30 mt-2 w-80 rounded-xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">Company</label>
                                    <select name="company_id" class="mt-1 h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                        <option value="">All Companies</option>
                                        @foreach($companies as $comp)
                                            <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">Department</label>
                                    <select name="department_id" class="mt-1 h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($deviceType !== 'laptop')
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">Category</label>
                                        <select name="category_id" class="mt-1 h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <a href="{{ route('assets.replacements', ['type' => $deviceType]) }}" class="text-xs text-gray-500 hover:text-brand-600 transition">
                                        Reset
                                    </a>
                                    <button type="submit" @click="filterOpen = false" class="rounded-lg bg-brand-500 px-3 py-1 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="h-9 rounded-lg bg-gray-100 px-3 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                        Search
                    </button>
                </form>
            </div>

            {{-- Replacement Assets Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 bg-gray-50/75 text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Asset Tag & Device</th>
                            <th class="px-4 py-3">Model & Specification</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Age & Acquisition</th>
                            <th class="px-4 py-3">Current Holder</th>
                            <th class="px-4 py-3">Company & Dept</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Est. Cost</th>
                            <th class="px-4 py-3 text-right">Actions</th>
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
                            @endphp
                            <tr class="transition-colors hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <a href="{{ route('assets.show', $asset) }}" class="font-mono font-semibold tracking-tight text-brand-500 hover:text-brand-600 hover:underline">
                                            {{ $asset->asset_tag }}
                                        </a>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $asset->device_name ?? 'Unnamed Device' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $asset->product?->brand?->name ?? '-' }} {{ $asset->product?->model ?? '' }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 truncate max-w-xs" title="{{ $asset->specification }}">
                                            {{ $asset->specification ?? 'S/N: ' . ($asset->serial_number ?? '-') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($isCritical)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-[11px] font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                            <i data-lucide="alert-circle" class="w-3 h-3 text-red-600 dark:text-red-400"></i>
                                            Critical (≥5y / Service)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-0.5 text-[11px] font-semibold text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">
                                            <i data-lucide="clock" class="w-3 h-3 text-orange-600 dark:text-orange-400"></i>
                                            High (4-5y)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $ageYears }} Years Old</span>
                                        <span class="text-[11px] text-gray-400">
                                            {{ $acquisitionDate ? $acquisitionDate->format('d M Y') : 'Unknown Date' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            {{ strtoupper(substr($holder, 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $holder }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $asset->department?->company?->code ?? $asset->department?->company?->name ?? '-' }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">{{ $asset->department?->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-ui.badge :color="optional($asset->status)->color" variant="light" size="sm">
                                        {{ optional($asset->status)->name ?? 'Unknown' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-mono text-gray-700 dark:text-gray-300">
                                        {{ $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 0, ',', '.') : 'Rp 15.000.000' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('assets.show', $asset) }}"
                                           class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2.5 py-1 text-[11px] font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                                           title="View Details">
                                            <i data-lucide="eye" class="w-3 h-3"></i>
                                            View
                                        </a>
                                        @if(in_array(strtolower(optional($asset->status)->name ?? ''), ['ready to deploy', 'deployed', 'borrowed']))
                                            <a href="{{ route('assets.checkout', $asset) }}"
                                               class="inline-flex items-center gap-1 rounded-md bg-brand-50 border border-brand-200/80 px-2 py-1 text-[11px] font-semibold text-brand-600 hover:bg-brand-100 dark:bg-brand-950/40 dark:text-brand-400 dark:border-brand-800/40 transition"
                                               title="Asset Movement / Handover">
                                                <i data-lucide="arrow-right-left" class="w-3 h-3"></i>
                                                Movement
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <i data-lucide="check-circle" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">All Hardware Up to Date</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        No assets found matching the 4+ year replacement threshold. Your active equipment fleet is within the standard lifecycle.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assets->hasPages())
                <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                    {{ $assets->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
