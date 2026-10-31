@extends('layouts.app')

@section('content')
    @php
        $startDate = $asset->release_date ?? $asset->purchase_date ?? $asset->created_at;
        $yearsOld = $startDate ? (int) $startDate->diffInYears(now()) : null;
        $ageDisplay = '-';
        if ($startDate) {
            if ($yearsOld >= 1) {
                $ageDisplay = $yearsOld . ' ' . Str::plural('year', $yearsOld) . ' old';
            } else {
                $monthsOld = (int) $startDate->diffInMonths(now());
                $ageDisplay = $monthsOld >= 1 ? $monthsOld . ' ' . Str::plural('month', $monthsOld) . ' old' : (int) $startDate->diffInDays(now()) . ' days old';
            }
        }
        $isAged = $asset->isDueForReplacement();
    @endphp

    <div class="space-y-5" x-data="{ activeTab: 'details', retireModalOpen: false }">
        <x-common.page-breadcrumb pageTitle="Asset Details" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Hardware specification, active assignment, lifecycle status, maintenance history, and linked support tickets.
        </p>

        @if($isAged)
            <div class="flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900 shadow-xs dark:border-amber-900/40 dark:bg-amber-950/20 dark:text-amber-300">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                        <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xs">
                        <p class="font-bold text-gray-900 dark:text-white">Replacement Due ({{ $asset->replacement_priority }} Priority)</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-0.5">
                            This asset is {{ $ageDisplay }} (Acquired {{ $startDate?->format('d M Y') ?? '—' }}). It has reached the target lifecycle of {{ $asset->useful_life_years }} years and is recommended for replacement.
                        </p>
                    </div>
                </div>
                <a href="{{ route('assets.replacements') }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-amber-700 transition-colors">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    View Planner
                </a>
            </div>
        @endif

        <!-- Entity Header Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 lg:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-xs">
                        <i data-lucide="monitor" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white font-mono">{{ $asset->asset_tag }}</h2>
                            @php
                                $statusName = optional($asset->status)->name;
                                $color = optional($asset->status)->color ?? 'gray';
                            @endphp
                            <x-ui.badge :color="$color" variant="light" size="sm" :lucideIcon="optional($asset->status)->icon">{{ $statusName }}</x-ui.badge>
                            @if($isAged)
                                <x-ui.badge color="warning" variant="light" size="sm" lucideIcon="refresh-cw">
                                    Replace ({{ $asset->age_years }}y)
                                </x-ui.badge>
                            @endif
                            @if($asset->is_components_stripped)
                                <x-ui.badge color="warning" variant="light" size="sm" lucideIcon="cpu">Components Stripped</x-ui.badge>
                            @endif
                            @if($asset->isOverdue())
                                <x-ui.badge color="danger" variant="solid" size="sm" lucideIcon="alert-circle">
                                    Overdue ({{ $asset->currentCheckout->overdueDays() }}d)
                                </x-ui.badge>
                            @elseif($asset->currentCheckout?->expected_return_date)
                                <x-ui.badge color="info" variant="light" size="sm" lucideIcon="calendar">
                                    Due {{ $asset->currentCheckout->expected_return_date->format('d M Y') }}
                                </x-ui.badge>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-medium">
                            <span class="text-gray-900 dark:text-white font-semibold">{{ $asset->device_name }}</span> • 
                            {{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model ?? optional($asset->product)->name }} • 
                            S/N: <span class="font-mono text-gray-700 dark:text-gray-300">{{ $asset->serial_number ?? '—' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Action Toolbar: Primary vs Secondary Actions -->
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                    {{-- Primary Operational Action --}}
                    @if($asset->isCurrentlyCheckedOut())
                        <a href="{{ route('assets.checkin', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 active:scale-[0.98] transition-colors duration-150">
                            <i data-lucide="log-in" class="h-3.5 w-3.5"></i>
                            Check-in Asset
                        </a>
                        <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-amber-700 active:scale-[0.98] transition-colors duration-150">
                            <i data-lucide="wrench" class="h-3.5 w-3.5"></i>
                            Maintenance
                        </a>
                    @else
                        <a href="{{ route('assets.checkout', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-600 active:scale-[0.98] transition-colors duration-150">
                            <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                            Check-out Asset
                        </a>

                        @if($asset->status && in_array($asset->status->name, ['In Repair', 'Service']))
                            @php
                                $activeMaintenance = $asset->maintenances->where('completion_date', null)->first() ?? $asset->maintenances->first();
                            @endphp
                            @if($activeMaintenance)
                                <a href="{{ route('assets.maintenance.complete', [$asset, $activeMaintenance]) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 active:scale-[0.98] transition-colors duration-150">
                                    <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
                                    Complete Repair
                                </a>
                            @endif
                        @else
                            <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-amber-700 active:scale-[0.98] transition-colors duration-150">
                                <i data-lucide="wrench" class="h-3.5 w-3.5"></i>
                                Maintenance
                            </a>
                        @endif
                    @endif

                    {{-- Secondary Utility Actions --}}
                    <a href="{{ route('assets.edit', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors duration-150">
                        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    <a href="{{ route('assets.clone', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors duration-150" title="Clone Asset Specifications">
                        <i data-lucide="copy" class="h-3.5 w-3.5"></i>
                        Clone
                    </a>
                    <a href="{{ route('assets.print-label', $asset) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors duration-150" title="Print Asset Label">
                        <i data-lucide="printer" class="h-3.5 w-3.5"></i>
                        Print
                    </a>

                    @if(optional($asset->status)->name !== 'Retired')
                        <button type="button" @click="retireModalOpen = true" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400 active:scale-[0.98] transition-colors duration-150" title="Decommission & Retire Asset">
                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                            Retire
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- Left Column: Contextual Sidebar (Prioritizing Assignment & Value) -->
            <div class="space-y-4 lg:col-span-1">
                <!-- 1. Current Assignment (Top Priority Operational Card) -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Current Assignment</h3>
                        @if($asset->isCurrentlyCheckedOut())
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                In Active Use
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                In Storage
                            </span>
                        @endif
                    </div>

                    @if($asset->isCurrentlyCheckedOut())
                        @php
                            $assignedUser = $asset->currentCheckout->checkedOutToUser;
                        @endphp
                        <div class="mt-4 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 font-bold text-sm border border-brand-100 dark:bg-brand-500/15 dark:text-brand-300 dark:border-brand-900/40">
                                    {{ strtoupper(substr($assignedUser->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $assignedUser->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $assignedUser->email ?? '—' }}</p>
                                </div>
                            </div>

                            <dl class="space-y-2.5 text-xs border-t border-gray-100 pt-3 dark:border-gray-800">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Department</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white text-right">{{ $assignedUser->department->name ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Location</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white text-right">{{ $asset->currentCheckout->location->name ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Checked Out At</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white text-right tabular-nums">{{ $asset->currentCheckout->checked_out_at?->format('d M Y, H:i') ?? '—' }}</dd>
                                </div>

                                @if($asset->currentCheckout->expected_return_date)
                                    <div class="flex items-center justify-between border-t border-gray-100 pt-2.5 dark:border-gray-800">
                                        <dt class="text-gray-500 dark:text-gray-400">Expected Return</dt>
                                        <dd class="text-right">
                                            <span class="font-semibold text-gray-900 dark:text-white tabular-nums">
                                                {{ $asset->currentCheckout->expected_return_date->format('d M Y') }}
                                            </span>
                                            @if($asset->currentCheckout->isOverdue())
                                                <span class="block text-[10px] font-bold text-red-600 dark:text-red-400">
                                                    Overdue by {{ $asset->currentCheckout->overdueDays() }} days
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @else
                        <div class="mt-4 flex flex-col items-center justify-center py-6 text-center">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mb-2.5">
                                <i data-lucide="package-check" class="h-5 w-5"></i>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">Ready for Allocation</p>
                            <p class="text-[11px] text-gray-400 mt-0.5 max-w-[200px]">Asset is unassigned and stored in inventory.</p>
                            
                            @if($asset->lastCheckout && $asset->lastCheckout->checkedOutToUser)
                                <p class="text-[10px] text-gray-400 mt-3 pt-2 border-t border-gray-100 dark:border-gray-800 w-full">
                                    Last assigned to: <strong class="text-gray-700 dark:text-gray-300 font-medium">{{ $asset->lastCheckout->checkedOutToUser->name }}</strong>
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- 2. Depreciation & Value Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Valuation & Capital</h3>
                        <i data-lucide="banknote" class="w-4 h-4 text-gray-400"></i>
                    </div>

                    @if($asset->depreciation && $asset->purchase_cost && $startDate)
                        @php
                            $totalMonths = max(1, (int) $asset->depreciation->months);
                            $elapsedMonths = (int) $startDate->diffInMonths(now());
                            $percent = min(100, (int) round(($elapsedMonths / $totalMonths) * 100));
                            $remainingMonths = max(0, $totalMonths - $elapsedMonths);
                        @endphp
                        <div class="mt-4 space-y-3">
                            <dl class="space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Profile</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white text-right">{{ $asset->depreciation->name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Original Cost</dt>
                                    <dd class="font-mono font-semibold text-gray-900 dark:text-white text-right tabular-nums">
                                        Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Current Book Value</dt>
                                    <dd class="font-mono font-bold text-right tabular-nums {{ $asset->bookValue == 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        Rp {{ number_format($asset->bookValue, 0, ',', '.') }}
                                    </dd>
                                </div>
                            </dl>

                            <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mb-1.5">
                                    <span class="font-medium">{{ $percent }}% Depreciated</span>
                                    <span class="font-medium">{{ $remainingMonths > 0 ? $remainingMonths . ' mos left' : 'Zero Book Value' }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                    <div class="h-full {{ $percent >= 100 ? 'bg-red-500' : 'bg-brand-500' }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-3 py-4 text-center text-xs text-gray-400 italic">
                            No depreciation schedule attached to this asset.
                        </div>
                    @endif
                </div>

                <!-- 3. Hardware Lifecycle & Warranty Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Lifecycle & Warranty</h3>
                        <i data-lucide="shield-check" class="w-4 h-4 text-gray-400"></i>
                    </div>

                    <dl class="mt-4 space-y-2.5 text-xs">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Operating System</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $asset->operatingSystem->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Acquisition Date</dt>
                            <dd class="font-medium text-gray-900 dark:text-white tabular-nums">{{ $startDate?->format('d M Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-100 pt-2.5 dark:border-gray-800">
                            <dt class="text-gray-500 dark:text-gray-400">Warranty Status</dt>
                            <dd class="text-right">
                                @if($asset->warranty_expiry_date)
                                    @if($asset->warranty_expiry_date->isPast())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-400">
                                            Expired ({{ $asset->warranty_expiry_date->format('d M Y') }})
                                        </span>
                                    @elseif(($daysLeft = (int) ceil(now()->diffInDays($asset->warranty_expiry_date, false))) <= 30)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-semibold text-orange-700 dark:bg-orange-950/40 dark:text-orange-400">
                                            Expires in {{ max(0, $daysLeft) }}d
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                            Active ({{ $asset->warranty_expiry_date->format('d M Y') }})
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">—</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- 4. Asset QR Code (Compact Utility Card) -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex items-center justify-between gap-4">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Scan Label</h4>
                        <p class="text-xs font-semibold text-gray-900 dark:text-white mt-1">Physical QR Tag</p>
                        <a href="{{ route('assets.print-label', $asset) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-medium text-brand-600 hover:underline dark:text-brand-400 mt-1">
                            <i data-lucide="printer" class="w-3 h-3"></i>
                            Print Label Sticker
                        </a>
                    </div>
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 p-1 dark:border-gray-700 dark:bg-white">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(url('/scan/asset/' . $asset->id)) }}" alt="QR Code" class="h-full w-full object-contain">
                    </div>
                </div>
            </div>

            <!-- Right Column: Structured Main Data & Tab Navigation -->
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <!-- Segmented Tabs Navigation -->
                    <div class="border-b border-gray-100 p-3 sm:p-4 dark:border-gray-800">
                        <nav class="flex overflow-x-auto rounded-xl bg-gray-100 p-1 dark:bg-gray-950 gap-0.5 no-scrollbar">
                            <button @click="activeTab = 'details'"
                                :class="activeTab === 'details' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs transition-colors">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                Specifications
                            </button>
                            <button @click="activeTab = 'components'"
                                :class="activeTab === 'components' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs transition-colors">
                                <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                                Components
                                @if($asset->installedComponents->count() > 0)
                                    <span class="rounded-full bg-brand-50 px-1.5 py-0.2 text-[10px] font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ $asset->installedComponents->count() }}
                                    </span>
                                @endif
                            </button>
                            <button @click="activeTab = 'history'"
                                :class="activeTab === 'history' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs transition-colors">
                                <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                                Movement
                            </button>
                            <button @click="activeTab = 'maintenance'"
                                :class="activeTab === 'maintenance' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs transition-colors">
                                <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                                Maintenance
                                @if($asset->maintenances->count() > 0)
                                    <span class="rounded-full bg-amber-50 px-1.5 py-0.2 text-[10px] font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                                        {{ $asset->maintenances->count() }}
                                    </span>
                                @endif
                            </button>
                            <button @click="activeTab = 'tickets'"
                                :class="activeTab === 'tickets' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs transition-colors">
                                <i data-lucide="life-buoy" class="w-3.5 h-3.5"></i>
                                Tickets
                                @if($asset->tickets->count() > 0)
                                    <span class="rounded-full bg-blue-50 px-1.5 py-0.2 text-[10px] font-bold text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                                        {{ $asset->tickets->count() }}
                                    </span>
                                @endif
                            </button>
                            <button @click="activeTab = 'audit_logs'"
                                :class="activeTab === 'audit_logs' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs transition-colors">
                                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                                Logs
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Contents -->
                    <div class="p-5 sm:p-6">
                        <!-- 1. Specifications Tab (Clean TailAdmin Description List - Zero Box Clutter) -->
                        <div x-show="activeTab === 'details'" class="space-y-6">
                            <!-- Section: Hardware & Model -->
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                                    <i data-lucide="laptop" class="w-4 h-4 text-brand-500"></i>
                                    Hardware & Model Information
                                </h4>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3.5 text-xs py-2">
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Brand & Model</dt>
                                        <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white">
                                            {{ optional($asset->product->brand)->name ?? '—' }} {{ optional($asset->product)->model ?? optional($asset->product)->name }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Device Type / Category</dt>
                                        <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white">
                                            {{ optional($asset->product->category)->name ?? '—' }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Serial Number (S/N)</dt>
                                        <dd class="mt-0.5 font-mono font-semibold text-gray-900 dark:text-white">
                                            {{ $asset->serial_number ?? '—' }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col sm:col-span-2">
                                        <dt class="text-gray-400">Procurement Vendor</dt>
                                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">
                                            {{ optional($asset->vendor)->name ?? '—' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Section: Technical Specifications -->
                            <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                                    <i data-lucide="cpu" class="w-4 h-4 text-brand-500"></i>
                                    Configuration & Technical Specs
                                </h4>
                                <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-950">
                                    <p class="whitespace-pre-line text-xs font-mono text-gray-800 dark:text-gray-200 leading-relaxed">
                                        {{ $asset->specification ?: 'No technical specifications documented for this hardware unit.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Section: Licenses & Connectivity -->
                            <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                                    <i data-lucide="wifi" class="w-4 h-4 text-brand-500"></i>
                                    Network & Software Licenses
                                </h4>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3.5 text-xs py-2">
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Windows OS License</dt>
                                        <dd class="mt-0.5 font-mono text-gray-900 dark:text-white">
                                            {{ $asset->license_windows ?: '—' }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Office Suite License</dt>
                                        <dd class="mt-0.5 font-mono text-gray-900 dark:text-white">
                                            {{ $asset->license_office ?: '—' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Section: Procurement & Financials -->
                            <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                                    <i data-lucide="receipt" class="w-4 h-4 text-brand-500"></i>
                                    Procurement & Invoicing
                                </h4>
                                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3.5 text-xs py-2">
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Purchase Date</dt>
                                        <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white tabular-nums">
                                            {{ $asset->purchase_date?->format('d M Y') ?? '—' }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Acquisition Cost</dt>
                                        <dd class="mt-0.5 font-mono font-semibold text-gray-900 dark:text-white tabular-nums">
                                            {{ $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 0, ',', '.') : '—' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Section: Internal Notes -->
                            @if($asset->notes)
                                <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Internal Notes</h4>
                                    <div class="rounded-lg border-l-2 border-brand-500 bg-gray-50/70 p-3 text-xs text-gray-700 dark:bg-gray-950 dark:text-gray-300">
                                        {{ $asset->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 2. Installed Components Tab -->
                        <div x-show="activeTab === 'components'" class="space-y-4" style="display: none;">
                            <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">
                                    Installed Components & Parts
                                </h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Attached upgrades (RAM, SSD, GPU)</span>
                            </div>

                            @if($asset->installedComponents->isNotEmpty())
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs text-left">
                                        <thead class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                                            <tr>
                                                <th class="px-4 py-2.5">Component</th>
                                                <th class="px-4 py-2.5">Model / Serial</th>
                                                <th class="px-4 py-2.5">Installed Date</th>
                                                <th class="px-4 py-2.5 text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            @foreach($asset->installedComponents as $installedComp)
                                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                                    <td class="px-4 py-3">
                                                        <span class="block font-mono text-xs font-bold text-gray-900 dark:text-white">{{ $installedComp->component_tag ?? '—' }}</span>
                                                        <a href="{{ route('components.show', $installedComp) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                                            {{ $installedComp->name }}
                                                        </a>
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                                        <span>{{ $installedComp->model_number ?? '—' }}</span>
                                                        @if($installedComp->serial)
                                                            <span class="block text-[10px] text-gray-400 font-mono">SN: {{ $installedComp->serial }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 tabular-nums">
                                                        {{ $installedComp->release_date ? $installedComp->release_date->format('d M Y') : ($installedComp->created_at ? $installedComp->created_at->format('d M Y') : '—') }}
                                                    </td>
                                                    <td class="px-4 py-3 text-end">
                                                        <form action="{{ route('components.checkin', $installedComp) }}" method="POST" onsubmit="return confirm('Detach component from this asset?')">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-orange-200 bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100 dark:border-orange-900/40 dark:bg-orange-950/30 dark:text-orange-400 transition-colors">
                                                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Detach
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="py-12 text-center">
                                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mb-2">
                                        <i data-lucide="cpu" class="h-5 w-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white">No Components Attached</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Attach RAM, SSD, or replacement parts from the Component details page.</p>
                                </div>
                            @endif
                        </div>

                        <!-- 3. Movement History Tab -->
                        <div x-show="activeTab === 'history'" class="space-y-4" style="display: none;">
                            @php
                                $utilization = $asset->utilization_summary ?? [
                                    'total_display' => '< 1 day',
                                    'top_user' => null,
                                    'top_display' => '< 1 day',
                                ];
                            @endphp

                            @if($asset->checkoutHistory->count() > 0)
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 mb-3">
                                    <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-gray-950">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                            <i data-lucide="clock" class="h-4 w-4"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Utilization Time</p>
                                            <p class="text-xs font-bold text-gray-900 dark:text-white mt-0.5">
                                                {{ $utilization['total_display'] ?? '< 1 day' }}
                                                <span class="text-[11px] font-normal text-gray-500">({{ $asset->checkoutHistory->count() }} {{ Str::plural('assignment', $asset->checkoutHistory->count()) }})</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-gray-950">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <i data-lucide="award" class="h-4 w-4"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Primary Holder</p>
                                            <p class="text-xs font-bold text-gray-900 dark:text-white mt-0.5">
                                                {{ $utilization['top_user'] ?? '—' }}
                                                @if(!empty($utilization['top_user']))
                                                    <span class="text-[11px] font-normal text-gray-500">({{ $utilization['top_display'] ?? '< 1 day' }})</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2.5">User & Dept</th>
                                            <th class="px-4 py-2.5">Checkout</th>
                                            <th class="px-4 py-2.5">Checkin / Due</th>
                                            <th class="px-4 py-2.5">Duration</th>
                                            <th class="px-4 py-2.5">Status</th>
                                            <th class="px-4 py-2.5 text-end">Document</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->checkoutHistory as $history)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                                <td class="px-4 py-3">
                                                    <span class="font-semibold text-gray-900 dark:text-white block">{{ $history->checkedOutToUser->name ?? '—' }}</span>
                                                    <span class="text-[10px] text-gray-400">{{ $history->department->name ?? '—' }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap tabular-nums">
                                                    {{ $history->checked_out_at?->format('d M Y') ?? '—' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @if($history->checked_in_at)
                                                        <span class="text-gray-600 dark:text-gray-400 tabular-nums">{{ $history->checked_in_at->format('d M Y') }}</span>
                                                    @elseif($history->isOverdue())
                                                        <x-ui.badge color="danger" variant="solid" size="xs">Overdue ({{ $history->overdueDays() }}d)</x-ui.badge>
                                                    @elseif($history->expected_return_date)
                                                        <x-ui.badge color="info" variant="light" size="xs">Due {{ $history->expected_return_date->format('d M') }}</x-ui.badge>
                                                    @else
                                                        <x-ui.badge color="primary" variant="light" size="xs">Active</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium whitespace-nowrap">
                                                    @if($history->checked_out_at)
                                                        {{ $history->checked_out_at->diffForHumans($history->checked_in_at ?? now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]) }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @if($history->checkinStatus)
                                                        <x-ui.badge :color="$history->checkinStatus->color ?? 'gray'" variant="light" size="xs">
                                                            {{ $history->checkinStatus->name }}
                                                        </x-ui.badge>
                                                    @elseif(!$history->checked_in_at)
                                                        <x-ui.badge color="primary" variant="light" size="xs">Deployed</x-ui.badge>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-end whitespace-nowrap">
                                                    <a href="{{ route('assets.bast', [$asset, $history]) }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2.5 py-1 text-xs font-semibold text-brand-600 hover:bg-gray-50 dark:border-gray-800 dark:text-brand-400 dark:hover:bg-gray-800 transition-colors">
                                                        <i data-lucide="file-text" class="h-3 w-3"></i>
                                                        BAST PDF
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-xs text-gray-400">
                                                    No movement history recorded yet
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 4. Maintenance History Tab -->
                        <div x-show="activeTab === 'maintenance'" class="space-y-4" style="display: none;">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2.5">Service Provider</th>
                                            <th class="px-4 py-2.5">Start Date</th>
                                            <th class="px-4 py-2.5">Completion</th>
                                            <th class="px-4 py-2.5">Duration</th>
                                            <th class="px-4 py-2.5">Cost</th>
                                            <th class="px-4 py-2.5">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->maintenances as $maint)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                                    {{ $maint->service_provider ?? 'Internal IT' }}
                                                </td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap tabular-nums">
                                                    {{ $maint->start_date?->format('d M Y') ?? '—' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @if($maint->completion_date)
                                                        <span class="text-gray-600 dark:text-gray-400 tabular-nums">{{ $maint->completion_date->format('d M Y') }}</span>
                                                    @else
                                                        <x-ui.badge color="warning" variant="light" size="xs">In Repair</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium whitespace-nowrap">
                                                    @if($maint->completion_date)
                                                        {{ $maint->start_date?->diffForHumans($maint->completion_date, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]) }}
                                                    @elseif($maint->created_at)
                                                        {{ $maint->created_at->diffForHumans(now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]) }}
                                                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-normal">(Ongoing)</span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 font-mono font-semibold text-gray-900 dark:text-white whitespace-nowrap tabular-nums">
                                                    {{ $maint->cost ? 'Rp ' . number_format($maint->cost, 0, ',', '.') : '—' }}
                                                </td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 truncate max-w-[200px]" title="{{ $maint->notes ?? '—' }}">
                                                    {{ $maint->notes ?? '—' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-xs text-gray-400">
                                                    No maintenance records found for this asset
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 5. Support Tickets Tab -->
                        <div x-show="activeTab === 'tickets'" class="space-y-4" style="display: none;">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2.5">Ticket ID</th>
                                            <th class="px-4 py-2.5">Requester</th>
                                            <th class="px-4 py-2.5">Issue Summary</th>
                                            <th class="px-4 py-2.5">Priority</th>
                                            <th class="px-4 py-2.5">Status</th>
                                            <th class="px-4 py-2.5 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->tickets as $t)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                                <td class="px-4 py-3 font-mono font-bold text-brand-600 dark:text-brand-400 whitespace-nowrap">
                                                    <a href="{{ route('tickets.show', $t) }}" class="hover:underline">#{{ $t->request_id }}</a>
                                                </td>
                                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                    {{ $t->requester_name ?? '—' }}
                                                </td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $t->problem_description }}">
                                                    {{ Str::limit($t->problem_description, 50) }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <x-ui.badge :color="$t->priority?->color ?? 'secondary'" variant="light" size="xs">
                                                        {{ $t->priority?->name ?? 'Normal' }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <x-ui.badge :color="$t->status?->color ?? 'primary'" variant="light" size="xs">
                                                        {{ $t->status?->name ?? 'Open' }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-4 py-3 text-end whitespace-nowrap">
                                                    <a href="{{ route('tickets.show', $t) }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors" title="View Ticket Details">
                                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-xs text-gray-400">
                                                    No support tickets logged for this asset
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 6. Audit Activity Logs Tab -->
                        <div x-show="activeTab === 'audit_logs'" class="space-y-4" style="display: none;">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2.5">Timestamp</th>
                                            <th class="px-4 py-2.5">Actor</th>
                                            <th class="px-4 py-2.5">Action</th>
                                            <th class="px-4 py-2.5">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->activityLogs as $log)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap tabular-nums">
                                                    {{ $log->created_at->format('d M Y, H:i') }}
                                                </td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                                    {{ $log->user?->name ?? 'System' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <x-ui.badge :color="$log->event === 'created' ? 'success' : ($log->event === 'deleted' ? 'danger' : 'info')" variant="light" size="xs">
                                                        {{ strtoupper($log->event) }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                                    {{ $log->description }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-xs text-gray-400">
                                                    No system activity logs recorded
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

        <!-- Modal: Retire Asset -->
        <div x-show="retireModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
            <div @click.away="retireModalOpen = false" class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-500 dark:bg-red-500/10">
                            <i data-lucide="trash-2" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Retire Asset</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Decommission asset <strong class="font-mono text-gray-900 dark:text-white">{{ $asset->asset_tag }}</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="retireModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <form action="{{ route('assets.retire.process', $asset) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Retirement Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="retired_at" value="{{ date('Y-m-d') }}" required
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Reason for Retirement <span class="text-red-500">*</span>
                        </label>
                        <select name="retirement_reason" required
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="End of Life / Aged (>= 4 years)">End of Life / Aged (≥ 4 years)</option>
                            <option value="Broken Beyond Repair">Broken Beyond Repair</option>
                            <option value="Damaged / Scrapped">Damaged / Scrapped</option>
                            <option value="Obsolete Technology">Obsolete Technology</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Retirement Notes
                        </label>
                        <textarea name="retirement_notes" rows="3" placeholder="Provide additional details regarding retirement..."
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                    </div>

                    <div class="rounded-lg border border-amber-200/60 bg-amber-50/50 p-3 dark:border-amber-900/30 dark:bg-amber-950/20">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_components_stripped" value="1" {{ $asset->is_components_stripped ? 'checked' : '' }}
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                            <div>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">Components Stripped / Salvaged (RAM & SSD removed)</span>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Check this option if RAM, SSD/HDD or parts have been stripped from this unit.</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button type="button" @click="retireModalOpen = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-red-700 transition-colors">
                            Confirm Retirement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
