@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="GA Asset Categories" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('ga-categories.index') }}" method="GET" class="flex items-center gap-3">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                @if(request('search'))
                    <a href="{{ route('ga-categories.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                @endif
            </form>

            <a href="{{ route('ga-categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Category
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Name</th>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Prefix Code</th>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Depreciation Method</th>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Useful Life</th>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-sm font-semibold text-gray-850 dark:text-white">
                                    {{ $category->name }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="rounded-md bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">
                                        {{ $category->code_prefix }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <x-ui.badge color="primary" variant="light" size="sm">
                                        {{ match ($category->depreciation_method) {
                                            'straight_line' => 'Straight Line',
                                            'double_declining' => 'Double Declining Balance',
                                            'sum_of_years_digits' => 'Sum of Years Digits',
                                            default => $category->depreciation_method
                                        } }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-medium">
                                    {{ $category->useful_life_years }} Years
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route('ga-categories.edit', $category) }}" class="hover:text-brand-500 transition-colors">
                                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        <form action="{{ route('ga-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-error-500 transition-colors">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
