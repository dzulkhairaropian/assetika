@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="My Assigned Assets" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Overview of IT hardware assets, laptops, and equipment assigned directly to your account.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-common.small-box
                title="Total Assigned Assets"
                value="{{ number_format($summary['total'] ?? 0) }}"
                variant="info"
                icon="hard-drive"
                route="{{ route('assets.my-assets') }}"
                linkText="My Inventory"
            />
            <x-common.small-box
                title="Laptops & Workstations"
                value="{{ number_format($summary['laptops'] ?? 0) }}"
                variant="primary"
                icon="laptop"
                route="{{ route('assets.my-assets') }}"
                linkText="Primary Computers"
            />
            <x-common.small-box
                title="Peripherals & Accessories"
                value="{{ number_format($summary['other'] ?? 0) }}"
                variant="success"
                icon="package"
                route="{{ route('assets.my-assets') }}"
                linkText="Assigned Accessories"
            />
            <x-common.small-box
                title="BAST Handover Records"
                value="{{ number_format($summary['bast_count'] ?? 0) }}"
                variant="warning"
                icon="file-text"
                route="{{ route('assets.my-assets') }}"
                linkText="Digital Handover Docs"
            />
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('assets.my-assets') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, serial, product..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>

                        @if(request()->filled('search'))
                            <a href="{{ route('assets.my-assets') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">Clear Search</a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Report Issue / Create Ticket
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset Tag / Name</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Product Model</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Serial / Specification</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Assigned Date</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Location</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <a href="{{ route('assets.show', $asset) }}" class="font-mono font-bold text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400">
                                            {{ $asset->asset_tag }}
                                        </a>
                                        <span class="text-[11px] font-medium text-gray-800 dark:text-gray-200">{{ $asset->device_name ?? 'Unnamed Device' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ optional($asset->product->brand)->name ?? '' }} {{ optional($asset->product)->model ?? $asset->product?->name ?? 'Unknown Model' }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">{{ optional(optional($asset->product)->category)->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 font-mono">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">S/N: {{ $asset->serial_number ?? '-' }}</span>
                                        @if($asset->specification)
                                            <span class="text-[11px] text-gray-400 font-sans truncate max-w-xs" title="{{ $asset->specification }}">
                                                {{ \Illuminate\Support\Str::limit($asset->specification, 45) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @php
                                        $statusName = optional($asset->status)->name ?? 'Deployed';
                                        $color = optional($asset->status)->color ?? 'brand';
                                        $icon = optional($asset->status)->icon ?? 'monitor';
                                    @endphp
                                    <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="$icon">{{ $statusName }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap font-mono">
                                    @if($asset->currentCheckout?->checked_out_at)
                                        {{ $asset->currentCheckout->checked_out_at->format('d/m/Y') }}
                                    @elseif($asset->release_date)
                                        {{ $asset->release_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ optional($asset->location)->name ?? optional(optional($asset->currentCheckout)->location)->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($asset->currentCheckout)
                                            <a href="{{ route('assets.bast', [$asset->id, $asset->currentCheckout->id]) }}" target="_blank"
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-brand-600 shadow-sm hover:bg-brand-50 dark:border-gray-800 dark:bg-gray-950 dark:text-brand-400 dark:hover:bg-brand-500/10 transition"
                                               title="Download BAST PDF Document">
                                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                                <span>BAST</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('tickets.create', ['asset_id' => $asset->id]) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 shadow-sm hover:bg-amber-100 dark:border-amber-900/30 dark:bg-amber-500/10 dark:text-amber-400 transition"
                                           title="Report Hardware Issue for this Asset">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                            <span>Report Issue</span>
                                        </a>
                                        <a href="{{ route('assets.show', $asset) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="View Asset Details">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="hard-drive" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">You don't have any assigned assets</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">IT hardware assets and equipment assigned directly to your account will appear here.</p>
                                    </div>
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
                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
