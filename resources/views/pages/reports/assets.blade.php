@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Asset Report" />

    <!-- Header + Export -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Asset Report</h2>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Detailed report with multi-filter and export</p>
        </div>
        <a href="{{ route('reports.assets.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 transition">
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
    <form action="{{ route('reports.assets') }}" method="GET" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Filters</h3>
            <a href="{{ route('reports.assets') }}" class="text-xs text-brand-500 hover:text-brand-600">Clear All Filters</a>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tag, SN, Product #..."
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                <select name="status_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->id }}" {{ request('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Department</label>
                <select name="department_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <option value="">All Departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Location</label>
                <select name="location_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <option value="">All Locations</option>
                    @foreach($locations as $l)
                        <option value="{{ $l->id }}" {{ request('location_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                <select name="category_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Product</label>
                <select name="product_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->brand->name ?? '' }} - {{ $p->model }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Brand</label>
                <select name="brand_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Vendor</label>
                <select name="vendor_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Aging</label>
                <select name="aging" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
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
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Purchase From</label>
                <input type="date" name="purchase_from" value="{{ request('purchase_from') }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Purchase To</label>
                <input type="date" name="purchase_to" value="{{ request('purchase_to') }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Cost Min (Rp)</label>
                <input type="number" name="cost_min" value="{{ request('cost_min') }}" placeholder="0" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Cost Max (Rp)</label>
                <input type="number" name="cost_max" value="{{ request('cost_max') }}" placeholder="999999999" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                <i data-feather="filter" class="w-4 h-4"></i> Apply Filters
            </button>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full">
                <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Asset Tag</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Device Name</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Brand / Model</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Category</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Serial Number</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Department</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Location</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Holder</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">OS</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Purchase Date</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Warranty</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Cost</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Book Value</th>
                        <th class="px-4 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start whitespace-nowrap">Age</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($assets as $a)
                        @php
                            $date = $a->release_date ?? $a->purchase_date;
                            $ageYears = $date ? (int) floor($date->diffInDays(now()) / 365.25) : null;
                            $warrantyPast = $a->warranty_expiry_date && $a->warranty_expiry_date->isPast();
                            $warranty30d = $a->warranty_expiry_date && !$a->warranty_expiry_date->isPast() && $a->warranty_expiry_date->diffInDays() <= 30;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-mono font-semibold text-brand-600 dark:text-brand-400 whitespace-nowrap">{{ $a->asset_tag }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $a->device_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ optional($a->product->brand)->name ?? '-' }} {{ optional($a->product)->model ?? '' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ optional($a->product->category)->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap font-mono text-xs">{{ $a->serial_number ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusName = optional($a->status)->name;
                                    $statusColor = optional($a->status)->color;
                                @endphp
                                <x-ui.badge :color="$statusColor" variant="light" size="sm" :featherIcon="optional($a->status)->icon">{{ $statusName }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ optional($a->department->company)->code ?? '' }} {{ $a->department->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $a->location->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                @if($a->currentCheckout && $a->currentCheckout->checkedOutToUser)
                                    {{ $a->currentCheckout->checkedOutToUser->name }}
                                @else
                                    <span class="italic text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ optional($a->operatingSystem)->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $a->purchase_date ? $a->purchase_date->format('d M Y') : '-' }}</td>
                            <td class="px-4 py-3">
                                @if(!$a->warranty_expiry_date)
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @elseif($warrantyPast)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-500">Expired</span>
                                @elseif($warranty30d)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-theme-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">Expiring {{ $a->warranty_expiry_date->diffInDays() }}d</span>
                                @else
                                    <span class="text-xs text-gray-500">{{ $a->warranty_expiry_date->format('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 font-semibold whitespace-nowrap">
                                {{ $a->purchase_cost ? 'Rp ' . number_format($a->purchase_cost, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm {{ ($a->bookValue ?? 0) == 0 ? 'text-error-500' : 'text-brand-500' }} font-semibold whitespace-nowrap">
                                {{ $a->purchase_cost ? 'Rp ' . number_format($a->bookValue ?? 0, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                @if($ageYears !== null)
                                    <span class="font-semibold {{ $ageYears >= 4 ? 'text-error-700' : ($ageYears >= 2 ? 'text-amber-700' : 'text-emerald-700') }}">
                                        {{ $ageYears }}y
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="hard-drive" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No assets found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-6 dark:border-gray-800">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} results
                </p>
                {{ $assets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection