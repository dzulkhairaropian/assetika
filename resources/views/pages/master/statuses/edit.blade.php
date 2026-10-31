@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Edit Status" :items="[['label' => 'Asset Statuses', 'url' => route('statuses.index')], ['label' => 'Edit']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Update status title, master category group, or badge display color for <strong class="text-gray-900 dark:text-white">{{ $status->name }}</strong>.
                </p>
            </div>
            <a href="{{ route('statuses.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Statuses
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('statuses.update', $status) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf
            @method('PUT')

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card: Status Profile --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="activity" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Status Configuration</h3>
                                <p class="text-[11px] text-gray-400">Lifecycle title, master group, and icon display</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Status Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $status->name) }}" placeholder="e.g. Ready to Deploy / In Service / Retired" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Master Category Group <span class="text-error-500">*</span>
                            </label>
                            <div x-data="{ selectedId: '{{ old('master_category_id', $status->master_category_id) }}' }">
                                <input type="hidden" name="master_category_id" :value="selectedId" required />
                                <div class="flex flex-wrap gap-1.5 pt-0.5">
                                    @foreach($masterCategories as $master)
                                        <button type="button" @click="selectedId = '{{ $master->id }}'"
                                            :class="selectedId == '{{ $master->id }}' ? 'bg-brand-500 text-white shadow-xs font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                                            class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs transition">
                                            {{ $master->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @error('master_category_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Icon Name (Lucide Icons)
                            </label>
                            <input type="text" id="status-icon" name="icon" value="{{ old('icon', $status->icon) }}" placeholder="e.g. circle-check / wrench / trash-2"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach(['circle-check', 'circle-x', 'circle-alert', 'triangle-alert', 'wrench', 'trash-2', 'archive', 'rotate-ccw', 'monitor', 'laptop', 'shield-check', 'user-check'] as $iconName)
                                    <button type="button" onclick="document.getElementById('status-icon').value = '{{ $iconName }}'"
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-2 py-1 text-[11px] font-mono text-gray-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition">
                                        <i data-lucide="{{ $iconName }}" class="w-3 h-3"></i>
                                        {{ $iconName }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Badge Color --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                            Editing
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Update Status
                        </button>

                        <a href="{{ route('statuses.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Color Theme Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="palette" class="w-4 h-4 text-brand-500"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Badge Theme Color</h3>
                    </div>

                    <div x-data="{ selectedColor: '{{ old('color', $status->color ?? 'success') }}' }" class="space-y-3">
                        <input type="hidden" name="color" :value="selectedColor" />
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([
                                ['key' => 'success', 'label' => 'Success (Green)', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                ['key' => 'primary', 'label' => 'Primary (Brand)', 'badge' => 'bg-brand-50 text-brand-700 border-brand-200'],
                                ['key' => 'warning', 'label' => 'Warning (Amber)', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                ['key' => 'danger',  'label' => 'Danger (Red)',    'badge' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                ['key' => 'info',    'label' => 'Info (Blue)',     'badge' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                ['key' => 'gray',    'label' => 'Gray (Neutral)',  'badge' => 'bg-gray-100 text-gray-700 border-gray-200'],
                            ] as $c)
                                <button type="button" @click="selectedColor = '{{ $c['key'] }}'"
                                    :class="selectedColor === '{{ $c['key'] }}' ? 'ring-2 ring-brand-500 ring-offset-1 font-bold' : 'opacity-80 hover:opacity-100'"
                                    class="flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs transition {{ $c['badge'] }}">
                                    <span class="h-2 w-2 rounded-full bg-current"></span>
                                    <span>{{ $c['key'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
