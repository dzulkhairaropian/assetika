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

    <div class="space-y-6" x-data="{ activeTab: 'details', showQrModal: false }">
        <x-common.page-breadcrumb pageTitle="Asset Details" />

        @if($isAged)
            <div class="flex items-center gap-3 rounded-2xl border border-error-200 bg-error-50 p-4 text-error-800 dark:border-error-900/30 dark:bg-error-500/10 dark:text-error-400">
                <i data-lucide="alert-triangle" class="h-5 w-5 shrink-0"></i>
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
                        <i data-lucide="laptop" class="h-8 w-8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $asset->asset_tag }}</h2>
                            @php
                                $statusName = optional($asset->status)->name;
                                $color = match($statusName) {
                                    'Ready', 'Ready to Deploy', 'Available' => 'success',
                                    'Deployed', 'In Use', 'Borrowed' => 'primary',
                                    'Broken' => 'error',
                                    'In Repair', 'Service' => 'warning',
                                    'Archived', 'Retired' => 'gray',
                                    default => 'gray'
                                };
                                $icon = match($statusName) {
                                    'Ready', 'Ready to Deploy', 'Available' => 'circle-check',
                                    'Deployed', 'In Use', 'Borrowed' => 'user',
                                    'Broken' => 'x-circle',
                                    'In Repair', 'Service' => 'wrench',
                                    'Archived', 'Retired' => 'archive',
                                    'Draft' => 'file-text',
                                    default => 'help-circle'
                                };
                                $iconHtml = new \Illuminate\Support\HtmlString("<i data-lucide='{$icon}' class='w-3 h-3'></i>");
                            @endphp
                            <x-ui.badge :color="$color" variant="light" size="sm" :startIcon="$iconHtml">{{ $statusName }}</x-ui.badge>
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
                    <button @click="showQrModal = true" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        <i data-lucide="qr-code" class="h-4 w-4"></i>
                        QR Label
                    </button>
                    <a href="{{ route('assets.edit', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        <i data-lucide="edit-3" class="h-4 w-4"></i>
                        Edit
                    </a>
                    @if($asset->isCurrentlyCheckedOut())
                        <a href="{{ route('assets.checkin', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-warning-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-warning-600">
                            <i data-lucide="log-in" class="h-4 w-4"></i>
                            Check-in
                        </a>
                        <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                            <i data-lucide="wrench" class="h-4 w-4"></i>
                            Maintenance
                        </a>
                    @else
                        <a href="{{ route('assets.checkout', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-success-600">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Check-out
                        </a>

                        @if($asset->status && in_array($asset->status->name, ['In Repair', 'Service']))
                            @php
                                $activeMaintenance = $asset->maintenances->where('completion_date', null)->first() ?? $asset->maintenances->first();
                            @endphp
                            @if($activeMaintenance)
                                <a href="{{ route('assets.maintenance.complete', [$asset, $activeMaintenance]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                                    <i data-lucide="circle-check" class="h-4 w-4"></i>
                                    Complete Maintenance
                                </a>
                            @endif
                        @else
                            <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                                <i data-lucide="wrench" class="h-4 w-4"></i>
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
                <!-- Current Assignment -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Current Assignment</h3>
                    @if($asset->isCurrentlyCheckedOut())
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    <i data-lucide="user" class="h-5 w-5"></i>
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
                                <i data-lucide="user-minus" class="h-6 w-6"></i>
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
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <!-- Tabs Navigation -->
                    <div class="flex border-b border-gray-100 px-5 dark:border-gray-800">
                        <button @click="activeTab = 'details'"
                            :class="activeTab === 'details' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-4 text-sm font-medium transition-all">
                            Details
                        </button>
                        <button @click="activeTab = 'history'"
                            :class="activeTab === 'history' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-4 text-sm font-medium transition-all">
                            Movement History
                        </button>
                        <button @click="activeTab = 'maintenance'"
                            :class="activeTab === 'maintenance' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-4 text-sm font-medium transition-all">
                            Maintenance History
                        </button>
                    </div>

                    <!-- Tabs Content -->
                    <div class="p-6">
                        <!-- Details Tab -->
                        <div x-show="activeTab === 'details'" class="space-y-8" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <!-- Product Info -->
                            <div>
                                <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-white/90">
                                    <i data-lucide="package" class="h-5 w-5 text-brand-500"></i>
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
                                <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-white/90">
                                    <i data-lucide="cpu" class="h-5 w-5 text-brand-500"></i>
                                    Technical Specifications
                                </h4>
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                    <p class="whitespace-pre-line text-sm text-gray-600 dark:text-gray-400">{{ $asset->specification ?? 'No specifications provided.' }}</p>
                                </div>
                            </div>

                            <!-- Financial Data -->
                            <div>
                                <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-white/90">
                                    <i data-lucide="dollar-sign" class="h-5 w-5 text-brand-500"></i>
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
                                        <p class="mt-1 text-sm font-semibold {{ $isAged ? 'text-error-500 font-bold animate-pulse' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $ageDisplay }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            @if($asset->notes)
                                <div>
                                    <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-white/90">
                                        <i data-lucide="file-text" class="h-5 w-5 text-brand-500"></i>
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
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">User / Dept</th>
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Date Out</th>
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Date In</th>
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Condition</th>
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">BAST</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                                        @forelse($asset->checkoutHistory as $history)
                                            <tr>
                                                <td class="py-4">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $history->checkedOutToUser->name ?? '-' }}</span>
                                                        <span class="text-[10px] text-gray-400">{{ $history->department->name ?? '-' }}</span>
                                                    </div>
                                                </td>
                                                <td class="py-4">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $history->checked_out_at?->format('d M Y') ?? '-' }}</span>
                                                </td>
                                                <td class="py-4">
                                                    @if($history->checked_in_at)
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $history->checked_in_at?->format('d M Y') ?? '-' }}</span>
                                                    @else
                                                        <x-ui.badge color="primary" variant="light" size="sm">Current</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="py-4">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-[150px]" title="{{ $history->condition_report ?? '-' }}">
                                                        {{ $history->condition_report ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="py-4">
                                                    <a href="{{ route('assets.bast', [$asset, $history]) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-500 hover:text-brand-600 transition-colors">
                                                        <i data-lucide="file-down" class="h-4 w-4"></i>
                                                        BAST
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center justify-center space-y-2">
                                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
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
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Provider</th>
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Start Date</th>
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Completion Date</th>
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Cost</th>
                                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                                        @forelse($asset->maintenances as $maint)
                                            <tr>
                                                <td class="py-4">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $maint->service_provider ?? '-' }}</span>
                                                </td>
                                                <td class="py-4">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $maint->start_date?->format('d M Y') ?? '-' }}</span>
                                                </td>
                                                <td class="py-4">
                                                    @if($maint->completion_date)
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $maint->completion_date?->format('d M Y') ?? '-' }}</span>
                                                    @else
                                                        <x-ui.badge color="warning" variant="light" size="sm">In Repair</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="py-4">
                                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                        {{ $maint->cost ? 'Rp ' . number_format($maint->cost, 2) : '-' }}
                                                    </span>
                                                </td>
                                                <td class="py-4">
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
                                                            <i data-lucide="wrench" class="h-6 w-6"></i>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Label Modal -->
        <div x-show="showQrModal"
            class="fixed inset-0 z-[999] flex items-center justify-center overflow-y-auto"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <!-- Backdrop -->
            <div @click="showQrModal = false"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>

            <!-- Modal Content -->
            <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900 transition-all z-10 m-4">
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">Asset QR Label</h3>
                    <button @click="showQrModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <!-- Printable Area -->
                <div class="p-1">
                    <div id="printableLabel" style="width: 330px; max-width: 330px; box-sizing: border-box;" class="print-label-card mx-auto flex items-center gap-5 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02] text-left">
                        <!-- Left: QR Code -->
                        <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm border border-gray-100 dark:border-gray-800">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(url("/asset/{$asset->id}")) }}"
                                alt="Asset QR Code"
                                class="h-full w-full object-contain">
                        </div>

                        <!-- Right: Asset Info -->
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Asset Tag</span>
                                <p class="text-sm font-bold text-gray-800 dark:text-white/90 truncate">{{ $asset->asset_tag }}</p>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Serial Number</span>
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate">{{ $asset->serial_number ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Company</span>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 truncate">{{ optional(optional($asset->department)->company)->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copies Input -->
                <div class="mt-4 flex items-center justify-between gap-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Copies to print:</label>
                        <input type="number" id="detailLabelCopies" value="1" min="1" max="50"
                            class="w-16 rounded-lg border border-gray-200 px-2 py-1 text-center text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div class="flex gap-2">
                        <button @click="showQrModal = false"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800">
                            Close
                        </button>
                        <button onclick="printLabel()"
                            class="rounded-lg bg-brand-500 px-5 py-2.5 text-xs font-medium text-white hover:bg-brand-600 transition">
                            Print Label
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printLabel() {
            const copies = parseInt(document.getElementById('detailLabelCopies').value) || 1;
            const printContent = document.getElementById('printableLabel').outerHTML;
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Print Asset Label</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; background: #fff; margin: 0; }');
            printWindow.document.write('.label-grid { display: grid; grid-template-columns: repeat(auto-fill, 330px); gap: 12px; justify-content: start; }');
            printWindow.document.write('.print-label-card { display: flex; align-items: center; gap: 20px; padding: 20px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb; width: 330px !important; max-width: 330px !important; box-sizing: border-box; page-break-inside: avoid; }');
            printWindow.document.write('.h-28 { height: 112px; } .w-28 { width: 112px; }');
            printWindow.document.write('.shrink-0 { flex-shrink: 0; }');
            printWindow.document.write('.bg-white { background-color: white; padding: 8px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; }');
            printWindow.document.write('.space-y-1\\.5 > * + * { margin-top: 6px; }');
            printWindow.document.write('.text-\\[9px\\] { font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 700; margin-bottom: 2px; }');
            printWindow.document.write('.text-sm { font-size: 14px; font-weight: 700; color: #1f2937; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }');
            printWindow.document.write('.text-xs { font-size: 12px; font-weight: 600; color: #4b5563; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }');
            printWindow.document.write('.text-xs.font-medium { font-weight: 500; color: #6b7280; }');
            printWindow.document.write('img { max-width: 100%; height: auto; display: block; }');
            printWindow.document.write('@media print { body { padding: 0; } .print-label-card { page-break-inside: avoid; width: 330px !important; max-width: 330px !important; } }');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write('<div class="label-grid">');

            for (let i = 0; i < copies; i++) {
                printWindow.document.write(printContent);
            }

            printWindow.document.write('</div></body></html>');
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
@endsection
