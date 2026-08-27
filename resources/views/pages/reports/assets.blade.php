@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Asset Inventory Report" :items="[['label' => 'Reports', 'url' => route('reports.index')]]" />

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between -mt-3">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Complete hardware inventory list with multi-criteria filtering and Excel export.
        </p>
        <a href="{{ route('reports.assets.export', request()->query()) }}" class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
            <i data-feather="download" class="w-3.5 h-3.5"></i> Export Excel (.xlsx)
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 min-w-0">
        <x-common.stat-card title="Total Assets" icon="hard-drive" :value="$totalCount" description="Matching filtered records" />
        <x-common.stat-card title="Total Purchase Cost" icon="dollar-sign" value="Rp {{ number_format($totalPurchaseCost, 0, ',', '.') }}" description="Sum of purchase costs" />
        <x-common.stat-card title="Avg Asset Age" icon="clock" value="{{ $averageAgeYears }} yrs" description="Average age (release/purchase)" />
        <x-common.stat-card title="Records Shown" icon="file-text" value="{{ $assets->count() }}" description="On this page (max 50)" />
    </div>

    <!-- Filter Card -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-1.5">
                    <i data-feather="sliders" class="w-3.5 h-3.5 text-brand-500"></i> Multi-Criteria Filters
                </h3>
                <a href="{{ route('reports.assets') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 transition">Clear All Filters</a>
            </div>

            <form action="{{ route('reports.assets') }}" method="GET" class="space-y-3">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 text-left">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Search Keyword</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tag, SN, Product #..."
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Status</label>
                        <select name="status_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s->id }}" {{ request('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Department</label>
                        <select name="department_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">All Departments</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Location</label>
                        <select name="location_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">All Locations</option>
                            @foreach($locations as $l)
                                <option value="{{ $l->id }}" {{ request('location_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        <i data-feather="filter" class="w-3.5 h-3.5"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset Tag</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Device / Model</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Department & Location</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Holder</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Cost & Book Value</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Age</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($assets as $a)
                        @php
                            $date = $a->release_date ?? $a->purchase_date;
                            $ageYears = $date ? (int) floor($date->diffInDays(now()) / 365.25) : null;
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5 font-mono font-semibold text-brand-600 dark:text-brand-400">
                                <a href="{{ route('assets.show', $a) }}" class="hover:underline">
                                    {{ $a->asset_tag }}
                                </a>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $a->device_name }}</span>
                                    <span class="text-[11px] text-gray-400">{{ optional($a->product->brand)->name ?? '' }} {{ optional($a->product)->model ?? '' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                {{ optional($a->product->category)->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5">
                                <x-ui.badge :color="optional($a->status)->color" variant="light" size="sm" :featherIcon="optional($a->status)->icon">
                                    {{ optional($a->status)->name ?? 'Draft' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $a->department->name ?? '-' }}</span>
                                    <span class="text-[11px] text-gray-400">{{ $a->location->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 font-medium">
                                @if($a->currentCheckout && $a->currentCheckout->checkedOutToUser)
                                    {{ $a->currentCheckout->checkedOutToUser->name }}
                                @else
                                    <span class="text-[11px] text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $a->purchase_cost ? 'Rp ' . number_format($a->purchase_cost, 0, ',', '.') : '-' }}
                                    </span>
                                    <span class="text-[11px] font-semibold {{ ($a->bookValue ?? 0) == 0 ? 'text-red-600 dark:text-red-400' : 'text-brand-600 dark:text-brand-400' }}">
                                        Val: {{ $a->purchase_cost ? 'Rp ' . number_format($a->bookValue ?? 0, 0, ',', '.') : '-' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($ageYears !== null)
                                    <span class="font-semibold {{ $ageYears >= 4 ? 'text-red-600 dark:text-red-400' : ($ageYears >= 2 ? 'text-orange-600 dark:text-orange-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                        {{ $ageYears }}y
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-xs text-gray-400">
                                No assets found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
