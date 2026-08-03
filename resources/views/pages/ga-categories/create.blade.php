@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create GA Asset Category" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 max-w-2xl">
            <form action="{{ route('ga-categories.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Category Name <span class="text-error-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Kendaraan Dinas" required
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-850 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Code Prefix <span class="text-error-500">*</span>
                    </label>
                    <input type="text" name="code_prefix" value="{{ old('code_prefix') }}" placeholder="e.g. VEH" required
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-855 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    @error('code_prefix') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Depreciation Method <span class="text-error-500">*</span>
                    </label>
                    <select name="depreciation_method" required
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-850 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="straight_line" {{ old('depreciation_method') == 'straight_line' ? 'selected' : '' }}>Straight Line</option>
                        <option value="double_declining" {{ old('depreciation_method') == 'double_declining' ? 'selected' : '' }}>Double Declining Balance</option>
                        <option value="sum_of_years_digits" {{ old('depreciation_method') == 'sum_of_years_digits' ? 'selected' : '' }}>Sum of Years Digits</option>
                    </select>
                    @error('depreciation_method') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Useful Life (Years) <span class="text-error-500">*</span>
                    </label>
                    <input type="number" name="useful_life_years" value="{{ old('useful_life_years', 5) }}" min="1" required
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-850 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    @error('useful_life_years') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('ga-categories.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
