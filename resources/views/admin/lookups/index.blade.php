@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
        selectedItems: [],
        selectAll: false,
        bulkEditModalOpen: false,
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
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> {{ $title }}s selected.
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="bulkEditModalOpen = true"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-600">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    Bulk Edit (Change Selected)
                </button>
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-600">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <!-- Quick Filter Tabs -->
        @if(isset($counts))
            <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5">
                @php
                    $currentActive = request('is_active');
                @endphp
                <a href="{{ route($viewName.'.index', request()->except(['is_active', 'page'])) }}"
                    class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $currentActive === null || $currentActive === '' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    All ({{ $counts['total'] }})
                </a>
                <a href="{{ route($viewName.'.index', array_merge(request()->except('page'), ['is_active' => '1'])) }}"
                    class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $currentActive === '1' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
                    <i data-lucide="circle-check" class="w-3.5 h-3.5 text-green-500"></i>
                    Active ({{ $counts['active'] }})
                </a>
                <a href="{{ route($viewName.'.index', array_merge(request()->except('page'), ['is_active' => '0'])) }}"
                    class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $currentActive === '0' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
                    <i data-lucide="circle-x" class="w-3.5 h-3.5 text-gray-400"></i>
                    Inactive ({{ $counts['inactive'] }})
                </a>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route($viewName.'.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
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
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
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
                                    <div class="flex flex-col gap-0.5">
                                        <x-ui.badge :color="$item->color ?? 'gray'" variant="light" size="sm" :featherIcon="$item->icon">
                                            {{ $item->name }}
                                        </x-ui.badge>
                                        @if($item->description)
                                            <span class="text-[11px] text-gray-400 mt-0.5">{{ Str::limit($item->description, 60) }}</span>
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
                                                    <i data-lucide="{{ $item->icon }}" class="w-3 h-3 text-white"></i>
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
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route($viewName.'.edit', $item) }}" class="hover:text-brand-600 transition" title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route($viewName.'.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this {{ $title }}?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-red-600 transition" title="Delete {{ $title }}">
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
                                            <i data-lucide="inbox" class="w-5 h-5"></i>
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

        <!-- Bulk Edit Modal -->
        <div x-show="bulkEditModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
            <div @click.away="bulkEditModalOpen = false" class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                            <i data-lucide="edit-3" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Bulk Edit {{ $title }}s</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Update <strong x-text="selectedItems.length"></strong> selected items</p>
                        </div>
                    </div>
                    <button type="button" @click="bulkEditModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <form action="{{ route($viewName.'.bulk-edit') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-for="id in selectedItems" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Active Status (Optional)
                        </label>
                        <select name="is_active"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">-- Keep Current Status --</option>
                            <option value="1">Set Active</option>
                            <option value="0">Set Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Change Color / Hex (Optional)
                        </label>
                        <input type="text" name="color" placeholder="e.g. #10b981 or primary"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button type="button" @click="bulkEditModalOpen = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600 transition">
                            Apply Bulk Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
