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
        <x-common.page-breadcrumb pageTitle="Ticket Categories" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage ticket categories used across ITSM support ticketing system.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <!-- Bulk Delete Form -->
        <form id="bulkDeleteForm" action="{{ route('admin.ticket-categories.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="id in selectedItems" :key="id">
                <input type="hidden" name="ids[]" :value="id">
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
                    <span x-text="selectedItems.length"></span> categories selected.
                </span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected categories?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-600">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/3">
            {{-- Card Header: Tabs + Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                @php
                    $currentTab = request('tab') ?: request('status');
                    $tabFilters = array_filter(request()->except(['page', 'tab', 'status']));
                    $tabs = [
                        ['key' => null,       'label' => 'All Categories', 'count' => $categorySummary['total'] ?? 0,    'icon' => 'layers',       'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'active',   'label' => 'Active',         'count' => $categorySummary['active'] ?? 0,   'icon' => 'circle-check', 'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                        ['key' => 'inactive', 'label' => 'Inactive',       'count' => $categorySummary['inactive'] ?? 0, 'icon' => 'x-circle',     'badge' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'],
                    ];
                @endphp
                <nav class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-track]:bg-white dark:[&::-webkit-scrollbar-track]:bg-transparent">
                    @foreach($tabs as $tab)
                        @php
                            $isActive = ($tab['key'] === null && empty($currentTab)) || ($tab['key'] === $currentTab) || ($tab['key'] === 'active' && $currentTab === '1') || ($tab['key'] === 'inactive' && $currentTab === '0');
                            $tabUrl = route('admin.ticket-categories.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
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
                    <form action="{{ route('admin.ticket-categories.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ticket categories..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>

                        @php $activeCount = request()->has('status') && request('status') !== '' ? 1 : 0; @endphp

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
                                class="absolute left-0 top-full z-50 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                style="display: none;">
                                <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Status</label>
                                    <select name="status"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                        <option value="">All Statuses</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('admin.ticket-categories.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear Filters
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.ticket-categories.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Ticket Category
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/90 backdrop-blur dark:border-gray-800 dark:bg-gray-800/90">
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
                            <th class="px-4 py-2.5 text-start">
                                <x-common.sortable-header column="code" label="Code" />
                            </th>
                            <th class="px-4 py-2.5 text-start">
                                <x-common.sortable-header column="name" label="Category Name" />
                            </th>
                            <th class="px-4 py-2.5 text-start">
                                <x-common.sortable-header column="description" label="Description" />
                            </th>
                            <th class="px-4 py-2.5 text-start">
                                <x-common.sortable-header column="is_active" label="Status" />
                            </th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" value="{{ $cat->id }}" :checked="selectedItems.includes({{ $cat->id }})" @click="toggleItem({{ $cat->id }})" class="sr-only row-checkbox">
                                            <div :class="selectedItems.includes({{ $cat->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                                <span :class="selectedItems.includes({{ $cat->id }}) ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-4 py-2.5 font-mono font-medium text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $cat->code }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                    {{ $cat->name }}
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                    {{ $cat->description ?: '-' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($cat->is_active)
                                        <x-ui.badge color="success" variant="light" size="sm">Active</x-ui.badge>
                                    @else
                                        <x-ui.badge color="error" variant="light" size="sm">Inactive</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <div x-data="{
                                        open: false,
                                        dropdownStyle: '',
                                        toggle(e) {
                                            this.open = !this.open;
                                            if (this.open) {
                                                const rect = e.currentTarget.getBoundingClientRect();
                                                const menuHeight = 100;
                                                const spaceBelow = window.innerHeight - rect.bottom;
                                                const top = spaceBelow < menuHeight ? Math.max(10, rect.top - menuHeight) : (rect.bottom + 4);
                                                const left = Math.max(10, rect.right - 160);
                                                this.dropdownStyle = `position: fixed; top: ${top}px; left: ${left}px; z-index: 9999; display: block;`;
                                            }
                                        }
                                    }" @click.away="open = false" @scroll.window="open = false" class="inline-block text-left">
                                        <button type="button" @click="toggle($event)" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>

                                        <div x-show="open"
                                            x-transition
                                            :style="open ? dropdownStyle : 'display: none;'"
                                            class="w-40 rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                            style="display: none;">
                                            
                                            <a href="{{ route('admin.ticket-categories.edit', $cat) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-gray-400"></i>
                                                Edit Category
                                            </a>

                                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                            <form action="{{ route('admin.ticket-categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    Delete Category
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
                                            <i data-lucide="folder" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No ticket categories found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Try adjusting your search filter or add a new category.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
@endsection
