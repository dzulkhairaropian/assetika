@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Categories" />

        <!-- Stat Card Header -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <i data-feather="folder" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Total Categories</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $categories->total() }}</h4>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10">
                        <i data-feather="grid" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Master Root Filter</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ request('master_category_id') ? 'Filtered Group' : 'All Master Groups' }}</h4>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-500 dark:bg-purple-500/10">
                        <i data-feather="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Sub-categories</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">Catalog Sub-groups</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('categories.index') }}" method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                </div>

                @php $activeCount = request('master_category_id') ? 1 : 0; @endphp

                <div class="relative" @click.away="filterOpen = false">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 transition">
                        <svg class="stroke-current fill-white dark:fill-gray-900" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5" />
                            <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5" />
                        </svg>
                        Filters
                        @if($activeCount > 0)
                            <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                        @endif
                    </button>

                    <div x-show="filterOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-0 top-full z-50 mt-2 w-80 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                        style="display: none;">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Filter Options</h4>
                            <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Master Category</label>
                            <select name="master_category_id"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Master Categories</option>
                                @foreach($masterCategories as $mc)
                                    <option value="{{ $mc->id }}" {{ request('master_category_id') == $mc->id ? 'selected' : '' }}>{{ $mc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ route('categories.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">
                                Clear All Filters
                            </a>
                            <button type="submit" @click="filterOpen = false"
                                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <a href="{{ route('categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-feather="plus" class="w-4 h-4"></i>
                Add Category
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            x-data="{ 
                selectedRows: [], 
                selectAll: false, 
                handleSelectAll() { 
                    this.selectAll = !this.selectAll; 
                    if (this.selectAll) { 
                        this.selectedRows = {{ json_encode($categories->pluck('id')->toArray()) }}; 
                    } else { 
                        this.selectedRows = []; 
                    } 
                }, 
                handleRowSelect(id) { 
                    if (this.selectedRows.includes(id)) { 
                        this.selectedRows = this.selectedRows.filter(rowId => rowId !== id); 
                    } else { 
                        this.selectedRows.push(id); 
                    } 
                    this.selectAll = this.selectedRows.length === {{ $categories->count() }};
                } 
            }">
        <!-- Bulk Action Form -->
        <form id="bulkDeleteForm" action="{{ route('categories.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="id in selectedRows" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedRows.length > 0" 
            x-transition
            class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10 m-4"
            style="display: none;">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-brand-700 dark:text-brand-400">
                    <span x-text="selectedRows.length"></span> items selected.
                </span>
            </div>
            <div class="flex items-center gap-4">
                <button @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }" 
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Delete Selected
                </button>
            </div>
        </div>

            <div class="max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                                <div class="flex items-center gap-3">
                                    <div @click="handleSelectAll()"
                                        class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                        :class="selectAll ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                        <svg :class="selectAll ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <span>Code</span>
                                </div>
                            </th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Name</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Master Category</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @php
                            $avatarColors = [
                                ['bg-blue-100', 'text-blue-500'],
                                ['bg-pink-100', 'text-pink-500'],
                                ['bg-cyan-100', 'text-cyan-500'],
                                ['bg-orange-100', 'text-orange-500'],
                                ['bg-green-100', 'text-green-500'],
                                ['bg-purple-100', 'text-purple-500'],
                                ['bg-yellow-100', 'text-yellow-500'],
                            ];
                        @endphp
                        @forelse($categories as $category)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div @click="handleRowSelect({{ $category->id }})"
                                            class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                            :class="selectedRows.includes({{ $category->id }}) ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                            <svg :class="selectedRows.includes({{ $category->id }}) ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $category->code }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $color = $avatarColors[$loop->index % count($avatarColors)];
                                            $initials = collect(explode(' ', $category->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('');
                                        @endphp
                                        <div class="flex items-center justify-center w-10 h-10 rounded-full font-medium text-sm {{ $color[0] }} {{ $color[1] }}">
                                            <span>{{ $initials }}</span>
                                        </div>
                                        <div>
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $category->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="block text-gray-700 text-theme-sm dark:text-gray-400">
                                        {{ $category->masterCategory->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route('categories.edit', $category) }}" class="hover:text-brand-500 transition-colors">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-error-500 transition-colors">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No categories found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Add a category to organize your master data items.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-800">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
@endsection
