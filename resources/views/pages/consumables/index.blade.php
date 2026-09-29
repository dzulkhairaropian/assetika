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
        <x-common.page-breadcrumb pageTitle="Consumable Management" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage consumable items (ink, paper, cables) and stock levels.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <!-- Bulk Delete Form -->
        <form id="bulkDeleteForm" action="{{ route('consumables.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="c in selectedConsumables" :key="c.id">
                <input type="hidden" name="ids[]" :value="c.id">
            </template>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedConsumables.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/50 p-3 dark:border-brand-900/30 dark:bg-brand-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedConsumables.length"></span> items selected.
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="const ids = selectedConsumables.map(c => c.id).join(','); window.location.href = `{{ route('consumables.bulk-checkout') }}?ids=${ids}`;"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    Checkout Selected
                </button>
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-600">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden xl:overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
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
                <nav class="flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-track]:bg-white dark:[&::-webkit-scrollbar-track]:bg-transparent">
                    @foreach($tabs as $tab)
                        @php
                            $isActive = ($tab['key'] === null && empty($currentTab)) || ($tab['key'] === $currentTab);
                            $tabUrl = route('consumables.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                        @endphp
                        <a href="{{ $tabUrl }}"
                           class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-colors duration-200 ease-in-out {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-white/5 dark:text-white font-semibold' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                            {{ $tab['label'] }}
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('consumables.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search consumables..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
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

                            <div x-show="filterOpen"
                                x-transition
                                class="absolute left-0 top-full z-50 mt-2 w-80 sm:w-96 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                style="display: none;">
                                <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-2.5 text-left">
                                    <div>
                                        <x-form.searchable-select name="product_id" label="Product" :options="$products->map(fn($prod) => ['id' => $prod->id, 'text' => ($prod->brand->name ?? '') . ' - ' . $prod->name])->toArray()" :selected="request('product_id')" placeholder="All Products" :multiple="true" />
                                    </div>

                                    <div>
                                        <x-form.searchable-select name="category_id" label="Category" :options="$categories->map(fn($cat) => ['id' => $cat->id, 'text' => $cat->name])->toArray()" :selected="request('category_id')" placeholder="All Categories" :multiple="true" />
                                    </div>

                                    <div>
                                        <x-form.searchable-select name="brand_id" label="Brand" :options="$brands->map(fn($br) => ['id' => $br->id, 'text' => $br->name])->toArray()" :selected="request('brand_id')" placeholder="All Brands" :multiple="true" />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Status</label>
                                        <select name="status"
                                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Statuses</option>
                                            <option value="in_stock" {{ (request('status') === 'in_stock' || request('tab') === 'in_stock') ? 'selected' : '' }}>In Stock</option>
                                            <option value="low_stock" {{ (request('status') === 'low_stock' || request('tab') === 'low_stock') ? 'selected' : '' }}>Low Stock</option>
                                            <option value="out_of_stock" {{ (request('status') === 'out_of_stock' || request('tab') === 'out_of_stock') ? 'selected' : '' }}>Out of Stock</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('consumables.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear All Filters
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('consumables.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
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
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">
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
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Product</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category / Brand</th>
                            <th class="px-4 py-2.5 text-start">
                                <x-common.sortable-header column="quantity" label="Qty / Threshold" />
                            </th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-start">
                                <x-common.sortable-header column="purchase_cost" label="Purchase Cost" />
                            </th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($consumables as $consumable)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox"
                                                :checked="selectedConsumables.some(c => c.id === {{ $consumable->id }})"
                                                @click="toggleConsumable({ id: {{ $consumable->id }} })"
                                                class="sr-only consumable-checkbox"
                                                data-consumable="{{ json_encode(['id' => $consumable->id]) }}"
                                                {{ $consumable->quantity <= 0 ? 'disabled' : '' }}>
                                            <div :class="selectedConsumables.some(c => c.id === {{ $consumable->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition {{ $consumable->quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                                <span :class="selectedConsumables.some(c => c.id === {{ $consumable->id }}) ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="block font-medium text-gray-900 dark:text-white">
                                            {{ optional($consumable->product)->name }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">Model: {{ optional($consumable->product)->product_type ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ optional(optional($consumable->product)->category)->name ?? '-' }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">Brand: {{ optional(optional($consumable->product)->brand)->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ $consumable->quantity }} items
                                        </span>
                                        <span class="text-[11px] text-gray-400">Min: {{ $consumable->low_threshold }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $statusName = optional($consumable->status)->name ?? ($consumable->quantity <= 0 ? 'Out of Stock' : ($consumable->quantity <= $consumable->low_threshold ? 'Low Stock' : 'In Stock'));
                                        $statusColor = optional($consumable->status)->color ?? ($consumable->quantity <= 0 ? 'danger' : ($consumable->quantity <= $consumable->low_threshold ? 'warning' : 'success'));
                                        $statusIcon = optional($consumable->status)->icon ?? ($consumable->quantity <= 0 ? 'x-circle' : ($consumable->quantity <= $consumable->low_threshold ? 'alert-circle' : 'circle-check'));
                                    @endphp
                                    <x-ui.badge :color="$statusColor" variant="light" size="sm" :featherIcon="$statusIcon">{{ $statusName }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-2.5 text-gray-900 dark:text-white font-medium">
                                    Rp {{ number_format($consumable->purchase_cost, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <div class="flex items-center justify-end gap-2 text-gray-500">
                                        <a href="{{ route('consumables.restock', $consumable) }}" class="hover:text-green-600 transition" title="Restock">
                                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                        </a>

                                        @if($consumable->quantity > 0)
                                            <a href="{{ route('consumables.checkout', $consumable) }}" class="hover:text-brand-600 transition" title="Checkout">
                                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                            </a>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-700 cursor-not-allowed" title="Out of Stock">
                                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                            </span>
                                        @endif

                                        <a href="{{ route('consumables.edit', $consumable) }}" class="hover:text-brand-600 transition" title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>

                                        <form action="{{ route('consumables.destroy', $consumable) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this consumable?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-red-600 transition" title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="package" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No consumables found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Try adjusting your search or add a new consumable item.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $consumables->links() }}
            </div>
        </div>
    </div>
@endsection
