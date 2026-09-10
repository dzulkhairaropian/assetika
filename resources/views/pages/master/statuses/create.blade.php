@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create Status" :items="[['label' => 'Asset Statuses', 'url' => route('statuses.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Add a new status label with badge color and icon.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('statuses.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

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
                                    Status Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Ready to Deploy" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                                @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Master Category <span class="text-error-500">*</span>
                                </label>
                                <div x-data="{ selectedId: '{{ old('master_category_id') }}' }">
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

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Icon (Lucide Icons)
                                </label>
                                <input type="text" id="status-icon" name="icon" value="{{ old('icon') }}" placeholder="e.g., circle-check"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                                <div class="mt-2 flex flex-wrap gap-1.5">
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
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Color (Hex / Theme)
                                </label>
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="color" id="color-picker" value="{{ old('color', '#465FFF') }}" onchange="document.getElementById('status-color').value = this.value" class="h-9 w-12 cursor-pointer rounded-lg border border-gray-200 bg-transparent p-1 dark:border-gray-800 dark:bg-gray-950" />
                                    <input type="text" id="status-color" name="color" value="{{ old('color') }}" placeholder="e.g., #10b981 or success"
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

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Description
                                </label>
                                <textarea name="description" rows="3" placeholder="Enter description"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('description') }}</textarea>
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
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Create Status
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                            Create & Create New
                        </button>
                        <a href="{{ route('statuses.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
