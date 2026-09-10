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

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            {{ $isEdit ? 'Update details and settings for ' . $item->name : 'Add a new ' . strtolower($title) . ' label with badge color and icon.' }}
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form method="POST" action="{{ $isEdit ? route($viewName.'.update', $item) : route($viewName.'.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="space-y-6">
                <!-- General Information Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>
                        General Information
                    </h3>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label for="name" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                {{ str_contains(strtolower($title), 'status') ? 'Status Name' : $title . ' Name' }} <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $item?->name) }}" required placeholder="e.g., In Progress"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="code" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Code <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="code" id="code" value="{{ old('code', $item?->code) }}" required placeholder="e.g., IN-PROGRESS"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('code') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        @if(isset($rules['label']))
                            <div class="lg:col-span-2">
                                <label for="label" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Label</label>
                                <input type="text" name="label" id="label" value="{{ old('label', $item?->label) }}" placeholder="Custom display label"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            </div>
                        @endif

                        <div>
                            <label for="status-icon" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Icon (Lucide Icons)
                            </label>
                            <input type="text" id="status-icon" name="icon" value="{{ old('icon', $item?->icon) }}" placeholder="e.g., circle-check"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            <div class="mt-2 flex flex-wrap gap-1.5 max-h-32 overflow-y-auto">
                                @foreach([
                                    'circle-check', 'circle-x', 'circle-alert', 'triangle-alert',
                                    'user', 'user-check', 'users', 'bookmark',
                                    'refresh-cw', 'rotate-ccw', 'loader', 'clock',
                                    'check', 'x', 'thumbs-up',
                                    'wrench', 'settings', 'monitor',
                                    'file-text', 'archive', 'package',
                                    'trash-2', 'help-circle', 'info',
                                    'log-in', 'log-out', 'eye',
                                    'download', 'search', 'database',
                                    'key', 'cpu', 'wifi',
                                    'dollar-sign', 'bell', 'plus',
                                    'edit-3', 'activity', 'shield',
                                    'star', 'lock', 'zap'
                                ] as $iconName)
                                    <button type="button" onclick="document.getElementById('status-icon').value = '{{ $iconName }}'" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition text-gray-600 dark:text-gray-400">
                                        <i data-lucide="{{ $iconName }}" class="w-3 h-3"></i> {{ $iconName }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label for="status-color" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Color (Hex / Theme)
                            </label>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="color" id="color-picker" value="{{ old('color', str_starts_with($item?->color ?? '', '#') ? $item->color : '#465FFF') }}" onchange="document.getElementById('status-color').value = this.value" class="h-9 w-12 cursor-pointer rounded-lg border border-gray-200 bg-transparent p-1 dark:border-gray-800 dark:bg-gray-950" />
                                <input type="text" id="status-color" name="color" value="{{ old('color', $item?->color) }}" placeholder="e.g., #10b981 or success"
                                    class="h-9 flex-1 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach([
                                    'primary' => '#465FFF',
                                    'secondary' => '#667085',
                                    'success' => '#12B76A',
                                    'danger' => '#F04438',
                                    'warning' => '#F79009',
                                    'info' => '#0BA5EC',
                                    'light' => '#E4E7EC',
                                    'dark' => '#1D2939',
                                    'orange' => '#FB6514',
                                    'emerald' => '#10B981',
                                    'purple' => '#8B5CF6',
                                ] as $name => $hex)
                                    <button type="button" onclick="document.getElementById('status-color').value = '{{ $name }}'; document.getElementById('color-picker').value = '{{ $hex }}';" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition text-gray-600 dark:text-gray-400">
                                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $hex }}"></span>
                                        {{ $name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label for="sort_order" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $item?->sort_order ?? 0) }}" min="0"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div class="lg:col-span-2">
                            <label for="description" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Description</label>
                            <textarea name="description" id="description" rows="3" placeholder="Enter description..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('description', $item?->description) }}</textarea>
                        </div>
                    </div>
                </div>

                @if(isset($rules['response_time_minutes']))
                <!-- SLA Settings Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="clock" class="w-5 h-5 text-brand-500"></i>
                        SLA Settings
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label for="response_time_minutes" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Response Time (minutes)</label>
                            <input type="number" name="response_time_minutes" id="response_time_minutes" value="{{ old('response_time_minutes', $item?->response_time_minutes) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>
                        <div>
                            <label for="resolution_time_hours" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Resolution Time (hours)</label>
                            <input type="number" name="resolution_time_hours" id="resolution_time_hours" value="{{ old('resolution_time_hours', $item?->resolution_time_hours) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>
                        @if(isset($rules['example_cases']))
                        <div class="lg:col-span-2">
                            <label for="example_cases" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Example Cases</label>
                            <textarea name="example_cases" id="example_cases" rows="2" placeholder="Example usage..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('example_cases', $item?->example_cases) }}</textarea>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar Actions & Settings -->
            <div class="space-y-6">
                <!-- Status Options Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="settings" class="w-5 h-5 text-brand-500"></i>
                        Status Options
                    </h3>
                    <div class="space-y-3.5">
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

                <!-- Form Action Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-lucide="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center gap-1.5 items-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            {{ $isEdit ? 'Update ' . $title : 'Create ' . $title }}
                        </button>
                        @if(! $isEdit)
                            <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center gap-1.5 items-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Create & Create New
                            </button>
                        @endif
                        <a href="{{ route($viewName.'.index') }}" class="inline-flex w-full justify-center gap-1.5 items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
