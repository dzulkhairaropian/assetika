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

    <div class="space-y-4" x-data="{ activeTab: 'details', retireModalOpen: false }">
        <x-common.page-breadcrumb pageTitle="Asset Details" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Detailed specification, assignment history, maintenance, and support tickets for asset {{ $asset->asset_tag }}.
        </p>

        @if($isAged)
            <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-red-800 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400">
                <i data-lucide="triangle-alert" class="h-4 w-4 shrink-0"></i>
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
                        <i data-lucide="monitor" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $asset->asset_tag }}</h2>
                            @php
                                $statusName = optional($asset->status)->name;
                                $color = optional($asset->status)->color ?? 'gray';
                            @endphp
                            <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="optional($asset->status)->icon">{{ $statusName }}</x-ui.badge>
                            @if($asset->is_components_stripped)
                                <x-ui.badge color="warning" variant="light" size="sm" featherIcon="cpu">Components Stripped</x-ui.badge>
                            @endif
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
                        <i data-lucide="printer" class="h-3.5 w-3.5"></i>
                        Print Sticker
                    </a>
                    <a href="{{ route('assets.edit', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    @if($asset->isCurrentlyCheckedOut())
                        <a href="{{ route('assets.checkin', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-orange-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-orange-600 transition">
                            <i data-lucide="log-in" class="h-3.5 w-3.5"></i>
                            Check-in
                        </a>
                        <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 transition">
                            <i data-lucide="wrench" class="h-3.5 w-3.5"></i>
                            Maintenance
                        </a>
                    @else
                        <a href="{{ route('assets.checkout', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-green-700 transition">
                            <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                            Check-out
                        </a>

                        @if($asset->status && in_array($asset->status->name, ['In Repair', 'Service']))
                            @php
                                $activeMaintenance = $asset->maintenances->where('completion_date', null)->first() ?? $asset->maintenances->first();
                            @endphp
                            @if($activeMaintenance)
                                <a href="{{ route('assets.maintenance.complete', [$asset, $activeMaintenance]) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                                    <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
                                    Complete Maintenance
                                </a>
                            @endif
                        @else
                            <a href="{{ route('assets.maintenance', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 transition">
                                <i data-lucide="wrench" class="h-3.5 w-3.5"></i>
                                Maintenance
                            </a>
                        @endif
                    @endif

                    @if(optional($asset->status)->name !== 'Retired')
                        <button type="button" @click="retireModalOpen = true" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gray-800 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 transition">
                            <i data-lucide="trash-2" class="h-3.5 w-3.5 text-red-400"></i>
                            Retire Asset
                        </button>
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
                                    <i data-lucide="user" class="h-4 w-4"></i>
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
                                <i data-lucide="user-minus" class="h-5 w-5"></i>
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
                        <i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i>
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
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                Details
                            </button>
                            <button @click="activeTab = 'components'"
                                :class="activeTab === 'components' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                                Installed Components
                                @if($asset->installedComponents->count() > 0)
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ $asset->installedComponents->count() }}
                                    </span>
                                @endif
                            </button>
                            <button @click="activeTab = 'history'"
                                :class="activeTab === 'history' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                                Movement History
                            </button>
                            <button @click="activeTab = 'audit_logs'"
                                :class="activeTab === 'audit_logs' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                                Audit Logs
                            </button>
                            <button @click="activeTab = 'maintenance'"
                                :class="activeTab === 'maintenance' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                                Maintenance History
                            </button>
                            <button @click="activeTab = 'tickets'"
                                :class="activeTab === 'tickets' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                                class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                                <i data-lucide="life-buoy" class="w-3.5 h-3.5"></i>
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
                        <!-- Installed Components Tab -->
                        <div x-show="activeTab === 'components'" class="space-y-4" style="display: none;" x-transition>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                                    Hardware Components Attached to This Asset
                                </h4>
                            </div>

                            @if($asset->installedComponents->isNotEmpty())
                                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50/50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-800">
                                            <tr>
                                                <th class="px-3.5 py-2.5 text-start font-medium text-gray-500">Component Tag / Name</th>
                                                <th class="px-3.5 py-2.5 text-start font-medium text-gray-500">Model / Serial</th>
                                                <th class="px-3.5 py-2.5 text-start font-medium text-gray-500">Installed Date</th>
                                                <th class="px-3.5 py-2.5 text-end font-medium text-gray-500">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            @foreach($asset->installedComponents as $installedComp)
                                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                    <td class="px-3.5 py-2.5">
                                                        <span class="block font-mono text-[11px] font-bold text-gray-900 dark:text-white">{{ $installedComp->component_tag ?? '-' }}</span>
                                                        <a href="{{ route('components.show', $installedComp) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                                            {{ $installedComp->name }}
                                                        </a>
                                                    </td>
                                                    <td class="px-3.5 py-2.5 text-gray-700 dark:text-gray-300">
                                                        <span>{{ $installedComp->model_number ?? '-' }}</span>
                                                        @if($installedComp->serial)
                                                            <span class="block text-[10px] text-gray-400 font-mono">SN: {{ $installedComp->serial }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3.5 py-2.5 text-gray-600 dark:text-gray-400">
                                                        {{ $installedComp->release_date ? $installedComp->release_date->format('d M Y') : ($installedComp->created_at ? $installedComp->created_at->format('d M Y') : '-') }}
                                                    </td>
                                                    <td class="px-3.5 py-2.5 text-end">
                                                        <form action="{{ route('components.checkin', $installedComp) }}" method="POST" onsubmit="return confirm('Detach component from asset?')">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1 rounded bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100 dark:bg-orange-500/10 dark:text-orange-400 transition">
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
                                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-6 text-center dark:border-gray-800 dark:bg-gray-950">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <i data-lucide="cpu" class="w-6 h-6 text-gray-400"></i>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No components attached</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">You can attach RAM, SSD, Battery from Component Details page.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Details Tab -->
                        <div x-show="activeTab === 'details'" class="space-y-6" x-transition>
                            <!-- Product Info -->
                            <div>
                                <h4 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2.5 dark:border-gray-800 dark:text-white">
                                    <i data-lucide="package" class="h-4 w-4 text-brand-500"></i>
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
                                    <i data-lucide="cpu" class="h-4 w-4 text-brand-500"></i>
                                    Technical Specifications
                                </h4>
                                <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                    <p class="whitespace-pre-line text-xs text-gray-700 dark:text-gray-300">{{ $asset->specification ?? 'No specifications provided.' }}</p>
                                </div>
                            </div>

                            <!-- Financial Data -->
                            <div>
                                <h4 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2.5 dark:border-gray-800 dark:text-white">
                                    <i data-lucide="dollar-sign" class="h-4 w-4 text-brand-500"></i>
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
                                        <i data-lucide="file-text" class="h-4 w-4 text-brand-500"></i>
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
                            @php
                                $totalMinutes = 0;
                                $userDurations = [];
                                foreach ($asset->checkoutHistory as $h) {
                                    $start = $h->checked_out_at;
                                    $end = $h->checked_in_at ?? now();
                                    if ($start) {
                                        $diff = $start->diffInMinutes($end);
                                        $totalMinutes += $diff;
                                        $uName = $h->checkedOutToUser?->name ?? 'Unknown';
                                        $userDurations[$uName] = ($userDurations[$uName] ?? 0) + $diff;
                                    }
                                }
                                arsort($userDurations);
                                $topUser = !empty($userDurations) ? key($userDurations) : null;
                                $topMinutes = !empty($userDurations) ? current($userDurations) : 0;

                                // Format Total Duration
                                $totalDays = (int) floor($totalMinutes / 1440);
                                $totalMonths = (int) floor($totalDays / 30);
                                $remainingDays = $totalDays % 30;

                                if ($totalMonths >= 12) {
                                    $totalY = (int) floor($totalMonths / 12);
                                    $totalM = $totalMonths % 12;
                                    $totalDisplay = $totalY . 'y ' . ($totalM > 0 ? $totalM . 'm' : '');
                                } elseif ($totalMonths >= 1) {
                                    $totalDisplay = $totalMonths . 'm ' . ($remainingDays > 0 ? $remainingDays . 'd' : '');
                                } else {
                                    $totalDisplay = $totalDays > 0 ? $totalDays . ' days' : '< 1 day';
                                }

                                // Format Top User Duration
                                $topDays = (int) floor($topMinutes / 1440);
                                $topMonths = (int) floor($topDays / 30);
                                if ($topMonths >= 1) {
                                    $topDisplay = $topMonths . ' ' . Str::plural('month', $topMonths);
                                } else {
                                    $topDisplay = $topDays > 0 ? $topDays . ' ' . Str::plural('day', $topDays) : '< 1 day';
                                }
                            @endphp

                            @if($asset->checkoutHistory->count() > 0)
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                            <i data-lucide="clock" class="h-4 w-4"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-medium uppercase text-gray-400">Total Utilization Time</p>
                                            <p class="text-xs font-bold text-gray-900 dark:text-white">
                                                {{ $totalDisplay }}
                                                <span class="text-[11px] font-normal text-gray-500">({{ $asset->checkoutHistory->count() }} {{ Str::plural('assignment', $asset->checkoutHistory->count()) }})</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <i data-lucide="award" class="h-4 w-4"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-medium uppercase text-gray-400">Top Holder</p>
                                            <p class="text-xs font-bold text-gray-900 dark:text-white">
                                                {{ $topUser ?? '-' }}
                                                @if($topUser)
                                                    <span class="text-[11px] font-normal text-gray-500">({{ $topDisplay }} total)</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                                        <tr>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">User / Dept</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Date Out</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Date In</th>
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Duration</th>
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
                                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 font-medium">
                                                    @if($history->checked_out_at)
                                                        {{ $history->checked_out_at->diffForHumans($history->checked_in_at ?? now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]) }}
                                                        @if(!$history->checked_in_at)
                                                            <span class="text-[10px] text-brand-600 dark:text-brand-400 font-normal">(Active)</span>
                                                        @endif
                                                    @else
                                                        -
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
                                                        <i data-lucide="download-cloud" class="h-3.5 w-3.5"></i>
                                                        BAST
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-6 text-center text-xs text-gray-400">
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
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Duration</th>
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
                                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 font-medium">
                                                    @if($maint->completion_date)
                                                        {{ $maint->start_date?->diffForHumans($maint->completion_date, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]) }}
                                                    @elseif($maint->created_at)
                                                        {{ $maint->created_at->diffForHumans(now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]) }}
                                                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-normal">(Ongoing)</span>
                                                    @else
                                                        -
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
                                                <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-400">
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
                                            <th class="px-4 py-2.5 font-medium text-gray-500">Response / Resolution</th>
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
                                                <td class="px-4 py-2.5 text-[11px]">
                                                    <div class="flex flex-col gap-0.5">
                                                        <span class="text-gray-600 dark:text-gray-300">
                                                            Resp: <span class="font-medium text-gray-900 dark:text-white">{{ $t->response_time_minutes ? $t->response_time_minutes . 'm' : '-' }}</span>
                                                        </span>
                                                        <span class="text-gray-600 dark:text-gray-300">
                                                            Res: <span class="font-medium text-gray-900 dark:text-white">{{ $t->resolution_time_hours ? $t->resolution_time_hours . 'h' : '-' }}</span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <x-ui.badge :color="$t->status?->color ?? 'primary'" variant="light" size="sm" :featherIcon="$t->status?->icon ?: 'disc'">
                                                        {{ $t->status?->name ?? 'Open' }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-4 py-2.5 text-end">
                                                    <a href="{{ route('tickets.show', $t) }}" class="text-gray-500 hover:text-brand-600 transition" title="View Ticket">
                                                        <i data-lucide="eye" class="w-4 h-4 inline-block"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                         @empty
                                             <tr>
                                                 <td colspan="7" class="px-4 py-6 text-center text-xs text-gray-400">
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
                            <p class="text-xs text-gray-500 dark:text-gray-400">Decommission asset <strong>{{ $asset->asset_tag }}</strong></p>
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
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">Components Stripped / Empty Laptop (RAM & SSD removed)</span>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Check this option if RAM, SSD/HDD or parts have been stripped from this unit.</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button type="button" @click="retireModalOpen = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700 transition">
                            Confirm Retirement
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
@endpush
