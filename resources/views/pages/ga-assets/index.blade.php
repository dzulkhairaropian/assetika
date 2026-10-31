@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
        selectedItems: [],
        selectAll: false,
        toggleAll() {
            this.selectAll = !this.selectAll;
            if (this.selectAll) {
                this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(el => JSON.parse(el.dataset.asset));
            } else {
                this.selectedItems = [];
            }
        },
        toggleItem(asset) {
            const index = this.selectedItems.findIndex(a => a.id === asset.id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(asset);
            }
            this.selectAll = this.selectedItems.length === document.querySelectorAll('.row-checkbox').length;
        }
    }">
        <x-common.page-breadcrumb pageTitle="General Affairs Asset Management" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage General Affairs physical assets (vehicles, office furniture, appliances, and fixtures).
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Vehicle Tax / Warranty Alert Banners --}}
        @if(($taxDueVehiclesCount ?? 0) > 0 || ($warrantyExpiringCount ?? 0) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @if(($taxDueVehiclesCount ?? 0) > 0)
                    <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-3.5 dark:border-amber-900/30 dark:bg-amber-500/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white">
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                </div>
                                <h4 class="text-xs font-bold text-amber-800 dark:text-amber-300">Pajak Kendaraan Jatuh Tempo ({{ $taxDueVehiclesCount }})</h4>
                            </div>
                            <a href="{{ route('ga-assets.index', ['filter' => 'tax_due']) }}" class="text-[11px] font-semibold text-amber-800 underline hover:text-amber-900">Filter List</a>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($taxDueVehicles->take(5) as $v)
                                @if($v->asset)
                                    <a href="{{ route('ga-assets.show', $v->asset) }}" class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-[11px] font-semibold text-amber-900 shadow-xs border border-amber-200 hover:bg-amber-100 dark:bg-gray-900 dark:text-amber-300 dark:border-amber-800">
                                        <span>{{ $v->license_plate ?: $v->asset->asset_code }}</span>
                                        <span class="text-[10px] text-gray-500 font-mono">({{ $v->tax_due_date ? $v->tax_due_date->format('d M Y') : '-' }})</span>
                                    </a>
                                @endif
                            @endforeach
                            @if($taxDueVehiclesCount > 5)
                                <span class="text-[10px] text-amber-700 self-center">+{{ $taxDueVehiclesCount - 5 }} lainnya</span>
                            @endif
                        </div>
                    </div>
                @endif
                @if(($warrantyExpiringCount ?? 0) > 0)
                    <div class="rounded-xl border border-blue-200 bg-blue-50/80 p-3.5 dark:border-blue-900/30 dark:bg-blue-500/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-500 text-white">
                                    <i data-lucide="shield-off" class="w-3.5 h-3.5"></i>
                                </div>
                                <h4 class="text-xs font-bold text-blue-800 dark:text-blue-300">Garansi Aset GA Expiring ({{ $warrantyExpiringCount }})</h4>
                            </div>
                            <a href="{{ route('ga-assets.index', ['filter' => 'warranty_expiring']) }}" class="text-[11px] font-semibold text-blue-800 underline hover:text-blue-900">Filter List</a>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($warrantyExpiringAssets->take(5) as $item)
                                <a href="{{ route('ga-assets.show', $item) }}" class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-[11px] font-semibold text-blue-900 shadow-xs border border-blue-200 hover:bg-blue-100 dark:bg-gray-900 dark:text-blue-300 dark:border-blue-800">
                                    <span>{{ $item->asset_code }} - {{ Str::limit($item->name, 15) }}</span>
                                    <span class="text-[10px] text-gray-500 font-mono">({{ $item->warranty_expiry_date ? $item->warranty_expiry_date->format('d M Y') : '-' }})</span>
                                </a>
                            @endforeach
                            @if($warrantyExpiringCount > 5)
                                <span class="text-[10px] text-blue-700 self-center">+{{ $warrantyExpiringCount - 5 }} lainnya</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Bulk Delete Form --}}
        <form id="bulkDeleteForm" action="{{ route('ga-assets.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="asset in selectedItems" :key="asset.id">
                <input type="hidden" name="ids[]" :value="asset.id">
            </template>
        </form>

        {{-- Bulk Action Bar --}}
        <div x-show="selectedItems.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 p-3 shadow-xs dark:border-brand-900/50 dark:bg-brand-950/40"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> assets selected.
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected assets?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        {{-- Main Table Container (Exact Asset Index Layout) --}}
        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('ga-assets.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code, asset name, plate..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('category_id')) $activeCount++;
                            if (request('status')) $activeCount++;
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
                                        <x-form.searchable-select name="category_id" label="Category" :options="$categories->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray()" :selected="request('category_id')" placeholder="All Categories" />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Status</label>
                                        <select name="status"
                                            class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Statuses</option>
                                            @foreach(['Available', 'Deployed', 'In Repair', 'Disposed'] as $status)
                                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('ga-assets.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear All Filters
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(request()->filled('search') || $activeCount > 0)
                            <a href="{{ route('ga-assets.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('ga-assets.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add GA Asset
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">
                                <label class="flex cursor-pointer items-center select-none">
                                    <div class="relative">
                                        <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                        <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                            class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                            <span :class="selectAll ? '' : 'opacity-0'">
                                                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Asset Code</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Name & Model</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Category</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Acquisition Cost</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            @php
                                $statusSlug = strtolower($asset->status);
                                if ($statusSlug === 'available') {
                                    $color = 'success';
                                } elseif ($statusSlug === 'deployed') {
                                    $color = 'brand';
                                } elseif (in_array($statusSlug, ['in repair', 'maintenance'])) {
                                    $color = 'warning';
                                } else {
                                    $color = 'gray';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox"
                                                :checked="selectedItems.some(a => a.id === {{ $asset->id }})"
                                                @click="toggleItem({ id: {{ $asset->id }} })"
                                                class="row-checkbox sr-only">
                                            <div :class="selectedItems.some(a => a.id === {{ $asset->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                                <span :class="selectedItems.some(a => a.id === {{ $asset->id }}) ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <a href="{{ route('ga-assets.show', $asset) }}" class="font-bold font-mono text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400">
                                        {{ $asset->asset_code }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $asset->name }}</span>
                                        <span class="text-[11px] text-gray-400">{{ $asset->brand ?? '' }} {{ $asset->model ?? '' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-[10px] font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                                        {{ optional($asset->category)->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <x-ui.badge :color="$color" variant="light" size="sm">{{ $asset->status }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap font-mono text-gray-700 dark:text-gray-300">
                                    {{ $asset->purchase_price ? 'Rp ' . number_format($asset->purchase_price, 0, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('ga-assets.show', $asset) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="View Details">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                        <a href="{{ route('ga-assets.edit', $asset) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="Edit Asset">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <i data-lucide="box" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No GA Assets Found</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        No General Affairs inventory assets found matching your criteria.
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
                    </select>
                </div>
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
