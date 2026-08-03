@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Edit Category" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')
                <!-- Section: General Information -->

                <div>

                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">

                        <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>

                        General Information

                    </h3>

                    
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Category Code <span class="text-error-500">*</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code', $category->code) }}" placeholder="e.g., LAP" required
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        @error('code') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Category Name <span class="text-error-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $category->name) }}" placeholder="e.g., Laptop" required
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Master Category <span class="text-error-500">*</span>
                        </label>
                        <div x-data="{ selectedId: '{{ old('master_category_id', $category->master_category_id) }}' }">
                            <input type="hidden" name="master_category_id" :value="selectedId" required />
                            <div class="flex flex-wrap gap-2">
                                @foreach($masterCategories as $master)
                                    <button type="button" @click="selectedId = '{{ $master->id }}'"
                                        :class="selectedId == '{{ $master->id }}' ? 'bg-brand-500 text-white border-brand-500' : 'bg-transparent text-gray-700 dark:text-gray-400 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                        class="inline-flex justify-center rounded-lg border px-4 py-2 text-sm font-medium transition">
                                        {{ $master->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('master_category_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Description
                        </label>
                        <textarea name="description" rows="3" placeholder="Enter description"
                            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('description', $category->description) }}</textarea>
                    </div>
                </div>

                </div>



                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('categories.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
