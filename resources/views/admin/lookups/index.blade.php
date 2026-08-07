@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{
        selectedItems: [],
        selectAll: false,
        toggleAll() {
            this.selectAll = !this.selectAll;
            if (this.selectAll) {
                this.selectedItems = Array.from(document.querySelectorAll('.item-checkbox')).map(el => parseInt(el.dataset.id));
            } else {
                this.selectedItems = [];
            }
        },
        toggleItem(id) {
            const index = this.selectedItems.indexOf(id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(id);
            }
            this.selectAll = this.selectedItems.length === document.querySelectorAll('.item-checkbox').length;
        }
    }">
        <x-common.page-breadcrumb
            pageTitle="{{ $title }}s"
            :items="[
                ['label' => 'Ticketing', 'url' => route('ticket-dashboard')],
            ]"
        />

        <!-- Bulk Delete Form -->
        <form id="bulkDeleteForm" action="{{ route($viewName.'.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="id in selectedItems" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedItems.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-error-100 bg-error-50/50 p-4 dark:border-error-900/30 dark:bg-error-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-feather="info" class="w-5 h-5 text-error-500"></i>
                <span class="text-sm font-medium text-error-700 dark:text-error-400">
                    <span x-text="selectedItems.length"></span> items selected.
                </span>
            </div>
            <div class="flex items-center gap-4">
                <button @click="if (confirm('Delete selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <!-- Top Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route($viewName.'.index') }}" method="GET" class="flex items-center gap-3">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or code..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">Search</button>
                @if($search)
                    <a href="{{ route($viewName.'.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">Clear</a>
                @endif
            </form>

            <a href="{{ route($viewName.'.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-feather="plus" class="w-4 h-4"></i>
                Add {{ $title }}
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
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
                            <th class="px-6 py-3 font-medium text-gray-500 text-xs dark:text-gray-400 text-start">Name</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-xs dark:text-gray-400 text-start">Code</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-xs dark:text-gray-400 text-start">Color</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-xs dark:text-gray-400 text-start">Sort</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-xs dark:text-gray-400 text-start">Active</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-xs dark:text-gray-400 text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($items as $item)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex justify-center items-center">
                                        <label class="flex cursor-pointer items-center select-none">
                                            <div class="relative">
                                                <input type="checkbox"
                                                    :checked="selectedItems.includes({{ $item->id }})"
                                                    @click="toggleItem({{ $item->id }})"
                                                    class="item-checkbox sr-only"
                                                    data-id="{{ $item->id }}">
                                                <div :class="selectedItems.includes({{ $item->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                    class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                    <span :class="selectedItems.includes({{ $item->id }}) ? '' : 'opacity-0'">
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
                                        <span class="block font-medium text-gray-700 text-sm dark:text-gray-400">{{ $item->name }}</span>
                                        @if($item->description)
                                            <span class="text-xs text-gray-500 mt-0.5">{{ Str::limit($item->description, 60) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <code class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $item->code }}</code>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    @if($item->color)
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-gray-200 dark:border-gray-700" style="background-color: {{ $item->color }}">
                                                @if($item->icon)
                                                    <i data-feather="{{ $item->icon }}" class="w-3.5 h-3.5 text-white"></i>
                                                @endif
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->color }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $item->sort_order }}</span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <x-ui.badge :color="$item->is_active ? 'success' : 'gray'" variant="light" size="sm">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route($viewName.'.show', $item) }}" class="hover:text-brand-500 transition-colors" title="View Details">
                                            <i data-feather="eye" class="w-5 h-5"></i>
                                        </a>
                                        <a href="{{ route($viewName.'.edit', $item) }}" class="hover:text-brand-500 transition-colors" title="Edit">
                                            <i data-feather="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        <form action="{{ route($viewName.'.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this {{ $title }}?')" class="inline-flex">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="hover:text-error-500 transition-colors" title="Delete">
                                                <i data-feather="trash-2" class="w-5 h-5"></i>
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
                                            <i data-feather="inbox" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No {{ $title }}s found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or add a new entry.</p>
                                        <a href="{{ route($viewName.'.create') }}" class="mt-2 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                                            <i data-feather="plus" class="w-4 h-4"></i>
                                            Add {{ $title }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
                <div class="border-t border-gray-100 px-6 py-3 dark:border-gray-800">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
