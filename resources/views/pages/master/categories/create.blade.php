@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Create Category" :items="[['label' => 'Categories', 'url' => route('categories.index')], ['label' => 'Create']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Add a new asset category and assign it to a master category group and refresh lifecycle.
                </p>
            </div>
            <a href="{{ route('categories.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Categories
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('categories.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card: General Category Details --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="folder" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Category Information</h3>
                                <p class="text-[11px] text-gray-400">Code prefix, title, and master classification</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Category Code Prefix <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. LAP / DSK / MON" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono uppercase text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('code') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Category Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Laptop / Workstation" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Master Category Group <span class="text-error-500">*</span>
                            </label>
                            <div x-data="{ selectedId: '{{ old('master_category_id', $masterCategories->first()?->id) }}' }">
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

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Device Type Classification
                            </label>
                            <select name="device_type"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                <option value="other" {{ old('device_type', 'other') === 'other' ? 'selected' : '' }}>General Hardware / Other</option>
                                <option value="laptop" {{ old('device_type') === 'laptop' ? 'selected' : '' }}>Laptop / Notebook</option>
                                <option value="desktop" {{ old('device_type') === 'desktop' ? 'selected' : '' }}>Desktop / Workstation</option>
                                <option value="display" {{ old('device_type') === 'display' ? 'selected' : '' }}>Display / Monitor</option>
                                <option value="infrastructure" {{ old('device_type') === 'infrastructure' ? 'selected' : '' }}>Server & Network Equipment</option>
                                <option value="mobile" {{ old('device_type') === 'mobile' ? 'selected' : '' }}>Mobile & Tablet</option>
                                <option value="peripheral" {{ old('device_type') === 'peripheral' ? 'selected' : '' }}>Peripheral / Accessory</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Target Useful Life Cycle (Years)
                            </label>
                            <input type="number" name="useful_life_years" value="{{ old('useful_life_years', 4) }}" min="1" max="30"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            New Record
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Save Category
                        </button>

                        <button type="submit" name="create_another" value="1" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-500/30 bg-brand-50/50 px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-100/60 active:scale-[0.98] dark:bg-brand-950/20 dark:text-brand-400 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Save & Add Another
                        </button>

                        <a href="{{ route('categories.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
