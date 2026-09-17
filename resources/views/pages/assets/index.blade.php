@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
        selectedAssets: [],
        selectAll: false,
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
        submitBulkLabels() {
            if (!this.selectedAssets || !this.selectedAssets.length) return;
            const copies = document.getElementById('labelCopies') ? document.getElementById('labelCopies').value : 1;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("labels.generate", [], false) }}';
            form.target = '_blank';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            this.selectedAssets.forEach(a => {
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'ids[]';
                inputId.value = a.id;
                form.appendChild(inputId);
            });

            const inputCopies = document.createElement('input');
            inputCopies.type = 'hidden';
            inputCopies.name = 'copies';
            inputCopies.value = copies;
            form.appendChild(inputCopies);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },
        exportSelectedAssets(assets = this.selectedAssets) {
            const selected = assets && assets.length ? assets : this.selectedAssets;
            if (!selected || !selected.length) return;
            const url = new URL('{{ route("assets.export") }}', window.location.origin);
            selected.forEach(asset => url.searchParams.append('ids[]', asset.id));
            window.location.href = url.toString();
        }
    }">
        <x-common.page-breadcrumb pageTitle="Asset Management" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage inventory, checkouts, and asset lifecycle across your organization.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <!-- Bulk Action Form -->
        <form id="bulkDeleteForm" action="{{ route('assets.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="asset in selectedAssets" :key="asset.id">
                <input type="hidden" name="ids[]" :value="asset.id">
            </template>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedAssets.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/50 p-3 dark:border-brand-900/30 dark:bg-brand-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedAssets.length"></span> assets selected.
                </span>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] text-gray-500 dark:text-gray-400">Copies per asset:</label>
                    <input type="number" id="labelCopies" min="1" max="10" value="1"
                        class="w-14 rounded-lg border border-gray-300 bg-white dark:bg-gray-900 dark:border-gray-700 px-2 py-1 text-center text-xs font-semibold text-gray-800 dark:text-white">
                </div>
                <button type="button" @click="submitBulkLabels()"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    Print Labels
                </button>
                <button type="button" @click="exportSelectedAssets(selectedAssets)"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Export Selected
                </button>
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected assets?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-error-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-error-600">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden xl:overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Tabs + Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                @php
                    $currentTab = request('tab');
                    $tabFilters = array_filter(request()->except(['page', 'tab']));
                    $tabs = [
                        ['key' => null,       'label' => 'All Assets',      'count' => $inventorySummary['total'],      'icon' => 'layers',       'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'today',    'label' => 'Added Today',     'count' => $inventorySummary['today'],      'icon' => 'calendar',     'badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'],
                        ['key' => 'draft',    'label' => 'Draft',           'count' => $inventorySummary['draft'],      'icon' => 'file-text',    'badge' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-400'],
                        ['key' => 'ready',    'label' => 'Ready to Deploy', 'count' => $inventorySummary['ready'],      'icon' => 'circle-check', 'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                        ['key' => 'deployed', 'label' => 'Deployed',        'count' => $inventorySummary['deployed'],   'icon' => 'monitor',      'badge' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                        ['key' => 'service',  'label' => 'Service & Repair','count' => $inventorySummary['in_service'], 'icon' => 'wrench',       'badge' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500'],
                        ['key' => 'broken',   'label' => 'Broken',          'count' => $inventorySummary['broken'],     'icon' => 'alert-triangle','badge' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'],
                        ['key' => 'retired',  'label' => 'Retired',         'count' => $inventorySummary['retired'],    'icon' => 'trash-2',      'badge' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400'],
                        ['key' => 'sold',     'label' => 'Sold / For Sale', 'count' => $inventorySummary['sold'],       'icon' => 'dollar-sign',  'badge' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400'],
                        ['key' => 'aged',     'label' => 'Aged (≥4y)',      'count' => $inventorySummary['aged'],       'icon' => 'clock',        'badge' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
                    ];
                @endphp
                <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5">
                    @foreach($tabs as $tab)
                        @php
                            $isActive = $currentTab === $tab['key'];
                            $tabUrl = route('assets.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                        @endphp
                        <a href="{{ $tabUrl }}"
                           class="flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
                            <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                            {{ $tab['label'] }}
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('assets.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search assets..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('company_id')) $activeCount++;
                            if (request('status_id')) $activeCount++;
                            if (request('category_id')) $activeCount++;
                            if (request('product_id')) $activeCount++;
                            if (request('department_id')) $activeCount++;
                            if (request('location_id')) $activeCount++;
                            if (request('year')) $activeCount++;
                            if (request('aging')) $activeCount++;
                            if (request('created_from')) $activeCount++;
                            if (request('created_to')) $activeCount++;
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
                                        <x-form.searchable-select name="company_id" label="Company" :options="isset($companies) ? $companies->map(fn($cmp) => ['id' => $cmp->id, 'text' => $cmp->name])->toArray() : []" :selected="request('company_id')" placeholder="All Companies" />
                                    </div>

                                    <div>
                                        <x-form.searchable-select name="status_id" label="Status" :options="$statuses->map(fn($s) => ['id' => $s->id, 'text' => $s->name])->toArray()" :selected="request('status_id')" placeholder="All Statuses" />
                                    </div>

                                    <div>
                                        <x-form.searchable-select name="category_id" label="Category" :options="$categories->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray()" :selected="request('category_id')" placeholder="All Categories" />
                                    </div>

                                    <div>
                                        <x-form.searchable-select name="product_id" label="Product Model" :options="$products->map(fn($p) => ['id' => $p->id, 'text' => $p->name])->toArray()" :selected="request('product_id')" placeholder="All Models" />
                                    </div>

                                    <div>
                                        <x-form.searchable-select name="department_id" label="Department" :options="$departments->map(fn($d) => ['id' => $d->id, 'text' => $d->name])->toArray()" :selected="request('department_id')" placeholder="All Departments" />
                                    </div>

                                    <div>
                                        <x-form.searchable-select name="location_id" label="Location" :options="$locations->map(fn($l) => ['id' => $l->id, 'text' => $l->name])->toArray()" :selected="request('location_id')" placeholder="All Locations" />
                                    </div>

                                    <div>
                                        <x-form.searchable-select name="year" label="Release / Purchase Year" :options="isset($availableYears) ? $availableYears->map(fn($yr) => ['id' => $yr, 'text' => (string)$yr])->toArray() : []" :selected="request('year')" placeholder="All Years" />
                                    </div>

                                    <div class="col-span-2">
                                        <x-form.searchable-select name="aging" label="Lifecycle Aging" :options="[
                                            ['id' => '0-1', 'text' => 'New (< 1 Year)'],
                                            ['id' => '1-2', 'text' => '1 - 2 Years'],
                                            ['id' => '2-3', 'text' => '2 - 3 Years'],
                                            ['id' => '3-4', 'text' => '3 - 4 Years'],
                                            ['id' => '4+',  'text' => 'Aged (≥ 4 Years)']
                                        ]" :selected="request('aging')" placeholder="All Ages" />
                                    </div>

                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Created From (Input Date)</label>
                                        <input type="date" name="created_from" value="{{ request('created_from') }}"
                                            class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                    </div>

                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Created To (Input Date)</label>
                                        <input type="date" name="created_to" value="{{ request('created_to') }}"
                                            class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('assets.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear All Filters
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('assets.export', request()->query()) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-lucide="download" class="w-3.5 h-3.5 text-gray-500"></i>
                            Export Excel
                        </a>
                        <a href="{{ route('assets.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Asset
                        </a>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/90 backdrop-blur dark:border-gray-800 dark:bg-gray-800/90">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">
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
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset Tag / Name</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Product / Serial</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Depreciation / Value</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Holder</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Age</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Warranty</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            @php
                                $statusName = strtolower(optional($asset->status)->name ?? '');
                                $startDate = $asset->release_date ?? $asset->purchase_date;
                                $ageYears = $startDate ? (int) floor($startDate->diffInDays(now()) / 365.25) : null;
                                $isAged = $ageYears !== null && $ageYears >= 4;

                                if (in_array($statusName, ['broken'])) {
                                    $rowBg = 'bg-red-50/50 dark:bg-red-500/10 hover:bg-red-100/50';
                                } elseif (in_array($statusName, ['service', 'maintenance', 'in repair'])) {
                                    $rowBg = 'bg-orange-50/50 dark:bg-orange-500/10 hover:bg-orange-100/50';
                                } elseif (in_array($statusName, ['retired', 'disposal'])) {
                                    $rowBg = 'bg-gray-50 dark:bg-white/[0.02] hover:bg-gray-100/50';
                                } elseif ($isAged) {
                                    $rowBg = 'bg-red-50/40 dark:bg-red-500/10 hover:bg-red-100/40';
                                } else {
                                    $rowBg = 'hover:bg-gray-50/50 dark:hover:bg-white/[0.02]';
                                }
                            @endphp
                            <tr class="transition {{ $rowBg }}">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox"
                                                :checked="selectedAssets.some(a => a.id === {{ $asset->id }})"
                                                @click="toggleAsset({
                                                    id: {{ $asset->id }},
                                                    tag: '{{ $asset->asset_tag }}',
                                                    sn: '{{ $asset->serial_number ?? '-' }}',
                                                    company: '{{ optional(optional($asset->department)->company)->name ?? '-' }}',
                                                    url: '{{ rtrim(env('URL_PRODUCTION', 'https://assetika.diastika.co.id'), '/') . '/scan/asset/' . $asset->id }}'
                                                })"
                                                class="asset-checkbox sr-only"
                                                data-asset='{"id":{{ $asset->id }},"tag":"{{ $asset->asset_tag }}","sn":"{{ $asset->serial_number ?? "-" }}","company":"{{ optional(optional($asset->department)->company)->name ?? "-" }}","url":"{{ rtrim(env('URL_PRODUCTION', 'https://assetika.diastika.co.id'), '/') . '/scan/asset/' . $asset->id }}"}'>
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
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <a href="{{ route('assets.show', $asset) }}" class="block font-mono font-medium tracking-tight text-brand-500 hover:text-brand-600 hover:underline">{{ $asset->asset_tag }}</a>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $asset->device_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $asset->product?->brand?->name ?? '-' }} {{ $asset->product?->model ?? '' }}
                                        </span>
                                        <span class="font-mono text-[11px] tracking-tight text-gray-400">S/N: {{ $asset->serial_number ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $statusName = optional($asset->status)->name;
                                        $statusColor = optional($asset->status)->color;
                                    @endphp
                                    <x-ui.badge :color="$statusColor" variant="light" size="sm" :featherIcon="optional($asset->status)->icon">{{ $statusName }}</x-ui.badge>
                                    @if($asset->is_components_stripped)
                                        <div class="mt-1">
                                            <x-ui.badge color="warning" variant="light" size="sm" featherIcon="cpu">Components Stripped</x-ui.badge>
                                        </div>
                                    @endif
                                    @if(in_array($statusName, ['Sold', 'For Sale']))
                                        <div class="mt-1 flex flex-col text-[11px] text-gray-500 dark:text-gray-400">
                                            @if($asset->sale_price)
                                                <span class="font-medium text-emerald-600 dark:text-emerald-400">Sale: Rp {{ number_format($asset->sale_price, 0, ',', '.') }}</span>
                                            @endif
                                            @if($asset->buyer_name)
                                                <span>Buyer: {{ $asset->buyer_name }}</span>
                                            @endif
                                            @if($asset->sale_date)
                                                <span>Date: {{ $asset->sale_date->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        @if($asset->depreciation)
                                            <span class="font-medium {{ $asset->bookValue == 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                                Rp {{ number_format($asset->bookValue, 0, ',', '.') }}
                                            </span>
                                            @if($asset->bookValue == 0)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-red-600 dark:text-red-400">
                                                    Fully Depreciated
                                                </span>
                                            @else
                                                <span class="text-[10px] text-gray-400">
                                                    {{ $asset->depreciation->name }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">No Depr. Profile</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($asset->isCurrentlyCheckedOut())
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-800 dark:text-gray-200">
                                                {{ $asset->currentCheckout->checkedOutToUser->name ?? '-' }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                                {{ $asset->currentCheckout->checkedOutToUser->department->name ?? '-' }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex flex-col">
                                            <span class="text-gray-400 italic">Not Assigned</span>
                                            @if($asset->lastCheckout && $asset->lastCheckout->checkedOutToUser)
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                    Prev: {{ $asset->lastCheckout->checkedOutToUser->name }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($ageYears !== null)
                                        <span class="font-semibold {{ $ageYears >= 4 ? 'text-red-600 dark:text-red-400' : ($ageYears >= 2 ? 'text-orange-600 dark:text-orange-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                            {{ $ageYears }}y old {{ $ageYears >= 4 ? '(Review)' : '' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($asset->warranty_expiry_date)
                                        @if($asset->warranty_expiry_date->isPast())
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                                <i data-lucide="shield-alert" class="w-3 h-3 text-red-600 dark:text-red-400"></i>
                                                Warranty Expired
                                            </span>
                                        @elseif(($daysLeft = (int) ceil(now()->diffInDays($asset->warranty_expiry_date, false))) <= 30)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-medium text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">
                                                <i data-lucide="shield-off" class="w-3 h-3 text-orange-600 dark:text-orange-400"></i>
                                                Warranty Expiring in {{ max(0, $daysLeft) }}d
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                <i data-lucide="shield-check" class="w-3 h-3 text-emerald-600 dark:text-emerald-400"></i>
                                                Active ({{ $asset->warranty_expiry_date->format('d/m/Y') }})
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">-</span>
                                    @endif
                                </td>
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

                                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                            <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this asset?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    Delete Asset
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="monitor" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No assets found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Try adjusting your search or add a new asset to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Per page:</span>
                    <select onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();"
                        class="h-8 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
</div>
@endsection

@push('scripts')

@endpush
