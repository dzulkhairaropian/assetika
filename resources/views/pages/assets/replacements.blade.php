@extends('layouts.app')

@section('content')
    @php
        $tabFilters = array_filter(request()->except(['page', 'tab']));
        $brokenStatusId = $statuses->first(fn ($s) => strtolower($s->name) === 'broken')?->id;
        $repairStatusId = $statuses->first(fn ($s) => in_array(strtolower($s->name), ['in repair', 'service', 'service & repair'], true))?->id;
        $retiredStatusId = $statuses->first(fn ($s) => strtolower($s->name) === 'retired')?->id;
        $defaultCheckinStatusId = $brokenStatusId ?? $repairStatusId ?? $retiredStatusId ?? $statuses->first()?->id ?? '';
    @endphp
    <div class="space-y-4" x-data="{
        selectAll: false,
        selectedAssets: [],
        replaceModalOpen: false,
        replaceAsset: null,
        replacementStock: @js($replacementStockOptions),
        replaceForm: {
            replacement_asset_id: '',
            replacement_mode: 'permanent',
            checkin_status_id: '{{ $defaultCheckinStatusId }}',
            replacement_date: '{{ date('Y-m-d') }}',
            expected_return_date: '{{ date('Y-m-d', strtotime('+14 days')) }}',
            condition_report: '',
            notes: ''
        },
        get selectedReplacement() {
            if (!this.replaceForm.replacement_asset_id) return null;
            return this.replacementStock.find(item => String(item.id) === String(this.replaceForm.replacement_asset_id)) || null;
        },
        get isReplacementOlder() {
            return Boolean(this.selectedReplacement && this.replaceAsset && Number(this.selectedReplacement.age_years) > Number(this.replaceAsset.age));
        },
        get isReplacementAged() {
            return Boolean(this.selectedReplacement && this.selectedReplacement.is_aged);
        },
        openReplaceModal(assetData) {
            this.replaceAsset = assetData;
            this.replaceForm.replacement_asset_id = '';
            this.replaceForm.replacement_mode = 'permanent';
            this.replaceForm.checkin_status_id = assetData.default_checkin_status_id || '{{ $defaultCheckinStatusId }}';
            this.replaceForm.condition_report = assetData.reasons && assetData.reasons.length
                ? 'Replacement triggered: ' + assetData.reasons.join(', ')
                : 'Returned for hardware replacement';
            this.replaceForm.notes = '';
            this.replaceModalOpen = true;
            this.$nextTick(() => {
                if (window.createIcons && window.lucideIcons) {
                    window.createIcons({ icons: window.lucideIcons });
                }
            });
        },
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
            Identify eligible users for replacement using age, hardware failures, repair frequency, and ticket history, then swap safely from available stock.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif
        @if($errors->any())
            <x-ui.alert variant="error" message="{{ $errors->first() }}" />
        @endif

        {{-- KPI Summary Strip --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('assets.replacements', array_merge($tabFilters ?? [], ['tab' => 'user_needs'])) }}"
               class="group rounded-2xl border border-gray-200 bg-white p-4 shadow-xs transition hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Users Needing Swap</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($replacementSummary['user_needs'] ?? 0) }}</p>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">Active deployed units eligible</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 group-hover:scale-105 transition-transform">
                        <i data-lucide="user-check" class="h-5 w-5"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('assets.replacements', array_merge($tabFilters ?? [], ['tab' => 'troubled'])) }}"
               class="group rounded-2xl border border-gray-200 bg-white p-4 shadow-xs transition hover:border-amber-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Troubled / Frequent Repairs</p>
                        <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">{{ number_format($replacementSummary['troubled'] ?? 0) }}</p>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">Broken, in repair, or ≥2x repairs</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 group-hover:scale-105 transition-transform">
                        <i data-lucide="wrench" class="h-5 w-5"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('assets.replacements', array_merge($tabFilters ?? [], ['tab' => 'critical'])) }}"
               class="group rounded-2xl border border-gray-200 bg-white p-4 shadow-xs transition hover:border-red-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-red-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Critical Urgency</p>
                        <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400 tabular-nums">{{ number_format($replacementSummary['critical'] ?? 0) }}</p>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">Immediate replacement priority</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 group-hover:scale-105 transition-transform">
                        <i data-lucide="alert-circle" class="h-5 w-5"></i>
                    </div>
                </div>
            </a>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Ready Replacement Pool</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($replacementStockOptions->count()) }}</p>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">Spares available to swap now</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <i data-lucide="package-check" class="h-5 w-5"></i>
                    </div>
                </div>
            </div>
        </div>

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
                        ['key' => null,         'label' => 'All Eligible',       'count' => $replacementSummary['total'],      'icon' => 'layers',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'user_needs', 'label' => 'User Needs Swap',    'count' => $replacementSummary['user_needs'], 'icon' => 'user-check',    'badge' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                        ['key' => 'troubled',   'label' => 'Troubled / Broken',  'count' => $replacementSummary['troubled'],   'icon' => 'wrench',        'badge' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
                        ['key' => 'critical',   'label' => 'Critical',           'count' => $replacementSummary['critical'],   'icon' => 'alert-circle',  'badge' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'],
                        ['key' => 'high',       'label' => 'High',               'count' => $replacementSummary['high'],       'icon' => 'clock',         'badge' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500'],
                        ['key' => 'deployed',   'label' => 'Deployed',           'count' => $replacementSummary['deployed'],   'icon' => 'monitor',       'badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'],
                        ['key' => 'ready',      'label' => 'Ready to Deploy',    'count' => $replacementSummary['ready'],      'icon' => 'circle-check',  'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                        ['key' => 'retired',    'label' => 'Retired',            'count' => $replacementSummary['retired'],    'icon' => 'trash-2',       'badge' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400'],
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
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Eligibility & Urgency</th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="age" label="Age & Acquisition" />
                            </th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="holder" label="Assigned Holder" />
                            </th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Company & Dept</th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="status" label="Status & Notes" />
                            </th>
                            <th class="px-4 py-3 text-start">
                                <x-common.sortable-header column="purchase_cost" label="Valuation & Repairs" />
                            </th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            @php
                                $statusName = optional($asset->status)->name ?? 'Unknown';
                                $statusColor = optional($asset->status)->color ?? 'gray';
                                $ageYears = $asset->age_years ?? 0.0;
                                $eval = $asset->replacement_evaluation;
                                $tier = $eval['tier'];
                                $score = $eval['score'];
                                $reasons = $eval['reasons'];
                                $isCritical = $tier === 'Critical';
                                $isHigh = $tier === 'High';
                                $acquisitionDate = $asset->release_date ?? $asset->purchase_date;
                                $holderUser = $asset->currentCheckout?->checkedOutToUser ?? $asset->assignedToUser;
                                $isCheckedOut = $asset->isCurrentlyCheckedOut();

                                $suggestedCheckinId = $defaultCheckinStatusId;
                                $statusSlug = strtolower($statusName);
                                if ($statusSlug === 'broken' && $brokenStatusId) {
                                    $suggestedCheckinId = $brokenStatusId;
                                } elseif (in_array($statusSlug, ['in repair', 'service', 'service & repair', 'maintenance'], true) && $repairStatusId) {
                                    $suggestedCheckinId = $repairStatusId;
                                } elseif (($ageYears >= ($asset->useful_life_years ?? 4)) && $retiredStatusId) {
                                    $suggestedCheckinId = $retiredStatusId;
                                }

                                if ($isCritical) {
                                    $rowBg = 'bg-red-50/40 dark:bg-red-500/5 hover:bg-red-100/40 dark:hover:bg-red-500/10';
                                } else {
                                    $rowBg = 'hover:bg-gray-50/80 dark:hover:bg-white/[0.02]';
                                }

                                $replacePayload = [
                                    'id' => $asset->id,
                                    'asset_tag' => $asset->asset_tag,
                                    'device_name' => $asset->device_name ?: 'Unnamed Device',
                                    'model' => trim(($asset->product?->brand?->name ?? '').' '.($asset->product?->model ?? $asset->product?->name ?? '')),
                                    'age' => (float) $ageYears,
                                    'status' => $statusName,
                                    'holder_name' => $holderUser?->name ?? 'Unassigned',
                                    'holder_dept' => $holderUser?->department?->name ?? ($asset->department?->name ?? '-'),
                                    'score' => $score,
                                    'tier' => $tier,
                                    'reasons' => $reasons,
                                    'default_checkin_status_id' => $suggestedCheckinId,
                                ];
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

                                {{-- Eligibility & Urgency --}}
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col gap-1.5 min-w-[165px]">
                                        <div class="flex items-center gap-2">
                                            @if($isCritical)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-500/15 dark:text-red-400">
                                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                                    Critical ({{ $score }})
                                                </span>
                                            @elseif($isHigh)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                                    High ({{ $score }})
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                                                    <i data-lucide="info" class="w-3 h-3"></i>
                                                    {{ $tier }} ({{ $score }})
                                                </span>
                                            @endif
                                            <div class="h-1.5 w-14 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800" title="Urgency Score: {{ $score }}/100">
                                                <div class="h-full rounded-full {{ $isCritical ? 'bg-red-500' : ($isHigh ? 'bg-amber-500' : 'bg-brand-500') }}" style="width: {{ $score }}%"></div>
                                            </div>
                                        </div>
                                        @if(!empty($reasons))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($reasons as $reason)
                                                    <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        {{ $reason }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Age & Acquisition --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900 dark:text-white tabular-nums">
                                            {{ $asset->age_years !== null ? $asset->age_years . ' Years' : '—' }}
                                            <span class="text-[11px] font-normal text-gray-400">(Tgt: {{ $asset->useful_life_years ?? 4 }}y)</span>
                                        </span>
                                        <span class="text-[10px] text-gray-400 tabular-nums">
                                            {{ $acquisitionDate ? $acquisitionDate->format('M Y') : 'Unknown Date' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Assigned Holder (Exact Asset Index Pattern) --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($isCheckedOut)
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

                                {{-- Status & Notes --}}
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col gap-1 items-start">
                                        <x-ui.badge :color="$statusColor" variant="light" size="sm" :lucideIcon="optional($asset->status)->icon">
                                            {{ $statusName }}
                                        </x-ui.badge>
                                        @if($asset->is_components_stripped)
                                            <x-ui.badge color="warning" variant="light" size="sm" lucideIcon="cpu">
                                                Stripped
                                            </x-ui.badge>
                                        @endif
                                        @if(filled($asset->notes))
                                            <div class="mt-0.5 flex items-start gap-1 max-w-[190px] text-[11px] leading-snug text-gray-500 dark:text-gray-400" title="{{ $asset->notes }}">
                                                <i data-lucide="file-text" class="mt-0.5 h-3 w-3 shrink-0 text-amber-500 dark:text-amber-400"></i>
                                                <span class="line-clamp-2 break-words">{{ $asset->notes }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Cost / Valuation & Repairs --}}
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
                                        @if($eval['maint_count'] > 0)
                                            <span class="mt-0.5 font-sans text-[10px] font-medium text-amber-600 dark:text-amber-400 tabular-nums">
                                                {{ $eval['maint_count'] }}x Repair{{ $eval['maint_cost'] > 0 ? ' • Rp ' . number_format($eval['maint_cost'], 0, ',', '.') : '' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Actions (Quick Swap + TailAdmin Action Dropdown) --}}
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        @if($isCheckedOut)
                                            <button type="button" @click="openReplaceModal({{ json_encode($replacePayload) }})"
                                                class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-xs hover:bg-brand-600 active:scale-[0.98] transition">
                                                <i data-lucide="repeat" class="w-3 h-3"></i>
                                                Replace
                                            </button>
                                        @endif
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

                                                @if($isCheckedOut)
                                                    <button type="button" @click="open = false; openReplaceModal({{ json_encode($replacePayload) }})"
                                                        class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10 transition-colors">
                                                        <i data-lucide="repeat" class="w-3.5 h-3.5"></i>
                                                        Replace / Swap
                                                    </button>
                                                @endif

                                                <a href="{{ route('assets.show', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                                                    View Details
                                                </a>

                                                <a href="{{ route('assets.edit', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5 text-gray-400"></i>
                                                    Edit
                                                </a>

                                                @if($isCheckedOut)
                                                    <a href="{{ route('assets.checkin', $asset) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-500/10 transition-colors">
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
                                        No assets have triggered replacement eligibility criteria for this filter.
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

        {{-- Modal: Replace / Swap Asset --}}
        <div x-show="replaceModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 backdrop-blur-sm p-4" style="display: none;">
            <div @click.away="replaceModalOpen = false"
                class="my-auto w-full max-w-xl rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            <i data-lucide="repeat" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Replace / Swap User Hardware</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Swap <strong class="font-mono text-brand-600 dark:text-brand-400" x-text="replaceAsset?.asset_tag"></strong>
                                held by <strong class="text-gray-800 dark:text-gray-200" x-text="replaceAsset?.holder_name"></strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="replaceModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <form :action="'/assets/' + (replaceAsset?.id || '') + '/replace'" method="POST" class="mt-4 space-y-4 text-left">
                    @csrf

                    {{-- Current Unit & Eligibility Summary --}}
                    <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-3.5 dark:border-gray-800 dark:bg-gray-950/60">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Returning Unit (Current Holder)</span>
                                <p class="text-xs font-semibold text-gray-900 dark:text-white mt-0.5">
                                    <span class="font-mono text-brand-600 dark:text-brand-400" x-text="replaceAsset?.asset_tag"></span>
                                    — <span x-text="replaceAsset?.model"></span>
                                </p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Age: <strong x-text="(replaceAsset?.age ?? 0) + 'y'"></strong> •
                                    Holder: <span x-text="replaceAsset?.holder_name"></span> (<span x-text="replaceAsset?.holder_dept"></span>)
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-500/15 dark:text-red-400"
                                x-text="(replaceAsset?.tier || 'High') + ' (' + (replaceAsset?.score || 0) + '/100)'"></span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1" x-show="replaceAsset?.reasons?.length">
                            <template x-for="reason in (replaceAsset?.reasons || [])" :key="reason">
                                <span class="inline-flex items-center rounded bg-white px-1.5 py-0.5 text-[10px] font-medium text-gray-700 border border-gray-200 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-300" x-text="reason"></span>
                            </template>
                        </div>
                    </div>

                    {{-- Replacement Mode --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Replacement Type <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <label :class="replaceForm.replacement_mode === 'permanent' ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-500/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950'"
                                class="flex cursor-pointer items-center gap-2.5 rounded-xl border p-2.5 transition">
                                <input type="radio" name="replacement_mode" value="permanent" x-model="replaceForm.replacement_mode" class="sr-only">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                                    <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white leading-tight">Permanent Swap</p>
                                    <p class="text-[10px] text-gray-400">Full hardware refresh</p>
                                </div>
                            </label>

                            <label :class="replaceForm.replacement_mode === 'loaner' ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-500/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950'"
                                class="flex cursor-pointer items-center gap-2.5 rounded-xl border p-2.5 transition">
                                <input type="radio" name="replacement_mode" value="loaner" x-model="replaceForm.replacement_mode" class="sr-only">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                                    <i data-lucide="clock" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white leading-tight">Temporary Loaner</p>
                                    <p class="text-[10px] text-gray-400">Backup while repairing</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Select Replacement Unit from Ready Pool --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Select Replacement Unit (Ready to Deploy Stock) <span class="text-red-500">*</span>
                        </label>
                        <select name="replacement_asset_id" x-model="replaceForm.replacement_asset_id" required
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">-- Choose an available unit --</option>
                            <template x-for="stock in replacementStock" :key="stock.id">
                                <option :value="stock.id"
                                    x-text="stock.asset_tag + ' • ' + stock.model + ' (' + stock.age_years + 'y)' + (stock.notes ? ' — ' + stock.notes : '')"></option>
                            </template>
                        </select>
                        <p x-show="replacementStock.length === 0" class="mt-1 text-[11px] text-red-500">
                            No Ready to Deploy spare units are currently available in inventory.
                        </p>
                    </div>

                    {{-- Selected Replacement Preview & Safety Warning --}}
                    <div x-show="selectedReplacement" class="space-y-2.5">
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-950">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    <span class="font-mono text-brand-600 dark:text-brand-400" x-text="selectedReplacement?.asset_tag"></span>
                                    — <span x-text="selectedReplacement?.model"></span>
                                </span>
                                <span :class="(isReplacementOlder || isReplacementAged) ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400'"
                                    class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                    x-text="'Age: ' + (selectedReplacement?.age_years ?? 0) + 'y'"></span>
                            </div>
                            <div x-show="selectedReplacement?.notes" class="mt-1.5 flex items-start gap-1.5 text-[11px] text-gray-600 dark:text-gray-400">
                                <i data-lucide="file-text" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-500"></i>
                                <span x-text="selectedReplacement?.notes"></span>
                            </div>
                        </div>

                        <div x-show="(isReplacementOlder || isReplacementAged) && replaceForm.replacement_mode === 'permanent'"
                            class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200">
                            <i data-lucide="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400"></i>
                            <div class="space-y-1.5">
                                <p class="font-semibold">Age Mismatch Warning</p>
                                <p class="text-[11px] leading-relaxed">
                                    Selected replacement unit (<strong x-text="(selectedReplacement?.age_years ?? 0) + 'y'"></strong>) is older than the user's current device (<strong x-text="(replaceAsset?.age ?? 0) + 'y'"></strong>) or exceeds its target lifecycle. Recommended only as a temporary loaner.
                                </p>
                                <button type="button" @click="replaceForm.replacement_mode = 'loaner'"
                                    class="inline-flex items-center gap-1 rounded-md bg-amber-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-amber-700 transition">
                                    Switch to Temporary Loaner
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Check-in Status & Dates --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Old Unit Status After Return <span class="text-red-500">*</span>
                            </label>
                            <select name="checkin_status_id" x-model="replaceForm.checkin_status_id" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                @foreach($statuses as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Swap Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="replacement_date" x-model="replaceForm.replacement_date" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                    </div>

                    <div x-show="replaceForm.replacement_mode === 'loaner'">
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Expected Return Date (Loaner) <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="expected_return_date" x-model="replaceForm.expected_return_date" :required="replaceForm.replacement_mode === 'loaner'"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Returned Unit Condition Report <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="condition_report" x-model="replaceForm.condition_report" required placeholder="e.g. Hinge broken, battery swollen, motherboard issue"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Swap Notes (Optional)
                        </label>
                        <textarea name="notes" rows="2" x-model="replaceForm.notes" placeholder="Handover notes, accessories swapped, or reason for replacement..."
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button type="button" @click="replaceModalOpen = false"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" :disabled="!replaceForm.replacement_asset_id"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Confirm Swap</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
