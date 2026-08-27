@php $isEdit = isset($item) && $item !== null; @endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-4 min-w-0">
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

        <form method="POST" action="{{ $isEdit ? route($viewName.'.update', $item) : route($viewName.'.store') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_340px]">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Basic Information</h3>
                    </div>
                    <div class="p-4 sm:p-5 space-y-3.5">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="name" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $item?->name) }}" required class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="code" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Code <span class="text-red-500">*</span></label>
                                <input type="text" name="code" id="code" value="{{ old('code', $item?->code) }}" required class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                @error('code') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @if(isset($rules['label']))
                        <div>
                            <label for="label" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Label</label>
                            <input type="text" name="label" id="label" value="{{ old('label', $item?->label) }}" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        @endif
                        <div>
                            <label for="description" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3" class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('description', $item?->description) }}</textarea>
                        </div>
                    </div>
                </div>

                @if(isset($rules['response_time_minutes']))
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">SLA Settings</h3>
                    </div>
                    <div class="p-4 sm:p-5 space-y-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="response_time_minutes" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Response Time (minutes)</label>
                                <input type="number" name="response_time_minutes" id="response_time_minutes" value="{{ old('response_time_minutes', $item?->response_time_minutes) }}" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>
                            <div>
                                <label for="resolution_time_hours" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Resolution Time (hours)</label>
                                <input type="number" name="resolution_time_hours" id="resolution_time_hours" value="{{ old('resolution_time_hours', $item?->resolution_time_hours) }}" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>
                        </div>
                        @if(isset($rules['example_cases']))
                        <div class="mt-3">
                            <label for="example_cases" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Example Cases</label>
                            <textarea name="example_cases" id="example_cases" rows="2" placeholder="Example usage..." class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('example_cases', $item?->example_cases) }}</textarea>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Appearance</h3>
                    <div class="space-y-3">
                        <div>
                            <label for="icon-input" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Feather Icon</label>
                            <input type="text" id="icon-input" name="icon" value="{{ old('icon', $item?->icon) }}" placeholder="e.g. alert-circle" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            <div class="mt-2 flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                @foreach([
                                    'alert-circle', 'alert-triangle', 'check-circle', 'clock',
                                    'file-text', 'help-circle', 'info', 'loader', 'lock',
                                    'refresh-cw', 'shield', 'star', 'tag', 'thumbs-up',
                                    'tool', 'user', 'users', 'x-circle', 'zap'
                                ] as $i)
                                    <button type="button" onclick="document.getElementById('icon-input').value = '{{ $i }}'" class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] rounded border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400">
                                        <i data-feather="{{ $i }}" class="w-3 h-3"></i> {{ $i }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label for="color" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="color" id="color" value="{{ old('color', $item?->color ?? '#3B82F6') }}" class="h-9 w-12 cursor-pointer rounded-lg border border-gray-200 bg-transparent p-1 dark:border-gray-800 dark:bg-gray-950" />
                                <input type="text" id="color-text" name="color_hex" value="{{ old('color', $item?->color ?? '#3B82F6') }}" onchange="document.getElementById('color').value = this.value" class="h-9 flex-1 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>
                        </div>
                        <div>
                            <label for="sort_order" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $item?->sort_order ?? 0) }}" min="0" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2.5">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="hidden" name="is_active" value="0" />
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item?->is_active ?? true) ? 'checked' : '' }} class="peer sr-only" />
                                <div class="peer h-5 w-9 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-700"></div>
                            </label>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Active</span>
                        </div>
                        @if(isset($rules['is_closed']))
                        <div class="flex items-center gap-2.5">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="hidden" name="is_closed" value="0" />
                                <input type="checkbox" name="is_closed" value="1" {{ old('is_closed', $item?->is_closed ?? false) ? 'checked' : '' }} class="peer sr-only" />
                                <div class="peer h-5 w-9 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-700"></div>
                            </label>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Closed Status</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                        <i data-feather="save" class="w-4 h-4 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            {{ $isEdit ? 'Update ' . $title : 'Create ' . $title }}
                        </button>
                        @if(! $isEdit)
                            <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:border-brand-500 dark:bg-gray-950 dark:text-brand-400 transition">
                                Create & Create New
                            </button>
                        @endif
                        <a href="{{ route($viewName.'.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
