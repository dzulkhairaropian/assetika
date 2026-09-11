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
            Manage General Affairs assets (furniture, vehicles, appliances).
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        @if(($taxDueVehiclesCount ?? 0) > 0 || ($warrantyExpiringCount ?? 0) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @if(($taxDueVehiclesCount ?? 0) > 0)
                    <div class="rounded-xl border border-warning-200 bg-warning-50/80 p-3.5 dark:border-warning-900/30 dark:bg-warning-500/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-warning-500 text-white">
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                </div>
                                <h4 class="text-xs font-bold text-warning-800 dark:text-warning-300">Pajak Kendaraan Jatuh Tempo ({{ $taxDueVehiclesCount }})</h4>
                            </div>
                            <a href="{{ route('ga-assets.index', ['filter' => 'tax_due']) }}" class="text-[11px] font-semibold text-warning-800 underline hover:text-warning-900">Filter List</a>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($taxDueVehicles->take(5) as $v)
                                @if($v->asset)
                                    <a href="{{ route('ga-assets.show', $v->asset) }}" class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-[11px] font-semibold text-warning-900 shadow-xs border border-warning-200 hover:bg-warning-100 dark:bg-gray-900 dark:text-warning-300 dark:border-warning-800">
                                        <span>{{ $v->license_plate ?: $v->asset->asset_code }}</span>
                                        <span class="text-[10px] text-gray-500">({{ $v->tax_due_date ? $v->tax_due_date->format('d M Y') : '-' }})</span>
                                    </a>
                                @endif
                            @endforeach
                            @if($taxDueVehiclesCount > 5)
                                <span class="text-[10px] text-warning-700 self-center">+{{ $taxDueVehiclesCount - 5 }} lainnya</span>
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
                                    <span class="text-[10px] text-gray-500">({{ $item->warranty_expiry_date ? $item->warranty_expiry_date->format('d M Y') : '-' }})</span>
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

        <!-- Bulk Delete Form -->
        <form id="bulkDeleteForm" action="{{ route('ga-assets.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="asset in selectedItems" :key="asset.id">
                <input type="hidden" name="ids[]" :value="asset.id">
            </template>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedItems.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/50 p-3 dark:border-brand-900/30 dark:bg-brand-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> items selected.
                </span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-error-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-error-600">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden xl:overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Toolbar --}}
            <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('ga-assets.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search assets..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
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
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Category</label>
                                        <select name="category_id"
                                            class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
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
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('ga-assets.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add GA Asset
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto xl:overflow-visible">
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
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset Name / Tag</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Holder</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" :checked="selectedItems.some(a => a.id === {{ $asset->id }})" @click="toggleItem({ id: {{ $asset->id }} })" class="row-checkbox sr-only" data-asset='{"id":{{ $asset->id }}}'>
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
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <a href="{{ route('ga-assets.show', $asset) }}" class="font-medium text-brand-500 hover:underline">{{ $asset->name }}</a>
                                        <span class="text-[11px] text-gray-500 font-mono">{{ $asset->asset_code ?? $asset->asset_tag ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ $asset->category?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $statusName = $asset->status ?? 'Available';
                                        $statusObj = $asset->statusObject;
                                        $color = $statusObj->color ?? match($statusName) {
                                            'Available' => 'success',
                                            'Deployed', 'In Use' => 'brand',
                                            'In Repair', 'Maintenance' => 'warning',
                                            'Disposed', 'Broken' => 'danger',
                                            default => 'gray'
                                        };
                                        $icon = $statusObj->icon ?? match($statusName) {
                                            'Available' => 'circle-check',
                                            'Deployed', 'In Use' => 'monitor',
                                            'In Repair', 'Maintenance' => 'tool',
                                            'Disposed', 'Broken' => 'alert-triangle',
                                            default => 'info'
                                        };
                                    @endphp
                                    <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="$icon">{{ $statusName }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ $asset->assignedToUser?->name ?? 'Not Assigned' }}
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <div x-data="{
                                        open: false,
                                        dropdownStyle: '',
                                        toggle(e) {
                                            this.open = !this.open;
                                            if (this.open) {
                                                const rect = e.currentTarget.getBoundingClientRect();
                                                const menuHeight = 140;
                                                const spaceBelow = window.innerHeight - rect.bottom;
                                                const top = spaceBelow < menuHeight ? Math.max(10, rect.top - menuHeight) : (rect.bottom + 4);
                                                const left = Math.max(10, rect.right - 144);
                                                this.dropdownStyle = `position: fixed; top: ${top}px; left: ${left}px; z-index: 9999;`;
                                            }
                                        }
                                    }" @click.away="open = false" @scroll.window="open = false" class="inline-block text-left">
                                        <button type="button" @click="toggle($event)" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>

                                        <div x-show="open"
                                            x-transition
                                            :style="dropdownStyle"
                                            class="w-36 rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                            style="display: none;">
                                            
                                            <a href="{{ route('ga-assets.show', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                                                View Details
                                            </a>

                                            <a href="{{ route('ga-assets.edit', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-gray-400"></i>
                                                Edit
                                            </a>

                                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                            <form action="{{ route('ga-assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="briefcase" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No GA assets found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Add General Affairs assets like furniture, vehicles, or appliances.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $assets->links() }}
            </div>
        </div>
    </div>
@endsection