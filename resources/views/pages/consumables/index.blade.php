@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{
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

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('consumables.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search consumables..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                </div>

                <select name="product_id" onchange="this.form.submit()"
                    class="h-10 rounded-lg border border-gray-300 bg-white pl-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                    <option value="">All Products</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>{{ $prod->brand->name ?? '' }} - {{ $prod->model }}</option>
                    @endforeach
                </select>

                <select name="category_id" onchange="this.form.submit()"
                    class="h-10 rounded-lg border border-gray-300 bg-white pl-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select name="brand_id" onchange="this.form.submit()"
                    class="h-10 rounded-lg border border-gray-300 bg-white pl-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                    <option value="">All Brands</option>
                    @foreach($brands as $br)
                        <option value="{{ $br->id }}" {{ request('brand_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="h-10 rounded-lg border border-gray-300 bg-white pl-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                    <option value="">All Statuses</option>
                    <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>

                @if(request()->anyFilled(['search', 'product_id', 'category_id', 'brand_id', 'status']))
                    <a href="{{ route('consumables.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                @endif
            </form>

            <a href="{{ route('consumables.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Consumable
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <!-- Bulk Action Bar -->
        <div x-show="selectedConsumables.length > 0" 
            x-transition
            class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10 mb-4"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>
                <span class="text-sm font-medium text-brand-700 dark:text-brand-400">
                    <span x-text="selectedConsumables.length"></span> items selected.
                </span>
            </div>
            <div class="flex items-center gap-4">
                <button @click="const ids = selectedConsumables.map(c => c.id).join(','); window.location.href = `{{ route('consumables.bulk-checkout') }}?ids=${ids}`;" 
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-brand-600">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Checkout Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">
                                <div class="flex justify-center items-center">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                            <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                <span :class="selectAll ? '' : 'opacity-0'">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Product</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Category / Brand</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Qty / Threshold</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Status</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Purchase Cost</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($consumables as $consumable)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex justify-center items-center">
                                        <label class="flex cursor-pointer items-center select-none">
                                            <div class="relative">
                                                <input type="checkbox" 
                                                    :checked="selectedConsumables.some(c => c.id === {{ $consumable->id }})" 
                                                    @click="toggleConsumable({
                                                        id: {{ $consumable->id }}
                                                    })"
                                                    class="sr-only consumable-checkbox"
                                                    data-consumable="{{ json_encode(['id' => $consumable->id]) }}"
                                                    {{ $consumable->quantity <= 0 ? 'disabled' : '' }}>
                                                <div :class="selectedConsumables.some(c => c.id === {{ $consumable->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                    class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition {{ $consumable->quantity <= 0 ? 'opacity-50 cursor-not-allowed bg-gray-105 dark:bg-gray-800' : '' }}">
                                                    <span :class="selectedConsumables.some(c => c.id === {{ $consumable->id }}) ? '' : 'opacity-0'">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ optional($consumable->product)->name }}
                                        </span>
                                        <span class="text-xs text-gray-500">Model: {{ optional($consumable->product)->product_type ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ optional(optional($consumable->product)->category)->name ?? '-' }}
                                        </span>
                                        <span class="text-xs text-gray-500">Brand: {{ optional(optional($consumable->product)->brand)->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ $consumable->quantity }} items
                                        </span>
                                        <span class="text-xs text-gray-500">Min: {{ $consumable->low_threshold }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    @php
                                        $color = 'success';
                                        $statusText = 'In Stock';
                                         $icon = 'circle-check';
                                        if ($consumable->quantity <= 0) {
                                            $color = 'error';
                                            $statusText = 'Out of Stock';
                                            $icon = 'x-circle';
                                        } elseif ($consumable->quantity <= $consumable->low_threshold) {
                                            $color = 'warning';
                                            $statusText = 'Low Stock';
                                            $icon = 'alert-circle';
                                        }
                                        $iconHtml = new \Illuminate\Support\HtmlString("<i data-lucide='{$icon}' class='w-3 h-3'></i>");
                                    @endphp
                                    <x-ui.badge :color="$color" variant="light" size="sm" :startIcon="$iconHtml">{{ $statusText }}</x-ui.badge>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">
                                    Rp {{ number_format($consumable->purchase_cost, 2, ',', '.') }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route('consumables.restock', $consumable) }}" class="hover:text-success-500 transition-colors" title="Restock">
                                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                                        </a>

                                        @if($consumable->quantity > 0)
                                            <a href="{{ route('consumables.checkout', $consumable) }}" class="hover:text-brand-500 transition-colors" title="Checkout">
                                                <i data-lucide="log-out" class="w-5 h-5"></i>
                                            </a>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-700 cursor-not-allowed" title="Out of Stock">
                                                <i data-lucide="log-out" class="w-5 h-5"></i>
                                            </span>
                                        @endif

                                        <a href="{{ route('consumables.edit', $consumable) }}" class="hover:text-brand-500 transition-colors" title="Edit">
                                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                                        </a>

                                        <form action="{{ route('consumables.destroy', $consumable) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this consumable?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-error-500 transition-colors" title="Delete">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No consumables found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or add a new consumable item.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-800">
                {{ $consumables->links() }}
            </div>
        </div>
    </div>
@endsection
