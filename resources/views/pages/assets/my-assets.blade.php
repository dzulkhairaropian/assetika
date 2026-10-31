@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="My Assigned Assets" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => 'My Equipment']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Overview of IT hardware assets, laptops, and equipment assigned directly to your custody.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tickets.create') }}"
                   class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Report Hardware Issue
                </a>
            </div>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Main Table Container (Exact Style from pages.assets.index) --}}
        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Category Quick Tabs + Search Bar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                @php
                    $currentTab = request('tab');
                    $tabFilters = array_filter(request()->except(['page', 'tab']));
                    $tabs = [
                        ['key' => null,      'label' => 'All My Assets',   'count' => $summary['total'] ?? 0,      'icon' => 'layers',   'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'laptops', 'label' => 'Computers & PC',  'count' => $summary['laptops'] ?? 0,    'icon' => 'laptop',   'badge' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                        ['key' => 'other',   'label' => 'Peripherals',     'count' => $summary['other'] ?? 0,      'icon' => 'package',  'badge' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400'],
                        ['key' => 'bast',    'label' => 'BAST Documents',  'count' => $summary['bast_count'] ?? 0, 'icon' => 'file-text','badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'],
                    ];
                @endphp
                <nav class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-track]:bg-transparent">
                    @foreach($tabs as $tab)
                        @php
                            $isActive = $currentTab === $tab['key'];
                            $tabUrl = route('assets.my-assets', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                        @endphp
                        <a href="{{ $tabUrl }}"
                           class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all duration-150 {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                            {{ $tab['label'] }}
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('assets.my-assets') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, serial, product..."
                                class="h-9 w-60 sm:w-72 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @if(request()->filled('search'))
                            <a href="{{ route('assets.my-assets', request('tab') ? ['tab' => request('tab')] : []) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Showing <strong class="text-gray-900 dark:text-white">{{ $assets->firstItem() ?? 0 }}-{{ $assets->lastItem() ?? 0 }}</strong> of <strong class="text-gray-900 dark:text-white">{{ $assets->total() }}</strong> units
                        </span>
                    </div>
                </div>
            </div>

            {{-- Table View --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Asset Tag & Device</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Hardware & Model</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Serial & Specifications</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Assigned Date</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Location</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            @php
                                $statusName = optional($asset->status)->name ?? 'Deployed';
                                $color = optional($asset->status)->color ?? 'brand';
                                $icon = optional($asset->status)->icon ?? 'monitor';
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                {{-- Asset Tag & Device --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <a href="{{ route('assets.show', $asset) }}" class="font-bold font-mono text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400">
                                            {{ $asset->asset_tag }}
                                        </a>
                                        <span class="text-[11px] font-medium text-gray-800 dark:text-gray-200">
                                            {{ $asset->device_name ?: 'Unnamed Device' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Hardware Model --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ optional($asset->product->brand)->name ?? '' }} {{ optional($asset->product)->model ?? $asset->product?->name ?? 'Unknown Model' }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                            {{ optional(optional($asset->product)->category)->name ?? 'Hardware' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Serial & Spec --}}
                                <td class="px-4 py-2.5 max-w-xs">
                                    <div class="flex flex-col font-mono text-xs">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            S/N: {{ $asset->serial_number ?? '—' }}
                                        </span>
                                        @if($asset->specification)
                                            <span class="text-[11px] text-gray-400 font-sans truncate" title="{{ $asset->specification }}">
                                                {{ $asset->specification }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status Badge --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="$icon">{{ $statusName }}</x-ui.badge>
                                </td>

                                {{-- Assigned Date --}}
                                <td class="px-4 py-2.5 whitespace-nowrap font-mono text-gray-600 dark:text-gray-400">
                                    @if($asset->currentCheckout?->checked_out_at)
                                        {{ $asset->currentCheckout->checked_out_at->format('d/m/Y') }}
                                    @elseif($asset->release_date)
                                        {{ $asset->release_date->format('d/m/Y') }}
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Location --}}
                                <td class="px-4 py-2.5 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ optional($asset->location)->name ?? optional(optional($asset->currentCheckout)->location)->name ?? '—' }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($asset->currentCheckout)
                                            <a href="{{ route('assets.bast', [$asset->id, $asset->currentCheckout->id]) }}" target="_blank"
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-brand-600 shadow-xs hover:bg-brand-50 dark:border-gray-800 dark:bg-gray-950 dark:text-brand-400 dark:hover:bg-brand-500/10 transition"
                                               title="Download BAST Handover PDF">
                                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                                <span>BAST</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('tickets.create', ['asset_id' => $asset->id]) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 shadow-xs hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-400 transition"
                                           title="Report Hardware Issue for this Device">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                            <span>Report Issue</span>
                                        </a>
                                        <a href="{{ route('assets.show', $asset) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="View Details">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <i data-lucide="monitor" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No Assigned Assets Found</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        You currently don't have any IT hardware or equipment assigned directly to your custody.
                                    </p>
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
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                    </select>
                </div>
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
