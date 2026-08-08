@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Components" />

        <!-- Stat Card Header -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <i data-feather="cpu" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Total Component Types</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $components->total() }}</h4>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10">
                        <i data-feather="hard-drive" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Total Units in Stock</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $components->sum('qty') }} Qty</h4>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-500 dark:bg-purple-500/10">
                        <i data-feather="link-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Sub-item Attachment</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">Asset Mapped</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('components.index') }}" method="GET" class="flex items-center gap-3">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search components..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                </div>
                @if(request()->filled('search'))
                    <a href="{{ route('components.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                @endif
            </form>

            <a href="{{ route('components.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-feather="plus" class="w-4 h-4"></i>
                Add Component
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            x-data="{
                selectedRows: [],
                selectAll: false,
                handleSelectAll() {
                    this.selectAll = !this.selectAll;
                    if (this.selectAll) {
                        this.selectedRows = {{ json_encode($components->pluck('id')->toArray()) }};
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
                    this.selectAll = this.selectedRows.length === {{ $components->count() }};
                }
            }">
            <form id="bulkDeleteForm" action="{{ route('components.bulk-delete') }}" method="POST" class="hidden">
                @csrf
                <template x-for="id in selectedRows" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>

            <div x-show="selectedRows.length > 0" x-transition
                class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10 m-4" style="display: none;">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-medium text-brand-700 dark:text-brand-400"><span x-text="selectedRows.length"></span> items selected.</span>
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
                                    <div @click="handleSelectAll()" class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]" :class="selectAll ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                        <svg :class="selectAll ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <span>Name</span>
                                </div>
                            </th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Category</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Model / Serial</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Qty / Available</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Location</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($components as $component)
                            @php $available = $component->availableQty(); @endphp
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div @click="handleRowSelect({{ $component->id }})" class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]" :class="selectedRows.includes({{ $component->id }}) ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                            <svg :class="selectedRows.includes({{ $component->id }}) ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <a href="{{ route('components.show', $component) }}" class="block font-medium text-brand-500 hover:text-brand-600 text-theme-sm">
                                            {{ $component->name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">
                                    {{ $component->category?->name ?? '-' }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="block text-theme-sm text-gray-700 dark:text-gray-400">{{ $component->model_number ?? '-' }}</span>
                                    <span class="text-xs text-gray-400">{{ $component->serial ?? '' }}</span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ $component->qty }}</span>
                                        <span class="text-xs text-gray-400">/</span>
                                        <span class="text-theme-sm {{ $available <= $component->min_qty ? 'text-error-500 font-semibold' : 'text-gray-500' }}">
                                            {{ $available }} avail
                                        </span>
                                    </div>
                                    @if($available <= $component->min_qty && $component->qty > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                            Low stock
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-500 dark:text-gray-400">
                                    {{ $component->location?->name ?? '-' }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route('components.show', $component) }}" class="hover:text-brand-500 transition-colors">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </a>
                                        <a href="{{ route('components.edit', $component) }}" class="hover:text-brand-500 transition-colors">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                        <form action="{{ route('components.destroy', $component) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="hover:text-error-500 transition-colors">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No components found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Add hardware components like RAM, SSD, GPU to track stock and assignments.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-800">
                {{ $components->links() }}
            </div>
        </div>
    </div>
@endsection
