@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Edit Brand" :items="[['label' => 'Brands', 'url' => route('brands.index')], ['label' => $brand->name]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Update manufacturer or vendor brand details.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('brands.update', $brand) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_340px]">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5 space-y-5">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white mb-4 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                            General Information
                        </h3>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Brand Code <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="code" value="{{ old('code', $brand->code) }}" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('code') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Brand Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $brand->name) }}" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Website
                                </label>
                                <input type="url" name="website" value="{{ old('website', $brand->website) }}" placeholder="https://..."
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('website') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Description
                                </label>
                                <textarea name="description" rows="3" placeholder="Enter brand description"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">{{ old('description', $brand->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white border-b border-gray-100 pb-2 dark:border-gray-800">
                        Actions
                    </h3>
                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            Update Brand
                        </button>
                        <a href="{{ route('brands.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
