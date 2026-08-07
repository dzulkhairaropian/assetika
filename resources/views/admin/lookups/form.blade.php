@php $isEdit = isset($item) && $item !== null; @endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6 min-w-0">
        <x-common.page-breadcrumb
            pageTitle="{{ $isEdit ? 'Edit ' . $title : 'Create ' . $title }}"
            :items="[
                ['label' => 'Ticketing', 'url' => route('ticket-dashboard')],
                ['label' => $title.'s', 'url' => route($viewName.'.index')],
            ]"
        />

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form method="POST" action="{{ $isEdit ? route($viewName.'.update', $item) : route($viewName.'.store') }}" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-8">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Basic Information</h3>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $item?->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('name') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Code *</label>
                                <input type="text" name="code" id="code" value="{{ old('code', $item?->code) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('code') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @if(isset($rules['label']))
                        <div>
                            <label for="label" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Label</label>
                            <input type="text" name="label" id="label" value="{{ old('label', $item?->label) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        @endif
                        <div>
                            <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                            <textarea name="description" id="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description', $item?->description) }}</textarea>
                        </div>
                    </div>
                </div>

                @if(isset($rules['response_time_minutes']))
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">SLA Settings</h3>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="response_time_minutes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Response Time (minutes)</label>
                                <input type="number" name="response_time_minutes" id="response_time_minutes" value="{{ old('response_time_minutes', $item?->response_time_minutes) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            </div>
                            <div>
                                <label for="resolution_time_hours" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Resolution Time (hours)</label>
                                <input type="number" name="resolution_time_hours" id="resolution_time_hours" value="{{ old('resolution_time_hours', $item?->resolution_time_hours) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            </div>
                        </div>
                        @if(isset($rules['example_cases']))
                        <div class="mt-5">
                            <label for="example_cases" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Example Cases</label>
                            <textarea name="example_cases" id="example_cases" rows="3" placeholder="Example usage..." class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('example_cases', $item?->example_cases) }}</textarea>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="color" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Color</label>
                            <input type="color" name="color" id="color" value="{{ old('color', $item?->color ?? '#000000') }}" class="h-11 w-full cursor-pointer rounded-lg border border-gray-300 bg-transparent px-2 dark:border-gray-700 dark:bg-gray-900" />
                        </div>
                        <div>
                            <label for="sort_order" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $item?->sort_order ?? 0) }}" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Preview</h3>
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Name</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ old('name', $item?->name ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Code</span>
                            <code class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ old('code', $item?->code ?? '-') }}</code>
                        </div>
                        <div class="flex justify-between">
                            <span>Color</span>
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-4 w-4 rounded-full border border-gray-200 dark:border-gray-700" style="background-color: {{ old('color', $item?->color ?? '#000000') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Status</h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="hidden" name="is_active" value="0" />
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item?->is_active ?? true) ? 'checked' : '' }} class="peer sr-only" />
                                <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-600"></div>
                            </label>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">Active</span>
                        </div>
                        @if(isset($rules['is_closed']))
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="hidden" name="is_closed" value="0" />
                                <input type="checkbox" name="is_closed" value="1" {{ old('is_closed', $item?->is_closed ?? false) ? 'checked' : '' }} class="peer sr-only" />
                                <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-600"></div>
                            </label>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">Closed Status</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route($viewName.'.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Cancel</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-600">{{ $isEdit ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection
