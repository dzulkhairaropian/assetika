@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
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

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
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
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/50 p-3 dark:border-brand-900/30 dark:bg-brand-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-feather="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> {{ $title }}s selected.
                </span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-600">
                    <i data-feather="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route($viewName.'.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-feather="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search {{ $title }}s..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>
                        @if($search)
                            <a href="{{ route($viewName.'.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 transition">Clear</a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route($viewName.'.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-feather="plus" class="w-3.5 h-3.5"></i>
                            Add {{ $title }}
                        </a>
                    </div>
                </div>
            </div>

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
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">{{ $title }} Name</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Code</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Color</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Sort</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Active</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox"
                                                :checked="selectedItems.includes({{ $item->id }})"
                                                @click="toggleItem({{ $item->id }})"
                                                class="item-checkbox sr-only"
                                                data-id="{{ $item->id }}">
                                            <div :class="selectedItems.includes({{ $item->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                                <span :class="selectedItems.includes({{ $item->id }}) ? '' : 'opacity-0'">
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
                                        <span class="block font-semibold text-gray-900 dark:text-white">{{ $item->name }}</span>
                                        @if($item->description)
                                            <span class="text-[11px] text-gray-400">{{ Str::limit($item->description, 60) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <code class="rounded bg-gray-100 px-2 py-0.5 text-xs font-mono font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $item->code }}</code>
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($item->color)
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-200 dark:border-gray-700" style="background-color: {{ $item->color }}">
                                                @if($item->icon)
                                                    <i data-feather="{{ $item->icon }}" class="w-3 h-3 text-white"></i>
                                                @endif
                                            </span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400 font-mono">{{ $item->color }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">{{ $item->sort_order }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <x-ui.badge :color="$item->is_active ? 'success' : 'secondary'" variant="light" size="sm">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <div class="flex items-center justify-end gap-2 text-gray-500">
                                        <a href="{{ route($viewName.'.show', $item) }}" class="hover:text-brand-600 transition" title="View Details">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route($viewName.'.edit', $item) }}" class="hover:text-brand-600 transition" title="Edit">
                                            <i data-feather="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route($viewName.'.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this {{ $title }}?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-red-600 transition" title="Delete {{ $title }}">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
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
                                            <i data-feather="inbox" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No {{ $title }}s found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Try adjusting your search or add a new {{ $title }}.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
