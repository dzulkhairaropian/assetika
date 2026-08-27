@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: 'details', checkoutOpen: false, checkinOpen: false, maintOpen: false }">
        <x-common.page-breadcrumb pageTitle="General Affairs Asset Details" :items="[['label' => 'GA Assets', 'url' => route('ga-assets.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Detailed specification, assignment history, maintenance, and vehicle data for physical GA asset {{ $asset->asset_code }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <!-- Header Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <i data-feather="briefcase" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $asset->asset_code }}</h2>
                             @php
                                   $color = optional($asset->statusObject)->color;
                                   $icon = optional($asset->statusObject)->icon;
                                   $iconHtml = $icon ? new \Illuminate\Support\HtmlString("<i data-feather='{$icon}' class='w-3 h-3'></i>") : null;
                                   $statusName = optional($asset->statusObject)->name;
                               @endphp
                               <x-ui.badge :color="$color" variant="light" size="sm" :startIcon="$iconHtml">{{ $statusName }}</x-ui.badge>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $asset->name }} • {{ $asset->brand }} {{ $asset->model }} • Qty: {{ $asset->qty }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('ga-assets.edit', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        <i data-feather="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    
                    @if($asset->status === 'Available')
                        <button type="button" @click="checkoutOpen = true" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-success-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-success-600">
                            <i data-feather="log-out" class="h-3.5 w-3.5"></i>
                            Checkout
                        </button>
                    @elseif(in_array($asset->status, ['Deployed', 'In Use', 'In Repair']))
                        <button type="button" @click="checkinOpen = true" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-warning-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-warning-600">
                            <i data-feather="log-in" class="h-3.5 w-3.5"></i>
                            Checkin
                        </button>
                    @endif
                    
                    <button type="button" @click="maintOpen = true" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-amber-600">
                        <i data-feather="tool" class="h-3.5 w-3.5"></i>
                        Maintenance
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Sidebar Info -->
            <div class="space-y-4 lg:col-span-1">
                <!-- Current Assignment -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Current Assignment</h3>
                    @if($asset->currentHolder)
                        <div class="space-y-3">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    <i data-feather="user" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $asset->currentHolder->name }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $asset->currentHolder->email }}</p>
                                </div>
                            </div>
                            @php
                                $lastAssignment = $asset->assignments->whereNull('returned_at')->first();
                            @endphp
                            <div class="grid grid-cols-2 gap-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <div class="col-span-2">
                                    <p class="text-[10px] font-medium uppercase text-gray-400">Assigned at</p>
                                    <p class="text-xs font-medium text-gray-800 dark:text-gray-205">
                                        {{ $lastAssignment && $lastAssignment->assigned_at ? $lastAssignment->assigned_at->format('d M Y, H:i') : '-' }}
                                    </p>
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
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">General Info</h3>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Category</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $asset->category->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Subcategory</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $asset->subcategory->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Serial Number</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $asset->serial_number ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Warranty Expiry</span>
                            <span class="font-medium {{ $asset->warranty_expiry_date?->isPast() ? 'text-red-650 dark:text-red-400' : 'font-medium text-gray-900 dark:text-white' }}">
                                {{ $asset->warranty_expiry_date?->format('d M Y') ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Tabs Navigation -->
                    <div class="flex border-b border-gray-100 px-4 dark:border-gray-800">
                        <button @click="activeTab = 'details'"
                            :class="activeTab === 'details' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="border-b-2 px-3 py-3 text-xs font-semibold transition-all">
                            Details
                        </button>
                        @if($asset->vehicle)
                            <button @click="activeTab = 'vehicle'"
                                :class="activeTab === 'vehicle' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="border-b-2 px-3 py-3 text-xs font-semibold transition-all">
                                Vehicle Specs
                            </button>
                        @endif
                        <button @click="activeTab = 'history'"
                            :class="activeTab === 'history' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="border-b-2 px-3 py-3 text-xs font-semibold transition-all">
                            Movement History
                        </button>
                        <button @click="activeTab = 'maintenance'"
                            :class="activeTab === 'maintenance' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="border-b-2 px-3 py-3 text-xs font-semibold transition-all">
                            Maintenance
                        </button>
                    </div>

                    <!-- Tabs Content -->
                    <div class="p-4 sm:p-5">
                        <!-- Details Tab -->
                        <div x-show="activeTab === 'details'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <!-- Product Info -->
                            <div>
                                <h4 class="mb-3 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    Product Information
                                </h4>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Brand & Model</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->brand }} {{ $asset->model }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Inventory Number</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->inventory_number ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Data -->
                            <div>
                                <h4 class="mb-3 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    Financial Data
                                </h4>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Purchase Date</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Purchase Cost</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Additional Cost</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">Rp {{ number_format($asset->additional_cost, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20 col-span-full">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Total Investment Value</p>
                                        <p class="mt-0.5 text-sm font-bold text-brand-600 dark:text-brand-400">Rp {{ number_format($asset->total_asset_value, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Vehicle Specifications -->
                        @if($asset->vehicle)
                            <div x-show="activeTab === 'vehicle'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                                <h4 class="mb-3 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    Vehicle Technical Data
                                </h4>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">License Plate</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->license_plate ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Chassis Number</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->chassis_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Engine Number</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->engine_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Fuel Consumption</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->fuel_consumption ? $asset->vehicle->fuel_consumption . ' km/l' : '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Tax Due Date</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->tax_due_date ? $asset->vehicle->tax_due_date->format('d M Y') : '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-[10px] font-medium uppercase text-gray-400">Last Odometer</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->last_odometer ? number_format($asset->vehicle->last_odometer) . ' km' : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Tab: History -->
                        <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="min-w-full text-xs divide-y divide-gray-100 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Employee</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Assigned</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Returned</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Return Cond.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->assignments as $assign)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                                <td class="px-4 py-2.5 font-semibold text-gray-800 dark:text-white/90">
                                                    {{ $assign->user->name ?? 'Unknown' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                    {{ $assign->assigned_at ? $assign->assigned_at->format('d M Y, H:i') : '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                    {{ $assign->returned_at ? $assign->returned_at->format('d M Y, H:i') : '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                    {{ $assign->condition_on_return ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-gray-400">No movement history.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab: Maintenance -->
                        <div x-show="activeTab === 'maintenance'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="min-w-full text-xs divide-y divide-gray-100 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Type</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Scheduled</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Completed</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Cost</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-gray-400">Vendor</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->maintenanceLogs as $log)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                                <td class="px-4 py-2.5 font-semibold text-gray-800 dark:text-white/90">
                                                    {{ $log->type }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                    {{ $log->scheduled_date ? $log->scheduled_date->format('d M Y') : '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                    {{ $log->completed_date ? $log->completed_date->format('d M Y') : '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-white/90">
                                                    Rp {{ number_format($log->cost, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                    {{ $log->vendor ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">No maintenance logged.</td>
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

        <!-- Checkout Modal -->
        <div x-show="checkoutOpen" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 p-4" style="display:none;">
            <div class="bg-white dark:bg-gray-900 rounded-xl max-w-md w-full p-5 space-y-4 shadow-xl border border-gray-200 dark:border-gray-800" @click.away="checkoutOpen = false">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Checkout GA Asset</h3>
                <form action="{{ route('ga-assets.checkout', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Employee <span class="text-error-500">*</span></label>
                        <select name="user_id" required class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Date Assigned <span class="text-error-500">*</span></label>
                        <input type="datetime-local" name="assigned_at" value="{{ date('Y-m-d\TH:i') }}" required class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Checkout Evidence (Photo)</label>
                        <input type="file" name="checkout_evidence" accept="image/*" class="w-full text-xs text-gray-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent p-3 text-xs dark:bg-gray-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="checkoutOpen = false" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600">Assign Asset</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Checkin Modal -->
        <div x-show="checkinOpen" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 p-4" style="display:none;">
            <div class="bg-white dark:bg-gray-900 rounded-xl max-w-md w-full p-5 space-y-4 shadow-xl border border-gray-200 dark:border-gray-800" @click.away="checkinOpen = false">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Checkin GA Asset</h3>
                <form action="{{ route('ga-assets.checkin', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Date Returned <span class="text-error-500">*</span></label>
                        <input type="datetime-local" name="returned_at" value="{{ date('Y-m-d\TH:i') }}" required class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status After Checkin <span class="text-error-500">*</span></label>
                        <select name="status" required class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white">
                            <option value="Available">Available</option>
                            <option value="Broken">Broken</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Condition on Return <span class="text-error-500">*</span></label>
                        <select name="condition_on_return" required class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white">
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Checkin Evidence (Photo)</label>
                        <input type="file" name="checkin_evidence" accept="image/*" class="w-full text-xs text-gray-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent p-3 text-xs dark:bg-gray-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="checkinOpen = false" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                        <button type="submit" class="rounded-lg bg-warning-500 text-white px-4 py-2 text-xs font-semibold hover:bg-warning-600">Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Maintenance Modal -->
        <div x-show="maintOpen" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 p-4" style="display:none;">
            <div class="bg-white dark:bg-gray-900 rounded-xl max-w-md w-full p-5 space-y-4 shadow-xl border border-gray-200 dark:border-gray-800" @click.away="maintOpen = false">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Log Maintenance</h3>
                <form action="{{ route('ga-assets.maintenance', $asset) }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Maintenance Type <span class="text-error-500">*</span></label>
                        <select name="type" required class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white">
                            <option value="Repair">Repair</option>
                            <option value="Routine">Routine Service</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Cost (Rp) <span class="text-error-500">*</span></label>
                        <input type="number" name="cost" required placeholder="e.g. 500000" class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Vendor / Workshop</label>
                        <input type="text" name="vendor" placeholder="e.g. Auto2000" class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Scheduled Date <span class="text-error-500">*</span></label>
                        <input type="date" name="scheduled_date" value="{{ date('Y-m-d') }}" required class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Completed Date (Optional)</label>
                        <input type="date" name="completed_date" class="h-9 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-xs dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes / Details</label>
                        <textarea name="notes" rows="2" placeholder="Describe the repair details..." class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent p-3 text-xs dark:bg-gray-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="maintOpen = false" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                        <button type="submit" class="rounded-lg bg-info-500 text-white px-4 py-2 text-xs font-semibold hover:bg-info-600">Save Log</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
