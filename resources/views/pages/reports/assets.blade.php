@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Asset Inventory Report" :items="[['label' => 'Reports', 'url' => route('reports.index')]]" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between -mt-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Complete hardware inventory list with multi-criteria filtering and Excel export.
        </p>
        <a href="{{ route('reports.assets.export', request()->query()) }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">
            <i data-feather="download" class="w-4 h-4"></i> Export Excel (.xlsx)
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-common.stat-card title="Total Assets" :value="$totalCount" description="Matching filtered records" />
        <x-common.stat-card title="Total Purchase Cost" value="Rp {{ number_format($totalPurchaseCost, 0, ',', '.') }}" description="Sum of purchase costs" />
        <x-common.stat-card title="Avg Asset Age" value="{{ $averageAgeYears }} yrs" description="Average age (release/purchase)" />
        <x-common.stat-card title="Records Shown" value="{{ $assets->count() }}" description="On this page (max 50)" />
    </div>

    <!-- Filter Card -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                    <i data-feather="sliders" class="w-4 h-4 text-brand-500"></i> Multi-Criteria Filters
                </h3>
                <a href="{{ route('reports.assets') }}" class="text-xs text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">Clear All Filters</a>
            </div>

            <form action="{{ route('reports.assets') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Search Keyword</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tag, SN, Product #..."
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                        <div class="relative">
                            <select name="status_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s->id }}" {{ request('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Department</label>
                        <div class="relative">
                            <select name="department_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Departments</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Location</label>
                        <div class="relative">
                            <select name="location_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Locations</option>
                                @foreach($locations as $l)
                                    <option value="{{ $l->id }}" {{ request('location_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                        <div class="relative">
                            <select name="category_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Categories</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Brand</label>
                        <div class="relative">
                            <select name="brand_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Brands</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Aging</label>
                        <div class="relative">
                            <select name="aging" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Aging</option>
                                <option value="0-1" {{ request('aging') == '0-1' ? 'selected' : '' }}>&lt; 1 Year</option>
                                <option value="1-2" {{ request('aging') == '1-2' ? 'selected' : '' }}>1 - 2 Years</option>
                                <option value="2-3" {{ request('aging') == '2-3' ? 'selected' : '' }}>2 - 3 Years</option>
                                <option value="3-4" {{ request('aging') == '3-4' ? 'selected' : '' }}>3 - 4 Years</option>
                                <option value="4+" {{ request('aging') == '4+' ? 'selected' : '' }}>&gt; 4 Years</option>
                                <option value="warranty_expired" {{ request('aging') == 'warranty_expired' ? 'selected' : '' }}>Warranty Expired</option>
                                <option value="warranty_30d" {{ request('aging') == 'warranty_30d' ? 'selected' : '' }}>Warranty Expiring (30d)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Vendor</label>
                        <div class="relative">
                            <select name="vendor_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                        <i data-feather="filter" class="w-4 h-4"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset Tag</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Device / Model</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Category</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Department & Location</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Holder</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Cost & Book Value</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Age</p>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($assets as $a)
                        @php
                            $date = $a->release_date ?? $a->purchase_date;
                            $ageYears = $date ? (int) floor($date->diffInDays(now()) / 365.25) : null;
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6 font-mono font-semibold text-brand-600 dark:text-brand-400 text-theme-sm">
                                <a href="{{ route('assets.show', $a) }}" class="hover:underline">
                                    {{ $a->asset_tag }}
                                </a>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col">
                                    <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $a->device_name }}</span>
                                    <span class="text-xs text-gray-500">{{ optional($a->product->brand)->name ?? '' }} {{ optional($a->product)->model ?? '' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-600 dark:text-gray-400">
                                {{ optional($a->product->category)->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge :color="optional($a->status)->color" variant="light" size="sm" :featherIcon="optional($a->status)->icon">
                                    {{ optional($a->status)->name ?? 'Draft' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col">
                                    <span class="text-theme-sm text-gray-700 dark:text-gray-300">{{ $a->department->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-400">{{ $a->location->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-600 dark:text-gray-400">
                                @if($a->currentCheckout && $a->currentCheckout->checkedOutToUser)
                                    {{ $a->currentCheckout->checkedOutToUser->name }}
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col">
                                    <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $a->purchase_cost ? 'Rp ' . number_format($a->purchase_cost, 0, ',', '.') : '-' }}
                                    </span>
                                    <span class="text-xs font-semibold {{ ($a->bookValue ?? 0) == 0 ? 'text-error-500' : 'text-brand-500' }}">
                                        Val: {{ $a->purchase_cost ? 'Rp ' . number_format($a->bookValue ?? 0, 0, ',', '.') : '-' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if($ageYears !== null)
                                    <span class="font-semibold text-theme-sm {{ $ageYears >= 4 ? 'text-error-600' : ($ageYears >= 2 ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $ageYears }}y
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="hard-drive" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No assets found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your multi-criteria filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection