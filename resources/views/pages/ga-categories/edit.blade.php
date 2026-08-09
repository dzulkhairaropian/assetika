@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Edit GA Asset Category" />

        <form action="{{ route('ga-categories.update', $category) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="folder" class="w-5 h-5 text-brand-500"></i> Category Information
                        </h3>
                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Category Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-850 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Code Prefix <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="code_prefix" value="{{ old('code_prefix', $category->code_prefix) }}" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-855 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('code_prefix') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Depreciation Method <span class="text-error-500">*</span>
                                </label>
                                <select name="depreciation_method" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-850 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="straight_line" {{ old('depreciation_method', $category->depreciation_method) == 'straight_line' ? 'selected' : '' }}>Straight Line</option>
                                    <option value="double_declining" {{ old('depreciation_method', $category->depreciation_method) == 'double_declining' ? 'selected' : '' }}>Double Declining Balance</option>
                                    <option value="sum_of_years_digits" {{ old('depreciation_method', $category->depreciation_method) == 'sum_of_years_digits' ? 'selected' : '' }}>Sum of Years Digits</option>
                                </select>
                                @error('depreciation_method') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Useful Life (Years) <span class="text-error-500">*</span>
                                </label>
                                <input type="number" name="useful_life_years" value="{{ old('useful_life_years', $category->useful_life_years) }}" min="1" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-850 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('useful_life_years') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white/90">
                        <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Update Category
                        </button>
                        <a href="{{ route('ga-categories.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

