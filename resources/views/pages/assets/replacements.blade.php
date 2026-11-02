@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
        selectAll: false,
        selectedAssets: [],
        toggleAll() {
            this.selectAll = !this.selectAll;
            if (this.selectAll) {
                this.selectedAssets = Array.from(document.querySelectorAll('.asset-checkbox')).map(el => JSON.parse(el.dataset.asset));
            } else {
                this.selectedAssets = [];
            }
        },
        toggleAsset(asset) {
            const index = this.selectedAssets.findIndex(a => a.id === asset.id);
            if (index > -1) {
                this.selectedAssets.splice(index, 1);
            } else {
                this.selectedAssets.push(asset);
            }
            this.selectAll = this.selectedAssets.length === document.querySelectorAll('.asset-checkbox').length;
        },
        exportSelectedAssets(assets = this.selectedAssets) {
            const selected = assets && assets.length ? assets : this.selectedAssets;
            if (!selected || !selected.length) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("assets.export") }}';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            selected.forEach(asset => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = asset.id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    }">
        <x-common.page-breadcrumb pageTitle="Hardware Replacement Planner" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => 'Replacement Planner']]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Forecast equipment lifecycle, identify aging fleets, and plan hardware refresh cycles.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Floating Bulk Action Bar --}}
        <div x-show="selectedAssets.length > 0"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="sticky top-20 z-30 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-brand-200 bg-brand-50/95 p-3.5 shadow-lg backdrop-blur dark:border-brand-900/50 dark:bg-brand-950/90"
            style="display: none;">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 text-white">
                    <i data-lucide="check-square" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-brand-900 dark:text-brand-100">
                        <span x-text="selectedAssets.length"></span> assets selected
                    </span>
                    <p class="text-[11px] text-brand-700/80 dark:text-brand-300/80">Batch actions for replacement candidates</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="exportSelectedAssets(selectedAssets)"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-600 active:scale-[0.98]">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Export Selected
                </button>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Device Tabs + Filters Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                @php
                    $currentTab = request('tab');
                    $tabFilters = array_filter(request()->except(['page', 'tab']));
                    $tabs = [
                        ['key' => null,       'label' => 'All Due',          'count' => $replacementSummary['total'],    'icon' => 'layers',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'critical',  'label' => 'Critical (≥5y)',   'count' => $replacementSummary['critical'], 'icon' => 'alert-circle',  'badge' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'],
                        ['key' => 'high',      'label' => 'High (≥4y)',       'count' => $replacementSummary['high'],     'icon' => 'clock',         'badge' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500'],
                        ['key' => 'deployed',  'label' => 'Deployed',         'count' => $replacementSummary['deployed'], 'icon' => 'monitor',       'badge' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                        ['key' => 'broken',    'label' => 'Broken',           'count' => $replacementSummary['broken'],   'icon' => 'alert-triangle','badge' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500'],
                        ['key' => 'ready',     'label' => 'Ready to Deploy',  'count' => $replacementSummary['ready'],    'icon' => 'circle-check',  'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                        ['key' => 'retired',   'label' => 'Retired',          'count' => $replacementSummary['retired'],  'icon' => 'trash-2',       'badge' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400'],
                    ];
                @endphp
                <nav class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-track]:bg-transparent">
                    @foreach($tabs as $t)
                        @php
                            $isActive = $currentTab === $t['key'];
                            $tabUrl = route('assets.replacements', $t['key'] ? array_merge($tabFilters, ['tab' => $t['key']]) : $tabFilters);
                        @endphp
                        <a href="{{ $tabUrl }}"
                           class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all duration-150 {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            <i data-lucide="{{ $t['icon'] }}" class="w-3.5 h-3.5"></i>
                            {{ $t['label'] }}
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $t['badge'] }}">{{ number_format($t['count']) }}</span>
                        </a>
                    @endforeach
                </nav>

                {{-- Toolbar: Search + Filter Dropdown + Export --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('assets.replacements') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
                        @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
                        @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                        @if(request('sort_dir')) <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, device, serial, user..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('type')) $activeCount++;
                            if (request('company_id')) $activeCount++;
                            if (request('department_id')) $activeCount++;
                            if (request('category_id')) $activeCount++;
                            if (request('status_id')) $activeCount++;
                        @endphp
                        <div class="relative" @click.away="filterOpen = false">
                            <button type="button" @click="filterOpen = !filterOpen"
                                class="relative inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                Filters
                                @if($activeCount > 0)
                                    <span class="inline-flex items-center justify-center min-w-[18px] h-4 px-1 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                                @endif
                            </button>

                            <div x-show="filterOpen"
                                x-transition
                                class="absolute left-0 top-full z-50 mt-2 w-80 sm:w-96 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                style="display: none;">
                                <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-2.5 text-left">
                                    <div class="col-span-2">
                                        <x-form.searchable-select name="type" label="Device Classification" :options="[
                                            ['id' => 'laptop', 'text' => 'Laptops / Notebooks'],
                                            ['id' => 'desktop', 'text' => 'Desktop PCs'],
                                            ['id' => 'display', 'text' => 'Monitors & Displays']
                                        ]" :selected="request('type')" placeholder="All Hardware Types" />
                                    </div>

                                    <div class="col-span-2">
                                        <x-form.searchable-select name="company_id" label="Company" :options="$companies->map(fn($cmp) => ['id' => $cmp->id, 'text' => $cmp->name])->toArray()" :selected="request('company_id')" placeholder="All Companies" />
                                    </div>

                                    <div class="col-span-2 sm:col-span-1">
                                        <x-form.searchable-select name="category_id" label="Category" :options="$categories->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray()" :selected="request('category_id')" placeholder="All Categories" />
                                    </div>

                                    <div class="col-span-2 sm:col-span-1">
                                        <x-form.searchable-select name="status_id" label="Status" :options="$statuses->map(fn($s) => ['id' => $s->id, 'text' => $s->name])->toArray()" :selected="request('status_id')" placeholder="All Statuses" />
                                    </div>

                                    <div class="col-span-2">
                                        <x-form.searchable-select name="department_id" label="Department" :options="$departments->map(fn($d) => ['id' => $d->id, 'text' => $d->name])->toArray()" :selected="request('department_id')" placeholder="All Departments" />
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('assets.replacements', request('tab') ? ['tab' => request('tab')] : []) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear All Filters
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(request()->filled('search') || $activeCount > 0)
                            <a href="{{ route('assets.replacements', request('tab') ? ['tab' => request('tab')] : []) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('assets.replacements.export', request()->query()) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition-all">
                            <i data-lucide="download" class="w-3.5 h-3.5 text-gray-500"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">
                                <label class="flex cursor-pointer items-center select-none">
                                    <div class="relative">
                                        <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                        <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                            class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                            <span :class="selectAll ? '' : 'opacity-0'">
                                                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="asset_tag" label="Asset Tag & Device" />
                            </th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Hardware & Serial</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Priority</th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="age" label="Age & Acquisition" />
                            </th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="holder" label="Assigned Holder" />
                            </th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Company & Dept</th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="status" label="Status" />
                            </th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="purchase_cost" label="Valuation / Cost" />
                            </th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            @php
                                $statusName = optional($asset->status)->name ?? 'Unknown';
                                $statusColor = optional($asset->status)->color ?? 'gray';
                                $ageYears = $asset->age_years ?? 4.0;
                                $priority = $asset->replacement_priority ?? 'High';
                                $isCritical = $priority === 'Critical';
                                $acquisitionDate = $asset->release_date ?? $asset->purchase_date;

                                if ($isCritical) {
                                    $rowBg = 'bg-red-50/40 dark:bg-red-500/5 hover:bg-red-100/40 dark:hover:bg-red-500/10';
                                } else {
                                    $rowBg = 'hover:bg-gray-50/80 dark:hover:bg-white/[0.02]';
                                }
                            @endphp
                            <tr class="transition-colors duration-150 {{ $rowBg }}">
                                {{-- Checkbox --}}
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox"
                                                :checked="selectedAssets.some(a => a.id === {{ $asset->id }})"
                                                @click="toggleAsset({
                                                    id: {{ $asset->id }},
                                                    tag: '{{ $asset->asset_tag }}'
                                                })"
                                                class="asset-checkbox sr-only"
                                                data-asset='{"id":{{ $asset->id }},"tag":"{{ $asset->asset_tag }}"}'>
                                            <div :class="selectedAssets.some(a => a.id === {{ $asset->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                                <span :class="selectedAssets.some(a => a.id === {{ $asset->id }}) ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>

                                {{-- Asset Tag & Device --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <a href="{{ route('assets.show', $asset) }}" class="font-mono font-semibold tracking-tight text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400">
                                            {{ $asset->asset_tag }}
                                        </a>
                                        <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500">
                                            {{ $asset->device_name ?: 'Unnamed Device' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Hardware & Serial --}}
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $asset->product?->brand?->name ?? '-' }} {{ $asset->product?->model ?? '' }}
                                        </span>
                                        <span class="font-mono text-[11px] tracking-tight text-gray-400">
                                            S/N: {{ $asset->serial_number ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Priority --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($isCritical)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-[11px] font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                                            Critical (≥{{ ($asset->useful_life_years ?? 4) + 1 }}y / Service)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"></i>
                                            High (≥{{ $asset->useful_life_years ?? 4 }}y)
                                        </span>
                                    @endif
                                </td>

                                {{-- Age & Acquisition --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900 dark:text-white tabular-nums">
                                            {{ $ageYears !== null ? $ageYears . ' Years' : '—' }}
                                            <span class="text-[11px] font-normal text-gray-400">(Tgt: {{ $asset->useful_life_years ?? 4 }}y)</span>
                                        </span>
                                        <span class="text-[10px] text-gray-400 tabular-nums">
                                            {{ $acquisitionDate ? $acquisitionDate->format('M Y') : 'Unknown Date' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Assigned Holder (Exact Asset Index Pattern) --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($asset->isCurrentlyCheckedOut())
                                        @php $holderName = $asset->currentCheckout->checkedOutToUser->name ?? '-'; @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-[10px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                {{ strtoupper(substr($holderName, 0, 1)) }}
                                            </span>
                                            <div class="flex flex-col min-w-0">
                                                <div class="flex items-center gap-1">
                                                    <span class="font-medium text-gray-900 dark:text-white truncate max-w-[140px]">{{ $holderName }}</span>
                                                    @if($asset->isOverdue())
                                                        <span class="inline-flex items-center rounded bg-red-100 px-1 py-0.2 text-[9px] font-bold text-red-700 dark:bg-red-950/40 dark:text-red-400">
                                                            Overdue
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] text-gray-400 truncate max-w-[140px]">
                                                    {{ $asset->currentCheckout->checkedOutToUser->department->name ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-50 text-[10px] text-gray-400 dark:bg-gray-800/40">
                                                —
                                            </span>
                                            <div class="flex flex-col">
                                                <span class="text-xs text-gray-400 italic">Not Assigned</span>
                                                @if($asset->lastCheckout && $asset->lastCheckout->checkedOutToUser)
                                                    <span class="text-[10px] text-gray-400">Prev: {{ $asset->lastCheckout->checkedOutToUser->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                {{-- Company & Dept --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $asset->department?->company?->name ?? '-' }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">{{ $asset->department?->name ?? '-' }}</span>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col gap-1 items-start">
                                        <x-ui.badge :color="$statusColor" variant="light" size="sm" :lucideIcon="optional($asset->status)->icon">
                                            {{ $statusName }}
                                        </x-ui.badge>
                                        @if($asset->is_components_stripped)
                                            <x-ui.badge color="warning" variant="light" size="sm" lucideIcon="cpu">
                                                Stripped
                                            </x-ui.badge>
                                        @endif
                                    </div>
                                </td>

                                {{-- Cost / Valuation (Dynamic, Zero Hardcoded Values) --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col font-mono">
                                        @if($asset->purchase_cost)
                                            <span class="font-semibold text-gray-900 dark:text-white tabular-nums">
                                                Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 tabular-nums">
                                                @if($asset->depreciation)
                                                    Book: Rp {{ number_format($asset->bookValue, 0, ',', '.') }}
                                                @else
                                                    Cost only
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-gray-400 font-sans italic">—</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Actions (Exact TailAdmin Action Dropdown) --}}
                                <td class="px-4 py-2.5 text-end">
                                    <div x-data="{
                                        open: false,
                                        dropdownStyle: '',
                                        toggle(e) {
                                            this.open = !this.open;
                                            if (this.open) {
                                                const rect = e.currentTarget.getBoundingClientRect();
                                                const menuHeight = 160;
                                                const spaceBelow = window.innerHeight - rect.bottom;
                                                const top = spaceBelow < menuHeight ? Math.max(10, rect.top - menuHeight) : (rect.bottom + 4);
                                                const left = Math.max(10, rect.right - 160);
                                                this.dropdownStyle = `position: fixed; top: ${top}px; left: ${left}px; z-index: 9999; display: block;`;
                                            }
                                        }
                                    }" @click.away="open = false" @scroll.window="open = false" class="inline-block text-left">
                                        <button type="button" @click="toggle($event)" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>

                                        <div x-show="open"
                                            x-transition
                                            :style="open ? dropdownStyle : 'display: none;'"
                                            class="w-40 rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                            style="display: none;">
                                            
                                            <a href="{{ route('assets.show', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                                                View Details
                                            </a>

                                            <a href="{{ route('assets.edit', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-gray-400"></i>
                                                Edit
                                            </a>

                                            @if($asset->isCurrentlyCheckedOut())
                                                <a href="{{ route('assets.checkin', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-orange-600 hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-500/10 transition-colors">
                                                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                                    Check-in
                                                </a>
                                            @else
                                                <a href="{{ route('assets.checkout', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-500/10 transition-colors">
                                                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                                    Check-out
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <i data-lucide="check-circle" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">All Fleets are Healthy</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        No assets have exceeded their replacement threshold for this category.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Per page:</span>
                    <select onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();"
                        class="h-8 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
