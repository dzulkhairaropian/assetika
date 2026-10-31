@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
        selectedConsumables: [],
        selectAll: false,
        toggleAll() {
            this.selectAll = !this.selectAll;
            if (this.selectAll) {
                this.selectedConsumables = Array.from(document.querySelectorAll('.consumable-checkbox:not(:disabled)')).map(el => JSON.parse(el.dataset.consumable));
            } else {
                this.selectedConsumables = [];
            }
        },
        toggleConsumable(consumable) {
            const index = this.selectedConsumables.findIndex(c => c.id === consumable.id);
            if (index > -1) {
                this.selectedConsumables.splice(index, 1);
            } else {
                this.selectedConsumables.push(consumable);
            }
            this.selectAll = this.selectedConsumables.length === document.querySelectorAll('.consumable-checkbox:not(:disabled)').length;
        }
    }">
        <x-common.page-breadcrumb pageTitle="Consumables" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage consumable supplies (toner, paper, cables, accessories) and stock alert levels.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Bulk Delete Form --}}
        <form id="bulkDeleteForm" action="{{ route('consumables.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="c in selectedConsumables" :key="c.id">
                <input type="hidden" name="ids[]" :value="c.id">
            </template>
        </form>

        {{-- Bulk Action Bar --}}
        <div x-show="selectedConsumables.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 p-3 shadow-xs dark:border-brand-900/50 dark:bg-brand-950/40"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedConsumables.length"></span> items selected.
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="const ids = selectedConsumables.map(c => c.id).join(','); window.location.href = `{{ route('consumables.bulk-checkout') }}?ids=${ids}`;"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-brand-600 transition">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    Checkout Selected
                </button>
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        {{-- Main Table Container (Exact Asset Index Layout) --}}
        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Tabs + Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                @php
                    $currentTab = request('tab') ?: request('status');
                    $tabFilters = array_filter(request()->except(['page', 'tab', 'status']));
                    $tabs = [
                        ['key' => null,           'label' => 'All Consumables', 'count' => $consumableSummary['total'],        'icon' => 'layers',       'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'in_stock',     'label' => 'In Stock',        'count' => $consumableSummary['in_stock'],     'icon' => 'circle-check', 'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                        ['key' => 'low_stock',    'label' => 'Low Stock',       'count' => $consumableSummary['low_stock'],    'icon' => 'alert-circle', 'badge' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500'],
                        ['key' => 'out_of_stock', 'label' => 'Out of Stock',   'count' => $consumableSummary['out_of_stock'], 'icon' => 'x-circle',     'badge' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'],
                    ];
                @endphp
                <nav class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-track]:bg-transparent">
                    @foreach($tabs as $tab)
                        @php
                            $isActive = ($tab['key'] === null && empty($currentTab)) || ($tab['key'] === $currentTab);
                            $tabUrl = route('consumables.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
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
                    <form action="{{ route('consumables.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search consumables, products..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('product_id')) $activeCount++;
                            if (request('category_id')) $activeCount++;
                            if (request('brand_id')) $activeCount++;
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
                                        <x-form.searchable-select name="product_id" label="Product Model" :options="$products->map(fn($p) => ['id' => $p->id, 'text' => $p->name])->toArray()" :selected="request('product_id')" placeholder="All Models" />
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('consumables.index', request('tab') ? ['tab' => request('tab')] : []) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
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
                            <a href="{{ route('consumables.index', request('tab') ? ['tab' => request('tab')] : []) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('consumables.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Consumable
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
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Item Model & Name</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Category & Brand</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Stock Availability</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Unit Cost & Value</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($consumables as $consumable)
                            @php
                                $isOut = $consumable->isOutOfStock();
                                $isLow = $consumable->isLowStock();
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox"
                                                :checked="selectedConsumables.some(c => c.id === {{ $consumable->id }})"
                                                @click="toggleConsumable({ id: {{ $consumable->id }} })"
                                                class="consumable-checkbox sr-only">
                                            <div :class="selectedConsumables.some(c => c.id === {{ $consumable->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                                <span :class="selectedConsumables.some(c => c.id === {{ $consumable->id }}) ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <a href="{{ route('consumables.show', $consumable) }}" class="font-bold text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">
                                            {{ $consumable->product?->name ?? 'Unnamed Consumable' }}
                                        </a>
                                        @if($consumable->notes)
                                            <span class="text-[11px] text-gray-400 truncate max-w-xs">{{ $consumable->notes }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ optional($consumable->product?->brand)->name ?? 'General Brand' }}</span>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">{{ optional($consumable->product?->category)->name ?? 'Supply' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold tabular-nums {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-gray-900 dark:text-white') }}">
                                            {{ number_format($consumable->quantity) }} units
                                        </span>
                                        @if($isOut)
                                            <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">Out</span>
                                        @elseif($isLow)
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Low ({{ $consumable->low_threshold }})</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">In Stock</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap font-mono text-gray-700 dark:text-gray-300">
                                    {{ $consumable->purchase_cost ? 'Rp ' . number_format($consumable->purchase_cost, 0, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('consumables.restock', $consumable) }}"
                                           class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-400 transition"
                                           title="Restock Inventory">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            Restock
                                        </a>
                                        @if($consumable->quantity > 0)
                                            <a href="{{ route('consumables.checkout', $consumable) }}"
                                               class="inline-flex items-center gap-1 rounded-lg bg-brand-50 border border-brand-200 px-2.5 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:border-brand-900/40 dark:bg-brand-950/30 dark:text-brand-400 transition"
                                               title="Checkout to User">
                                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                                Checkout
                                            </a>
                                        @endif
                                        <a href="{{ route('consumables.show', $consumable) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="View Details">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                        <a href="{{ route('consumables.edit', $consumable) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="Edit Consumable">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <i data-lucide="package" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No Consumables Found</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        No consumable items or office supplies found matching current filter.
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
                    {{ $consumables->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
