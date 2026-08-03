@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: 'details', checkoutOpen: false, checkinOpen: false, maintOpen: false }">
        <x-common.page-breadcrumb pageTitle="General Affairs Asset Details" />

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <!-- Header Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <i data-lucide="briefcase" class="h-8 w-8"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $asset->asset_code }}</h2>
                             @php
                                   $color = optional($asset->statusObject)->color;
                                   $icon = optional($asset->statusObject)->icon;
                                   $iconHtml = $icon ? new \Illuminate\Support\HtmlString("<i data-lucide='{$icon}' class='w-3 h-3'></i>") : null;
                                   $statusName = optional($asset->statusObject)->name;
                               @endphp
                               <x-ui.badge :color="$color" variant="light" size="sm" :startIcon="$iconHtml">{{ $statusName }}</x-ui.badge>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $asset->name }} • {{ $asset->brand }} {{ $asset->model }} • Qty: {{ $asset->qty }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('ga-assets.edit', $asset) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        <i data-lucide="edit-3" class="h-4 w-4"></i>
                        Edit
                    </a>
                    
                    @if($asset->status === 'Available')
                        <button type="button" @click="checkoutOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-success-600">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Checkout
                        </button>
                    @elseif(in_array($asset->status, ['Deployed', 'In Use', 'In Repair']))
                        <button type="button" @click="checkinOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg bg-warning-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-warning-600">
                            <i data-lucide="log-in" class="h-4 w-4"></i>
                            Checkin
                        </button>
                    @endif
                    
                    <button type="button" @click="maintOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                        <i data-lucide="wrench" class="h-4 w-4"></i>
                        Maintenance
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Sidebar Info -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Current Assignment -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Current Assignment</h3>
                    @if($asset->currentHolder)
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    <i data-lucide="user" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $asset->currentHolder->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $asset->currentHolder->email }}</p>
                                </div>
                            </div>
                            @php
                                $lastAssignment = $asset->assignments->whereNull('returned_at')->first();
                            @endphp
                            <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                                <div class="col-span-2">
                                    <p class="text-[10px] font-medium uppercase text-gray-400">Assigned at</p>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $lastAssignment && $lastAssignment->assigned_at ? $lastAssignment->assigned_at->format('d M Y, H:i') : '-' }}
                                    </p>
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
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">General Info</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Category</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $asset->category->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Subcategory</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $asset->subcategory->name ?? '-' }}</span>
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
                        @if($asset->vehicle)
                            <button @click="activeTab = 'vehicle'"
                                :class="activeTab === 'vehicle' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="border-b-2 px-4 py-4 text-sm font-medium transition-all">
                                Vehicle Specs
                            </button>
                        @endif
                        <button @click="activeTab = 'history'"
                            :class="activeTab === 'history' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-4 text-sm font-medium transition-all">
                            Movement History
                        </button>
                        <button @click="activeTab = 'maintenance'"
                            :class="activeTab === 'maintenance' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-4 text-sm font-medium transition-all">
                            Maintenance
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
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->brand }} {{ $asset->model }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Inventory Number</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->inventory_number ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Data -->
                            <div>
                                <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-white/90">
                                    <i data-lucide="dollar-sign" class="h-5 w-5 text-brand-500"></i>
                                    Financial Data
                                </h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Purchase Date</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Purchase Cost</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Additional Cost</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Rp {{ number_format($asset->additional_cost, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20 col-span-full">
                                        <p class="text-xs font-medium uppercase text-gray-400">Total Investment Value</p>
                                        <p class="mt-1 text-base font-bold text-brand-600 dark:text-brand-400">Rp {{ number_format($asset->total_asset_value, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Vehicle Specifications -->
                        @if($asset->vehicle)
                            <div x-show="activeTab === 'vehicle'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                                <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-white/90">
                                    <i data-lucide="car" class="h-5 w-5 text-brand-500"></i>
                                    Vehicle Technical Data
                                </h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">License Plate</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->license_plate ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Chassis Number</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->chassis_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Engine Number</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->engine_number ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Fuel Consumption</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->fuel_consumption ? $asset->vehicle->fuel_consumption . ' km/l' : '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Tax Due Date</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->tax_due_date ? $asset->vehicle->tax_due_date->format('d M Y') : '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/20">
                                        <p class="text-xs font-medium uppercase text-gray-400">Last Odometer</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $asset->vehicle->last_odometer ? number_format($asset->vehicle->last_odometer) . ' km' : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Tab: History -->
                        <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Employee</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Assigned</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Returned</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Return Cond.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->assignments as $assign)
                                            <tr>
                                                <td class="px-6 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                                                    {{ $assign->user->name ?? 'Unknown' }}
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $assign->assigned_at ? $assign->assigned_at->format('d M Y, H:i') : '-' }}
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $assign->returned_at ? $assign->returned_at->format('d M Y, H:i') : '-' }}
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $assign->condition_on_return ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-10 text-center text-gray-400">No movement history.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab: Maintenance -->
                        <div x-show="activeTab === 'maintenance'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                            <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Scheduled</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Completed</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Cost</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Vendor</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($asset->maintenanceLogs as $log)
                                            <tr>
                                                <td class="px-6 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                                                    {{ $log->type }}
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $log->scheduled_date ? $log->scheduled_date->format('d M Y') : '-' }}
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $log->completed_date ? $log->completed_date->format('d M Y') : '-' }}
                                                </td>
                                                <td class="px-6 py-3 text-sm font-bold text-gray-800 dark:text-white/90">
                                                    Rp {{ number_format($log->cost, 0, ',', '.') }}
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $log->vendor ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">No maintenance logged.</td>
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
            <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl" @click.away="checkoutOpen = false">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Checkout GA Asset</h3>
                <form action="{{ route('ga-assets.checkout', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Employee <span class="text-error-500">*</span></label>
                        <select name="user_id" required class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Date Assigned <span class="text-error-500">*</span></label>
                        <input type="datetime-local" name="assigned_at" value="{{ date('Y-m-d\TH:i') }}" required class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Checkout Evidence (Photo)</label>
                        <input type="file" name="checkout_evidence" accept="image/*" class="w-full text-sm text-gray-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Notes</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent p-3 text-sm dark:bg-gray-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="checkoutOpen = false" class="px-4 py-2 border rounded-lg text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600">Assign Asset</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Checkin Modal -->
        <div x-show="checkinOpen" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 p-4" style="display:none;">
            <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl" @click.away="checkinOpen = false">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Checkin GA Asset</h3>
                <form action="{{ route('ga-assets.checkin', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Date Returned <span class="text-error-500">*</span></label>
                        <input type="datetime-local" name="returned_at" value="{{ date('Y-m-d\TH:i') }}" required class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Condition on Return <span class="text-error-500">*</span></label>
                        <select name="condition_on_return" required class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white">
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Checkin Evidence (Photo)</label>
                        <input type="file" name="checkin_evidence" accept="image/*" class="w-full text-sm text-gray-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Notes</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent p-3 text-sm dark:bg-gray-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="checkinOpen = false" class="px-4 py-2 border rounded-lg text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-warning-500 text-white rounded-lg text-sm font-medium hover:bg-warning-600">Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Maintenance Modal -->
        <div x-show="maintOpen" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 p-4" style="display:none;">
            <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl" @click.away="maintOpen = false">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Log Maintenance</h3>
                <form action="{{ route('ga-assets.maintenance', $asset) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Maintenance Type <span class="text-error-500">*</span></label>
                        <select name="type" required class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white">
                            <option value="Repair">Repair</option>
                            <option value="Routine">Routine Service</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Cost (Rp) <span class="text-error-500">*</span></label>
                        <input type="number" name="cost" required placeholder="e.g. 500000" class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Vendor / Workshop</label>
                        <input type="text" name="vendor" placeholder="e.g. Auto2000" class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Scheduled Date <span class="text-error-500">*</span></label>
                        <input type="date" name="scheduled_date" value="{{ date('Y-m-d') }}" required class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Completed Date (Optional)</label>
                        <input type="date" name="completed_date" class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Notes / Details</label>
                        <textarea name="notes" rows="2" placeholder="Describe the repair details..." class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent p-3 text-sm dark:bg-gray-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="maintOpen = false" class="px-4 py-2 border rounded-lg text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-info-500 text-white rounded-lg text-sm font-medium hover:bg-info-600">Save Log</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
