@extends('layouts.app')

@section('content')
    @php
        $yearsOld = null;
        $ageDisplay = '-';
        $isAged = false;
        if ($asset->release_date) {
            $yearsOld = (int) $asset->release_date->diffInYears(now());
            $monthsOld = (int) $asset->release_date->diffInMonths(now());
            $daysOld = (int) $asset->release_date->diffInDays(now());

            $isAged = $yearsOld >= 4;

            if ($yearsOld >= 1) {
                $ageDisplay = $yearsOld . ' ' . Str::plural('year', $yearsOld) . ' old';
            } elseif ($monthsOld >= 1) {
                $ageDisplay = $monthsOld . ' ' . Str::plural('month', $monthsOld) . ' old';
            } else {
                $ageDisplay = $daysOld . ' ' . Str::plural('day', $daysOld) . ' old';
            }
        }
    @endphp

    <div class="space-y-6" x-data="{ activeTab: 'details' }">
        <x-common.page-breadcrumb pageTitle="Asset Details" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Detailed specification, assignment history, maintenance, and support tickets for asset {{ $asset->asset_tag }}.
        </p>

        @if($isAged)
            <div class="flex items-center gap-3 rounded-2xl border border-error-200 bg-error-50 p-4 text-error-800 dark:border-error-900/30 dark:bg-error-500/10 dark:text-error-400">
                <i data-feather="alert-triangle" class="h-5 w-5 shrink-0"></i>
                <div class="text-sm">
                    <strong>Aging Alert:</strong> This asset is {{ $ageDisplay }} (Released on {{ $asset->release_date->format('d M Y') }}). It has reached or exceeded its 4-year lifecycle since release and may require replacement or retirement.
                </div>
            </div>
        @endif

        <!-- Header Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <i data-feather="monitor" class="h-8 w-8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $asset->asset_tag }}</h2>
                            @php
                                $statusName = optional($asset->status)->name;
                                $color = optional($asset->status)->color ?? 'gray';
                            @endphp
                            <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="optional($asset->status)->icon">{{ $statusName }}</x-ui.badge>
                            @if($yearsOld !== null)
                                <x-ui.badge :color="$isAged ? 'error' : 'gray'" variant="light" size="sm">
                                    {{ $ageDisplay }}
                                </x-ui.badge>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $asset->device_name }} • {{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model }} • S/N: {{ $asset->serial_number ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('labels.print-single', $asset) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        <i data-feather="printer" class="h-4 w-4"></i>
                        Print Label Sticker
                    </a>
                    <a href="{{ route('assets.edit', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        <i data-feather="edit-3" class="h-4 w-4"></i>
                        Edit
                    </a>
                    @if($asset->isCurrentlyCheckedOut())
                        <a href="{{ route('assets.checkin', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-warning-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-warning-600">
                            <i data-feather="log-in" class="h-4 w-4"></i>
                            Check-in
                        </a>
                        <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                            <i data-feather="tool" class="h-4 w-4"></i>
                            Maintenance
                        </a>
                    @else
                        <a href="{{ route('assets.checkout', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-success-600">
                            <i data-feather="log-out" class="h-4 w-4"></i>
                            Check-out
                        </a>

                        @if($asset->status && in_array($asset->status->name, ['In Repair', 'Service']))
                            @php
                                $activeMaintenance = $asset->maintenances->where('completion_date', null)->first() ?? $asset->maintenances->first();
                            @endphp
                            @if($activeMaintenance)
                                <a href="{{ route('assets.maintenance.complete', [$asset, $activeMaintenance]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                                    <i data-feather="check-circle" class="h-4 w-4"></i>
                                    Complete Maintenance
                                </a>
                            @endif
                        @else
                            <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                                <i data-feather="tool" class="h-4 w-4"></i>
                                Maintenance
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Sidebar Info -->
            <div class="space-y-6 lg:col-span-1">
                <!-- QR Code Preview -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Asset QR Code</h3>
                    <div class="flex items-center justify-center rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(url("/asset/{$asset->id}")) }}" alt="QR Code" class="h-32 w-32 object-contain bg-white p-2 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                    </div>
                </div>

                <!-- Current Assignment -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Current Assignment</h3>
                    @if($asset->isCurrentlyCheckedOut())
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    <i data-feather="user" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $asset->currentCheckout->checkedOutToUser->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $asset->currentCheckout->checkedOutToUser->email ?? '' }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                                <div>
                                    <p class="text-[10px] font-medium uppercase text-gray-400">Department</p>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $asset->currentCheckout->checkedOutToUser->department->name ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-medium uppercase text-gray-400">Location</p>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $asset->currentCheckout->location->name ?? '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[10px] font-medium uppercase text-gray-400">Checked out at</p>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $asset->currentCheckout->checked_out_at?->format('d M Y, H:i') ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-300">
                                <i data-feather="user-minus" class="h-6 w-6"></i>
                            </div>
                            <p class="text-sm text-gray-400 italic">Not currently assigned</p>
                        </div>
                    @endif
                </div>

                <!-- Quick Specs -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">System Info</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">OS</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $asset->operatingSystem->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Serial Number</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $asset->serial_number ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Warranty Expiry</span>
                            <span class="text-sm font-medium {{ $asset->warranty_expiry_date?->isPast() ? 'text-error-500' : 'text-gray-800 dark:text-white/90' }}">
                                {{ $asset->warranty_expiry_date?->format('d M Y') ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Depreciation & Value Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Depreciation & Value</h3>
                    @if($asset->depreciation && $asset->purchase_cost && $asset->release_date)
                        @php
                            $totalMonths = $asset->depreciation->months;
                            $elapsedMonths = (int) $asset->release_date->diffInMonths(now());
                            $percent = min(100, (int) round(($elapsedMonths / $totalMonths) * 100));
                            $remainingMonths = max(0, $totalMonths - $elapsedMonths);
                        @endphp
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Profile</span>
                                <span class="font-medium text-gray-800 dark:text-white/90">{{ $asset->depreciation->name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Release Date</span>
                                <span class="font-medium text-gray-800 dark:text-white/90">{{ $asset->release_date->format('d M Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Original Cost</span>
                                <span class="font-medium text-gray-800 dark:text-white/90">Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Book Value</span>
                                <span class="font-bold {{ $asset->bookValue == 0 ? 'text-error-500' : 'text-brand-500' }}">Rp {{ number_format($asset->bookValue, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 mb-1">
                                    <span>{{ $percent }}% depreciated</span>
                                    <span>{{ $remainingMonths > 0 ? $remainingMonths . ' months left' : 'Fully Depreciated' }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                    <div class="h-full {{ $percent >= 100 ? 'bg-error-500' : 'bg-brand-500' }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-xs text-gray-400 italic">No depreciation profile assigned or missing release date/cost.</div>
                    @endif
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
                        <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5">
                            <button @click="activeTab = 'details'"
                                :class="activeTab === 'details' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                                <i data-feather="file-text" class="w-4 h-4"></i>
                                Details
                            </button>
                            <button @click="activeTab = 'history'"
                                :class="activeTab === 'history' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                                <i data-feather="activity" class="w-4 h-4"></i>
                                Movement History
                            </button>
                            <button @click="activeTab = 'maintenance'"
                                :class="activeTab === 'maintenance' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                                <i data-feather="tool" class="w-4 h-4"></i>
                                Maintenance History
                            </button>
                            <button @click="activeTab = 'tickets'"
                                :class="activeTab === 'tickets' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                                <i data-feather="life-buoy" class="w-4 h-4"></i>
                                Support Tickets
                                @if($asset->tickets->count() > 0)
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ $asset->tickets->count() }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    </div>

                    <!-- Tabs Content -->
                    <div class="p-6">
                        <!-- Details Tab -->
                        <div x-show="activeTab === 'details'" class="space-y-8" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <!-- Product Info -->
                            <div>
                                <h4 class="mb-5 flex items-center gap-2 text-base font-medium text-gray-800 border-b border-gray-100 pb-3 dark:border-gray-800 dark:text-white/90">
                                    <i data-feather="package" class="h-5 w-5 text-brand-500"></i>
                                    Product Information
                                </h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Brand & Model</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Category</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ optional($asset->product->category)->name ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Serial Number</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->serial_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Product Number</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->product_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20 sm:col-span-2">
                                        <p class="text-xs font-medium uppercase text-gray-400">Vendor</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vendor->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Technical Specs -->
                            <div>
                                <h4 class="mb-5 flex items-center gap-2 text-base font-medium text-gray-800 border-b border-gray-100 pb-3 dark:border-gray-800 dark:text-white/90">
                                    <i data-feather="cpu" class="h-5 w-5 text-brand-500"></i>
                                    Technical Specifications
                                </h4>
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                    <p class="whitespace-pre-line text-sm text-gray-600 dark:text-gray-400">{{ $asset->specification ?? 'No specifications provided.' }}</p>
                                </div>
                            </div>

                            <!-- Financial Data -->
                            <div>
                                <h4 class="mb-5 flex items-center gap-2 text-base font-medium text-gray-800 border-b border-gray-100 pb-3 dark:border-gray-800 dark:text-white/90">
                                    <i data-feather="dollar-sign" class="h-5 w-5 text-brand-500"></i>
                                    Financial Data
                                </h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Purchase Date</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Purchase Cost</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Rp {{ number_format($asset->purchase_cost, 2) }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Invoice</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->invoice_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Asset Aging</p>
                                        <p class="mt-1 text-sm font-semibold {{ $isAged ? 'text-error-500' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $ageDisplay }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            @if($asset->notes)
                                <div>
                                    <h4 class="mb-5 flex items-center gap-2 text-base font-medium text-gray-800 border-b border-gray-100 pb-3 dark:border-gray-800 dark:text-white/90">
                                        <i data-feather="file-text" class="h-5 w-5 text-brand-500"></i>
                                        Notes
                                    </h4>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20 text-sm text-gray-600 dark:text-gray-400 italic">
                                        {{ $asset->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- History Tab -->
                        <div x-show="activeTab === 'history'" class="space-y-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="border-b border-gray-100 dark:border-gray-800">
                                        <tr>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">User / Dept</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date Out</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date In</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Condition</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6 text-right">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">BAST</p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->checkoutHistory as $history)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $history->checkedOutToUser->name ?? '-' }}</span>
                                                        <span class="text-[10px] text-gray-400">{{ $history->department->name ?? '-' }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $history->checked_out_at?->format('d M Y') ?? '-' }}</span>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    @if($history->checked_in_at)
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $history->checked_in_at?->format('d M Y') ?? '-' }}</span>
                                                    @else
                                                        <x-ui.badge color="primary" variant="light" size="sm">Current</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    @if($history->checkinStatus)
                                                        <x-ui.badge :color="$history->checkinStatus->color ?? 'gray'" variant="light" size="sm">
                                                            {{ $history->checkinStatus->name }}
                                                        </x-ui.badge>
                                                    @elseif(!$history->checked_in_at)
                                                        <x-ui.badge color="primary" variant="light" size="sm">Deployed</x-ui.badge>
                                                    @else
                                                        <span class="text-xs text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-[150px]" title="{{ $history->condition_report ?? '-' }}">
                                                        {{ $history->condition_report ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6 text-right">
                                                    <a href="{{ route('assets.bast', [$asset, $history]) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-500 hover:text-brand-600 transition-colors">
                                                        <i data-feather="download-cloud" class="h-4 w-4"></i>
                                                        BAST
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center justify-center space-y-2">
                                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                            <i data-feather="clock" class="w-6 h-6"></i>
                                                        </div>
                                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No movement history found</p>
                                                        <p class="text-xs text-gray-400 dark:text-gray-500">This asset has not been assigned or checked out yet.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div x-show="activeTab === 'maintenance'" class="space-y-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="border-b border-gray-100 dark:border-gray-800">
                                        <tr>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Provider</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Start Date</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Completion Date</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Cost</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Notes</p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->maintenances as $maint)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-5 py-4 sm:px-6">
                                                    <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $maint->service_provider ?? '-' }}</span>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $maint->start_date?->format('d M Y') ?? '-' }}</span>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    @if($maint->completion_date)
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $maint->completion_date?->format('d M Y') ?? '-' }}</span>
                                                    @else
                                                        <x-ui.badge color="warning" variant="light" size="sm">In Repair</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                                        {{ $maint->cost ? 'Rp ' . number_format($maint->cost, 2) : '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-[150px]" title="{{ $maint->notes ?? '-' }}">
                                                        {{ $maint->notes ?? '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center justify-center space-y-2">
                                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                            <i data-feather="tool" class="h-6 w-6"></i>
                                                        </div>
                                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No maintenance history found</p>
                                                    </div>
                                                </td>
                                            </tr>
                                         @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tickets History Tab -->
                        <div x-show="activeTab === 'tickets'" class="space-y-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="border-b border-gray-100 dark:border-gray-800">
                                        <tr>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Ticket ID</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requester</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Problem Summary</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Priority</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6 text-right">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Action</p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->tickets as $t)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-5 py-4 sm:px-6 font-mono font-medium text-brand-600 dark:text-brand-400">
                                                    <a href="{{ route('tickets.show', $t) }}" class="hover:underline">#{{ $t->request_id }}</a>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6 text-sm font-medium text-gray-800 dark:text-white/90">
                                                    {{ $t->requester_name ?? '-' }}
                                                </td>
                                                <td class="px-5 py-4 sm:px-6 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $t->problem_description }}">
                                                    {{ Str::limit($t->problem_description, 50) }}
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <x-ui.badge color="gray" variant="light" size="sm">
                                                        {{ $t->priority?->name ?? 'Normal' }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <x-ui.badge :color="$t->status?->color ?? 'primary'" variant="light" size="sm">
                                                        {{ $t->status?->name ?? 'Open' }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6 text-right">
                                                    <div class="flex items-center justify-end gap-3 text-gray-500">
                                                        <a href="{{ route('tickets.show', $t) }}" class="transition-colors hover:text-brand-500" title="View Ticket">
                                                            <i data-feather="eye" class="w-5 h-5"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center justify-center space-y-2">
                                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                            <i data-feather="life-buoy" class="h-6 w-6"></i>
                                                        </div>
                                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No support tickets reported for this asset</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
@vite('resources/js/asset-labels.js')
@endpush
