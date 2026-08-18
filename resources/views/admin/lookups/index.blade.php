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
        <x-common.page-breadcrumb pageTitle="{{ $title }}s" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Manage ticket-related lookups: Priorities, Statuses, Types, SLA Statuses, Sources, and Labels.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form id="bulkDeleteForm" action="{{ route($viewName.'.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="id in selectedItems" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        <div x-show="selectedItems.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                <span class="text-sm font-medium text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> {{ $title }}s selected.
                </span>
            </div>
            <div class="flex items-center gap-4">
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="space-y-4 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route($viewName.'.index') }}" method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-gray-400">
                                <i data-feather="search" class="w-4 h-4"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search {{ $title }}s..."
                                class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                        </div>
                        @if($search)
                            <a href="{{ route($viewName.'.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">Clear</a>
                        @endif
                    </form>

                    <div class="flex items-center gap-3">
                        <a href="{{ route($viewName.'.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            <i data-feather="plus" class="w-4 h-4"></i>
                            Add {{ $title }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead class="border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
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
                                    </p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">{{ $title }} Name</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Code</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Color</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Sort</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Active</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center justify-end">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($items as $item)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center">
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
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col">
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $item->name }}</span>
                                        @if($item->description)
                                            <span class="text-xs text-gray-500">{{ Str::limit($item->description, 60) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <code class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $item->code }}</code>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
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
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->sort_order }}</span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <x-ui.badge :color="$item->is_active ? 'success' : 'gray'" variant="light" size="sm">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 sm:px-6 text-right">
                                    <div x-data="{ open: false }" class="relative inline-block text-left" @click.away="open = false">
                                        <button type="button" @click="open = !open" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                            <i data-feather="more-vertical" class="w-5 h-5"></i>
                                        </button>

                                        <div x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 top-full z-50 mt-1 w-44 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
                                            style="display: none;">
                                            
                                            <a href="{{ route($viewName.'.show', $item) }}" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-feather="eye" class="w-4 h-4 text-gray-400"></i>
                                                View Details
                                            </a>

                                            <a href="{{ route($viewName.'.edit', $item) }}" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-feather="edit-3" class="w-4 h-4 text-gray-400"></i>
                                                Edit
                                            </a>

                                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                            <form action="{{ route($viewName.'.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this {{ $title }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 transition-colors">
                                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                                    Delete {{ $title }}
                                                </button>
                                            </form>
                                        </div>
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
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or add a new {{ $title }}.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
