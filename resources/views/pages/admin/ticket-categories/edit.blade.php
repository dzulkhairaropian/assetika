@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Edit Ticket Category" :items="[['label' => 'Ticket Categories', 'url' => route('admin.ticket-categories.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Update ticket category information.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('admin.ticket-categories.update', $category) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>
                            General Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Category Code <span class="text-xs text-gray-400 font-normal">(Auto-generated if empty)</span>
                                </label>
                                <input type="text" name="code" value="{{ old('code', $category->code) }}" placeholder="e.g., HW, SW, NET"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                                @error('code') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Category Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $category->name) }}" placeholder="e.g., Hardware, Software, Network" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                                @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Description
                                </label>
                                <textarea name="description" rows="3" placeholder="Enter optional description..."
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('description', $category->description) }}</textarea>
                                @error('description') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Active</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-lucide="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                            Update Ticket Category
                        </button>
                        <a href="{{ route('admin.ticket-categories.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
