@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="GA Asset Categories" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage General Affairs asset categories.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Toolbar --}}
            <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('ga-categories.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>
                        @if(request()->filled('search'))
                            <a href="{{ route('ga-categories.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Clear</a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('ga-categories.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Category
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Prefix Code</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Depreciation Method</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Useful Life</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                    {{ $category->name }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $category->code_prefix }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <x-ui.badge color="primary" variant="light" size="sm">
                                        {{ match ($category->depreciation_method) {
                                            'straight_line' => 'Straight Line',
                                            'double_declining' => 'Double Declining Balance',
                                            'sum_of_years_digits' => 'Sum of Years Digits',
                                            default => $category->depreciation_method
                                        } }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-2.5 text-gray-650 dark:text-gray-400">
                                    {{ $category->useful_life_years }} Years
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <div x-data="{
    open: false,
    dropdownStyle: "",
    toggle(e) {
        this.open = !this.open;
        if (this.open) {
            const rect = e.currentTarget.getBoundingClientRect();
            const menuHeight = 120;
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
                                            
                                            <a href="{{ route('ga-categories.edit', $category) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-gray-400"></i>
                                                Edit
                                            </a>

                                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                            <form action="{{ route('ga-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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
                                <td colspan="5" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="bookmark" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No GA categories found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Add a category to classify General Affairs assets.</p>
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