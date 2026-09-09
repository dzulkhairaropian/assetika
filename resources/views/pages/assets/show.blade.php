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

    <div class="space-y-4" x-data="{ activeTab: 'details' }">
        <x-common.page-breadcrumb pageTitle="Asset Details" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Detailed specification, assignment history, maintenance, and support tickets for asset {{ $asset->asset_tag }}.
        </p>

        @if($isAged)
            <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-red-800 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400">
                <i data-feather="alert-triangle" class="h-4 w-4 shrink-0"></i>
                <div class="text-xs">
                    <strong>Aging Alert:</strong> This asset is {{ $ageDisplay }} (Released on {{ $asset->release_date->format('d M Y') }}). It has reached or exceeded its 4-year lifecycle since release and may require replacement or retirement.
                </div>
            </div>
        @endif

        <!-- Header Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <i data-feather="monitor" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $asset->asset_tag }}</h2>
                            @php
                                $statusName = optional($asset->status)->name;
                                $color = optional($asset->status)->color ?? 'gray';
                            @endphp
                            <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="optional($asset->status)->icon">{{ $statusName }}</x-ui.badge>
                            @if($yearsOld !== null)
                                <x-ui.badge :color="$isAged ? 'danger' : 'secondary'" variant="light" size="sm">
                                    {{ $ageDisplay }}
                                </x-ui.badge>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $asset->device_name }} • {{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model }} • S/N: {{ $asset->serial_number ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('assets.print-label', $asset) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="printer" class="h-3.5 w-3.5"></i>
                        Print Sticker
                    </a>
                    <a href="{{ route('assets.edit', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    @if($asset->isCurrentlyCheckedOut())
                        <a href="{{ route('assets.checkin', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-orange-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-orange-600 transition">
                            <i data-feather="log-in" class="h-3.5 w-3.5"></i>
                            Check-in
                        </a>
                        <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 transition">
                            <i data-feather="tool" class="h-3.5 w-3.5"></i>
                            Maintenance
                        </a>
                    @else
                        <a href="{{ route('assets.checkout', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-green-700 transition">
                            <i data-feather="log-out" class="h-3.5 w-3.5"></i>
                            Check-out
                        </a>

                        @if($asset->status && in_array($asset->status->name, ['In Repair', 'Service']))
                            @php
                                $activeMaintenance = $asset->maintenances->where('completion_date', null)->first() ?? $asset->maintenances->first();
                            @endphp
                            @if($activeMaintenance)
                                <a href="{{ route('assets.maintenance.complete', [$asset, $activeMaintenance]) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                                    <i data-feather="check-circle" class="h-3.5 w-3.5"></i>
                                    Complete Maintenance
                                </a>
                            @endif
                        @else
                            <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 transition">
                                <i data-feather="tool" class="h-3.5 w-3.5"></i>
                                Maintenance
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Sidebar Info -->
            <div class="space-y-4 lg:col-span-1">
                <!-- QR Code Preview -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Asset QR Code</h3>
                    <div class="flex items-center justify-center rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(url("/scan/asset/{$asset->id}")) }}" alt="QR Code" class="h-28 w-28 object-contain bg-white p-2 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                    </div>
                </div>

                <!-- Current Assignment -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Current Assignment</h3>
                    @if($asset->isCurrentlyCheckedOut())
                        <div class="space-y-3">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    <i data-feather="user" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $asset->currentCheckout->checkedOutToUser->name ?? '-' }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $asset->currentCheckout->checkedOutToUser->email ?? '' }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <div>
                                    <p class="text-[10px] font-medium uppercase text-gray-400">Department</p>
                                    <p class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $asset->currentCheckout->checkedOutToUser->department->name ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-medium uppercase text-gray-400">Location</p>
                                    <p class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $asset->currentCheckout->location->name ?? '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[10px] font-medium uppercase text-gray-400">Checked out at</p>
                                    <p class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $asset->currentCheckout->checked_out_at?->format('d M Y, H:i') ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-5 text-center">
                            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                <i data-feather="user-minus" class="h-5 w-5"></i>
                            </div>
                            <p class="text-xs text-gray-400 italic">Not currently assigned</p>
                        </div>
                    @endif
                </div>

                <!-- Quick Specs -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">System Info</h3>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">OS</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $asset->operatingSystem->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Serial Number</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $asset->serial_number ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Warranty Expiry</span>
                            <span class="font-medium {{ $asset->warranty_expiry_date?->isPast() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                {{ $asset->warranty_expiry_date?->format('d M Y') ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Depreciation & Value Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Depreciation & Value</h3>
                    @if($asset->depreciation && $asset->purchase_cost && $asset->release_date)
                        @php
                            $totalMonths = $asset->depreciation->months;
                            $elapsedMonths = (int) $asset->release_date->diffInMonths(now());
                            $percent = min(100, (int) round(($elapsedMonths / $totalMonths) * 100));
                            $remainingMonths = max(0, $totalMonths - $elapsedMonths);
                        @endphp
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Profile</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $asset->depreciation->name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Release Date</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $asset->release_date->format('d M Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Original Cost</span>
                                <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Book Value</span>
                                <span class="font-bold {{ $asset->bookValue == 0 ? 'text-red-600 dark:text-red-400' : 'text-brand-600 dark:text-brand-400' }}">Rp {{ number_format($asset->bookValue, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1">
                                    <span>{{ $percent }}% depreciated</span>
                                    <span>{{ $remainingMonths > 0 ? $remainingMonths . ' mos left' : 'Fully Depreciated' }}</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                    <div class="h-full {{ $percent >= 100 ? 'bg-red-500' : 'bg-brand-500' }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-xs text-gray-400 italic">No depreciation profile assigned.</div>
                    @endif
                </div>

                @if(in_array(optional($asset->status)->name, ['Sold', 'For Sale']) || $asset->sale_price)
                <!-- Sale Details Card -->
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/30 p-4 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                        <i data-feather="dollar-sign" class="w-3.5 h-3.5"></i>
                        Sale Information
                    </h3>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Sale Price</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($asset->sale_price ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Buyer</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $asset->buyer_name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Sale Date</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $asset->sale_date ? $asset->sale_date->format('d M Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                        <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5">
                            <button @click="activeTab = 'details'"
                                :class="activeTab === 'details' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-feather="file-text" class="w-3.5 h-3.5"></i>
                                Details
                            </button>
                            <button @click="activeTab = 'history'"
                                :class="activeTab === 'history' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-feather="activity" class="w-3.5 h-3.5"></i>
                                Movement History
                            </button>
                            <button @click="activeTab = 'audit_logs'"
                                :class="activeTab === 'audit_logs' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-feather="list" class="w-3.5 h-3.5"></i>
                                Audit Logs
                            </button>
                            <button @click="activeTab = 'maintenance'"
                                :class="activeTab === 'maintenance' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-feather="tool" class="w-3.5 h-3.5"></i>
                                Maintenance History
                            </button>
                            <button @click="activeTab = 'tickets'"
                                :class="activeTab === 'tickets' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-feather="life-buoy" class="w-3.5 h-3.5"></i>
                                Support Tickets
                                @if($asset->tickets->count() > 0)
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ $asset->tickets->count() }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    </div>

                    <!-- Tabs Content -->
                    <div class="p-4 sm:p-5">
                        <!-- Details Tab -->
                        <div x-show="activeTab === 'details'" class="space-y-6" x-transition>
                            <!-- Product Info -->
                            <div>
                                <h4 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2.5 dark:border-gray-800 dark:text-white">
                                    <i data-feather="package" class="h-4 w-4 text-brand-500"></i>
                                    Product Information
                                </h4>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Brand & Model</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-gray-200">{{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Category</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-gray-200">{{ optional($asset->product->category)->name ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Serial Number</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $asset->serial_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Product Number</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $asset->product_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950 sm:col-span-2">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Vendor</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $asset->vendor->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Technical Specs -->
                            <div>
                                <h4 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2.5 dark:border-gray-800 dark:text-white">
                                    <i data-feather="cpu" class="h-4 w-4 text-brand-500"></i>
                                    Technical Specifications
                                </h4>
                                <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                    <p class="whitespace-pre-line text-xs text-gray-700 dark:text-gray-300">{{ $asset->specification ?? 'No specifications provided.' }}</p>
                                </div>
                            </div>

                            <!-- Financial Data -->
                            <div>
                                <h4 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2.5 dark:border-gray-800 dark:text-white">
                                    <i data-feather="dollar-sign" class="h-4 w-4 text-brand-500"></i>
                                    Financial Data
                                </h4>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Purchase Date</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Purchase Cost</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Invoice</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $asset->invoice_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Asset Aging</p>
                                        <p class="mt-0.5 text-xs font-semibold {{ $isAged ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200' }}">
                                            {{ $ageDisplay }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            @if($asset->notes)
                                <div>
                                    <h4 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2.5 dark:border-gray-800 dark:text-white">
                                        <i data-feather="file-text" class="h-4 w-4 text-brand-500"></i>
                                        Notes
                                    </h4>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 italic">
                                        {{ $asset->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- History Tab -->
                        <div x-show="activeTab === 'history'" class="space-y-4" x-transition>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                                        <tr>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">User / Dept</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Date Out</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Date In</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Status</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Condition</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500 text-end">BAST</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->checkoutHistory as $history)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-4 py-2.5">
                                                    <div class="flex flex-col">
                                                        <span class="font-medium text-gray-900 dark:text-white">{{ $history->checkedOutToUser->name ?? '-' }}</span>
                                                        <span class="text-[10px] text-gray-400">{{ $history->department->name ?? '-' }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                                    {{ $history->checked_out_at?->format('d M Y') ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    @if($history->checked_in_at)
                                                        <span class="text-gray-600 dark:text-gray-400">{{ $history->checked_in_at?->format('d M Y') ?? '-' }}</span>
                                                    @else
                                                        <x-ui.badge color="primary" variant="light" size="sm">Current</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    @if($history->checkinStatus)
                                                        <x-ui.badge :color="$history->checkinStatus->color ?? 'gray'" variant="light" size="sm">
                                                            {{ $history->checkinStatus->name }}
                                                        </x-ui.badge>
                                                    @elseif(!$history->checked_in_at)
                                                        <x-ui.badge color="primary" variant="light" size="sm">Deployed</x-ui.badge>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 truncate max-w-[150px]" title="{{ $history->condition_report ?? '-' }}">
                                                    {{ $history->condition_report ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-end">
                                                    <a href="{{ route('assets.bast', [$asset, $history]) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:text-brand-700 transition">
                                                        <i data-feather="download-cloud" class="h-3.5 w-3.5"></i>
                                                        BAST
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-400">
                                                    No movement history found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div x-show="activeTab === 'maintenance'" class="space-y-4" x-transition>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                                        <tr>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Provider</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Start Date</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Completion Date</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Cost</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->maintenances as $maint)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                                    {{ $maint->service_provider ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                                    {{ $maint->start_date?->format('d M Y') ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    @if($maint->completion_date)
                                                        <span class="text-gray-600 dark:text-gray-400">{{ $maint->completion_date?->format('d M Y') ?? '-' }}</span>
                                                    @else
                                                        <x-ui.badge color="warning" variant="light" size="sm">In Repair</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white">
                                                    {{ $maint->cost ? 'Rp ' . number_format($maint->cost, 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 truncate max-w-[150px]" title="{{ $maint->notes ?? '-' }}">
                                                    {{ $maint->notes ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-6 text-center text-xs text-gray-400">
                                                    No maintenance history found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tickets History Tab -->
                        <div x-show="activeTab === 'tickets'" class="space-y-4" x-transition>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                                        <tr>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Ticket ID</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Requester</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Problem Summary</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Priority</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Status</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->tickets as $t)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-4 py-2.5 font-mono font-medium text-brand-600 dark:text-brand-400">
                                                    <a href="{{ route('tickets.show', $t) }}" class="hover:underline">#{{ $t->request_id }}</a>
                                                </td>
                                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                                    {{ $t->requester_name ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $t->problem_description }}">
                                                    {{ Str::limit($t->problem_description, 50) }}
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <x-ui.badge :color="$t->priority?->color ?? 'secondary'" variant="light" size="sm" :featherIcon="$t->priority?->icon ?: 'alert-circle'">
                                                        {{ $t->priority?->name ?? 'Normal' }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <x-ui.badge :color="$t->status?->color ?? 'primary'" variant="light" size="sm" :featherIcon="$t->status?->icon ?: 'disc'">
                                                        {{ $t->status?->name ?? 'Open' }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-4 py-2.5 text-end">
                                                    <a href="{{ route('tickets.show', $t) }}" class="text-gray-500 hover:text-brand-600 transition" title="View Ticket">
                                                        <i data-feather="eye" class="w-4 h-4 inline-block"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                         @empty
                                             <tr>
                                                 <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-400">
                                                     No support tickets reported for this asset
                                                 </td>
                                             </tr>
                                         @endforelse
                                     </tbody>
                                 </table>
                             </div>
                         </div>

                         <!-- Audit Logs Tab -->
                         <div x-show="activeTab === 'audit_logs'" class="space-y-4" x-transition>
                             <div class="overflow-x-auto">
                                 <table class="w-full text-left text-xs">
                                     <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                                         <tr>
                                             <th class="px-4 py-2.5 font-medium text-gray-500">Date & Time</th>
                                             <th class="px-4 py-2.5 font-medium text-gray-500">User</th>
                                             <th class="px-4 py-2.5 font-medium text-gray-500">Event</th>
                                             <th class="px-4 py-2.5 font-medium text-gray-500">Description</th>
                                         </tr>
                                     </thead>
                                     <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                         @forelse($asset->activityLogs as $log)
                                             <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                 <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                                     {{ $log->created_at->format('d M Y, H:i') }}
                                                 </td>
                                                 <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                                     {{ $log->user?->name ?? 'System' }}
                                                 </td>
                                                 <td class="px-4 py-2.5">
                                                     <x-ui.badge :color="$log->event === 'created' ? 'success' : ($log->event === 'deleted' ? 'danger' : 'info')" variant="light" size="sm">
                                                         {{ strtoupper($log->event) }}
                                                     </x-ui.badge>
                                                 </td>
                                                 <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                                     {{ $log->description }}
                                                 </td>
                                             </tr>
                                         @empty
                                             <tr>
                                                 <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">
                                                     No activity logs recorded yet
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

    </div>
@endsection

@push('scripts')
@endpush
