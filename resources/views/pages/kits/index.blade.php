@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
        selectedItems: [],
        selectAll: false,
        toggleAll() {
            this.selectAll = !this.selectAll;
            if (this.selectAll) {
                this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(el => Number(el.value));
            } else {
                this.selectedItems = [];
            }
        },
        toggleItem(id) {
            if (this.selectedItems.includes(id)) {
                this.selectedItems = this.selectedItems.filter(i => i !== id);
            } else {
                this.selectedItems.push(id);
            }
            this.selectAll = this.selectedItems.length === document.querySelectorAll('.row-checkbox').length;
        }
    }">
        <x-common.page-breadcrumb pageTitle="Pre-defined Kits" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Bundle hardware assets, software licenses, and components into ready-to-deploy equipment kits.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Bulk Delete Form --}}
        <form id="bulkDeleteForm" action="{{ route('kits.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="id in selectedItems" :key="id">
                <input type="hidden" name="ids[]" :value="id">
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
                    <span x-text="selectedItems.length"></span> kits selected.
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected kits?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition">
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
                    $currentTab = request('tab');
                    $tabFilters = array_filter(request()->except(['page', 'tab']));
                    $tabs = [
                        ['key' => null,     'label' => 'All Kits',    'count' => $summary['total'] ?? 0, 'icon' => 'layers',   'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'today',  'label' => 'Added Today', 'count' => $summary['today'] ?? 0, 'icon' => 'calendar', 'badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'],
                    ];
                @endphp
                <nav class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-track]:bg-transparent">
                    @foreach($tabs as $tab)
                        @php
                            $isActive = $currentTab === $tab['key'];
                            $tabUrl = route('kits.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
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
                    <form action="{{ route('kits.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search kits..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @if(request()->filled('search'))
                            <a href="{{ route('kits.index', request('tab') ? ['tab' => request('tab')] : []) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('kits.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Kit
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
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Kit Name</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Description</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Bundled Content</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($kits as $kit)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" value="{{ $kit->id }}" class="row-checkbox sr-only"
                                                :checked="selectedItems.includes({{ $kit->id }})" @click="toggleItem({{ $kit->id }})">
                                            <div :class="selectedItems.includes({{ $kit->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                                <span :class="selectedItems.includes({{ $kit->id }}) ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <a href="{{ route('kits.show', $kit) }}" class="font-bold text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">
                                        {{ $kit->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 max-w-sm text-gray-600 dark:text-gray-300 truncate">
                                    {{ $kit->description ?: '—' }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($kit->assets_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                                <i data-lucide="monitor" class="w-3 h-3"></i> {{ $kit->assets_count }} Assets
                                            </span>
                                        @endif
                                        @if($kit->licenses_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                <i data-lucide="key" class="w-3 h-3"></i> {{ $kit->licenses_count }} Licenses
                                            </span>
                                        @endif
                                        @if($kit->components_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-700 dark:bg-purple-500/10 dark:text-purple-400">
                                                <i data-lucide="cpu" class="w-3 h-3"></i> {{ $kit->components_count }} Components
                                            </span>
                                        @endif
                                        @if($kit->consumables_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                                <i data-lucide="package" class="w-3 h-3"></i> {{ $kit->consumables_count }} Supplies
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('kits.checkout-form', $kit) }}"
                                           class="inline-flex items-center gap-1 rounded-lg bg-brand-50 border border-brand-200 px-2.5 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:border-brand-900/40 dark:bg-brand-950/30 dark:text-brand-400 transition"
                                           title="Checkout Kit Bundle">
                                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                            Checkout
                                        </a>
                                        <a href="{{ route('kits.show', $kit) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="View Details">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                        <a href="{{ route('kits.edit', $kit) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="Edit Kit">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <i data-lucide="box" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No Equipment Kits Found</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        No pre-defined kits configured yet. Bundle hardware and software items to streamline employee setup.
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
                    {{ $kits->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
