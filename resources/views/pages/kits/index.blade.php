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
            Bundle assets and components into deployable equipment kits.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <!-- Bulk Delete Form -->
        <form id="bulkDeleteForm" action="{{ route('kits.bulk-delete') }}" method="POST" class="hidden">
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
                <i data-feather="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> items selected.
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
            {{-- Card Header: Toolbar --}}
            <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('kits.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-feather="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search kits..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>
                        @if(request()->filled('search'))
                            <a href="{{ route('kits.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Clear</a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('kits.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-feather="plus" class="w-3.5 h-3.5"></i>
                            Add Kit
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
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
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Kit Name</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Description</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($kits as $kit)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
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
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('kits.show', $kit) }}" class="block font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                        {{ $kit->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ Str::limit($kit->description, 80) }}
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <div class="flex items-center justify-end gap-2 text-gray-500">
                                        <a href="{{ route('kits.show', $kit) }}" class="hover:text-brand-600 transition" title="View">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('kits.edit', $kit) }}" class="hover:text-brand-600 transition" title="Edit">
                                            <i data-feather="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route('kits.destroy', $kit) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-red-600 transition" title="Delete">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-feather="box" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No kits found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Create a kit to bundle assets and components together.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $kits->links() }}
            </div>
        </div>
    </div>
@endsection
