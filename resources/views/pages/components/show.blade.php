@extends('layouts.app')

@section('content')
    @php
        $breadcrumbLabel = $component->component_tag ?? $component->name;
        $breadcrumbItems = [
            ['label' => 'Components', 'url' => route('components.index')],
            ['label' => $breadcrumbLabel],
        ];
        $statusObj = $component->status;
        $statusColor = $statusObj->color ?? 'primary';
        $statusIcon = $statusObj->icon ?? null;
        $statusName = $statusObj->name ?? $component->status ?? 'Ready';
        $isInstalled = (bool) $component->assignedAsset;
    @endphp

    <div class="space-y-5" x-data="{ activeTab: 'details' }">
        <x-common.page-breadcrumb pageTitle="Component Details" :items="$breadcrumbItems" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Hardware part specifications, assigned host device, stock inventory, and linked maintenance logs.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Entity Header Hero Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 lg:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-xs">
                        <i data-lucide="cpu" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white font-mono">
                                {{ $component->component_tag ?? $component->name }}
                            </h2>
                            <x-ui.badge :color="$statusColor" variant="light" size="sm" :featherIcon="$statusIcon">{{ $statusName }}</x-ui.badge>
                            @if($isInstalled)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Attached to {{ $component->assignedAsset->asset_tag }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    In Stock ({{ number_format($component->qty) }} available)
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-medium">
                            <span class="text-gray-900 dark:text-white font-semibold">{{ $component->name }}</span> • 
                            {{ $component->category?->name ?? 'Hardware Component' }} • 
                            {{ $component->manufacturer ?? optional($component->product?->brand)->name ?? '-' }} {{ $component->model_number ?? '' }} • 
                            S/N: <span class="font-mono text-gray-700 dark:text-gray-300">{{ $component->serial ?? '—' }}</span>
                        </p>
                    </div>
                </div>

                {{-- Action Toolbar --}}
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                    <a href="{{ route('components.edit', $component) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    <a href="{{ route('components.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- 3-Column Layout: Left (1 Col Context Sidebar) vs Right (2 Col Content Hub) --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            {{-- Left Column: Inventory & Installed Host Context --}}
            <div class="space-y-4 lg:col-span-1">
                {{-- 1. Installed Asset Status --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Host Asset Assignment</h3>
                        @if($isInstalled)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                Installed
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                Standalone Stock
                            </span>
                        @endif
                    </div>

                    @if($isInstalled)
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                    <i data-lucide="laptop" class="h-5 w-5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('assets.show', $component->assignedAsset) }}" class="font-bold font-mono text-xs text-brand-600 hover:underline dark:text-brand-400 block truncate">
                                        {{ $component->assignedAsset->asset_tag }}
                                    </a>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                        {{ $component->assignedAsset->product?->name ?? $component->assignedAsset->device_name }}
                                    </p>
                                </div>
                            </div>

                            <dl class="space-y-2 text-xs pt-1">
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-400">Current User:</dt>
                                    <dd class="font-semibold text-gray-900 dark:text-white">{{ $component->assignedAsset->assignedToUser?->name ?? 'Unassigned' }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-400">Department:</dt>
                                    <dd class="text-gray-700 dark:text-gray-300">{{ $component->assignedAsset->department?->name ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-400">Installation Date:</dt>
                                    <dd class="font-mono text-gray-800 dark:text-gray-200">{{ $component->release_date ? $component->release_date->format('d M Y') : '—' }}</dd>
                                </div>
                            </dl>

                            <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                                <form action="{{ route('components.checkin', $component) }}" method="POST" onsubmit="return confirm('Detach component from asset?')">
                                    @csrf
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-400 transition">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        Detach from Asset
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 space-y-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                This hardware component is stored in <strong>{{ $component->location?->name ?? 'Inventory' }}</strong> and ready for upgrade/replacement dispatch.
                            </p>
                            <button type="button" @click="activeTab = 'assignment'" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-600 transition">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                Install to Asset
                            </button>
                        </div>
                    @endif
                </div>

                {{-- 2. Valuation & Procurement Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Valuation & Procurement</h3>
                    </div>
                    <dl class="mt-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Unit Cost:</dt>
                            <dd class="font-mono font-semibold text-gray-900 dark:text-white">Rp {{ number_format($component->purchase_cost ?? 0, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Quantity Stored:</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white tabular-nums">{{ number_format($component->qty) }} units</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Min Alert Threshold:</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-300 tabular-nums">{{ number_format($component->min_qty) }} units</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-2 dark:border-gray-800">
                            <dt class="text-gray-400">Warranty:</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200">{{ $component->warranty_months ? $component->warranty_months . ' Months' : '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Right Column: Structured Main Data & Tab Navigation --}}
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    {{-- Segmented Tabs Navigation --}}
                    <div class="border-b border-gray-100 p-3 sm:p-4 dark:border-gray-800">
                        <nav class="flex overflow-x-auto rounded-xl bg-gray-100 p-1 dark:bg-gray-950 gap-0.5 no-scrollbar">
                            <button @click="activeTab = 'details'"
                                :class="activeTab === 'details' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs transition-colors">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                Specifications & Procurement
                            </button>
                            <button @click="activeTab = 'assignment'"
                                :class="activeTab === 'assignment' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs transition-colors">
                                <i data-lucide="laptop" class="w-3.5 h-3.5"></i>
                                Attachment Setup
                            </button>
                            <button @click="activeTab = 'tickets'"
                                :class="activeTab === 'tickets' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs transition-colors">
                                <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                                Maintenance Log
                            </button>
                        </nav>
                    </div>

                    {{-- Tab 1: Specifications & Procurement --}}
                    <div class="p-5 sm:p-6" x-show="activeTab === 'details'">
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                                    <i data-lucide="cpu" class="w-4 h-4 text-brand-500"></i>
                                    Technical Specifications
                                </h4>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3.5 text-xs">
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Component Tag</dt>
                                        <dd class="mt-0.5 font-mono font-bold text-gray-900 dark:text-white">{{ $component->component_tag ?? '—' }}</dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Component Name</dt>
                                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $component->name }}</dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Category</dt>
                                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $component->category?->name ?? '—' }}</dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Manufacturer / Brand</dt>
                                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $component->manufacturer ?? '—' }}</dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Model Number / Spec</dt>
                                        <dd class="mt-0.5 font-mono font-medium text-gray-900 dark:text-white">{{ $component->model_number ?? '—' }}</dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Serial Number / Batch No.</dt>
                                        <dd class="mt-0.5 font-mono font-medium text-gray-900 dark:text-white">{{ $component->serial ?? '—' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                                    <i data-lucide="receipt" class="w-4 h-4 text-emerald-500"></i>
                                    Procurement & Logistics
                                </h4>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3.5 text-xs">
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Supplier / Vendor</dt>
                                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $component->vendor?->name ?? '—' }}</dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Storage Location</dt>
                                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $component->location?->name ?? '—' }}</dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Purchase Date</dt>
                                        <dd class="mt-0.5 font-mono font-medium text-gray-900 dark:text-white">{{ $component->purchase_date ? $component->purchase_date->format('d M Y') : '—' }}</dd>
                                    </div>
                                    <div class="flex flex-col">
                                        <dt class="text-gray-400">Installation Date</dt>
                                        <dd class="mt-0.5 font-mono font-medium text-gray-900 dark:text-white">{{ $component->release_date ? $component->release_date->format('d M Y') : '—' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            @if($component->notes)
                                <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Remarks / Notes</h4>
                                    <div class="rounded-lg border-l-2 border-brand-500 bg-gray-50/70 p-3 text-xs text-gray-700 dark:bg-gray-950 dark:text-gray-300">
                                        {{ $component->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Tab 2: Installation Setup Form --}}
                    <div class="p-5 sm:p-6" x-show="activeTab === 'assignment'" style="display: none;">
                        @if($isInstalled)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900/30 dark:bg-emerald-950/20 text-xs">
                                <h4 class="font-bold text-emerald-900 dark:text-emerald-300 mb-1">Component Currently In Active Use</h4>
                                <p class="text-emerald-700 dark:text-emerald-400">
                                    This unit is currently attached to asset <strong class="font-mono">{{ $component->assignedAsset->asset_tag }}</strong>. To reassign, detach it first using the sidebar action.
                                </p>
                            </div>
                        @else
                            <form action="{{ route('components.checkout', $component) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Install Component onto IT Asset</h4>
                                    <p class="text-xs text-gray-400">Select target hardware device to bind this component.</p>
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <x-form.searchable-select name="asset_id" label="Target IT Asset" :options="$assetOpts" placeholder="Search & select target asset" required="true" />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Installation Date <span class="text-error-500">*</span></label>
                                        <input type="date" name="release_date" value="{{ date('Y-m-d') }}" required
                                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-600 transition">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                        Confirm Installation
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>

                    {{-- Tab 3: Maintenance Log --}}
                    <div class="p-5 sm:p-6" x-show="activeTab === 'tickets'" style="display: none;">
                        @if(isset($tickets) && $tickets->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-gray-50/70 border-b border-gray-100 dark:bg-gray-950 dark:border-gray-800 text-[11px] font-semibold text-gray-400 uppercase">
                                        <tr>
                                            <th class="px-4 py-2.5">Ticket ID</th>
                                            <th class="px-4 py-2.5">Subject</th>
                                            <th class="px-4 py-2.5">Status</th>
                                            <th class="px-4 py-2.5">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($tickets as $t)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-4 py-2.5 font-mono font-bold text-brand-600">{{ $t->ticket_number ?? '#' . $t->id }}</td>
                                                <td class="px-4 py-2.5 font-medium text-gray-800 dark:text-gray-200">{{ $t->subject }}</td>
                                                <td class="px-4 py-2.5"><x-ui.badge size="sm">{{ $t->status?->name ?? 'Open' }}</x-ui.badge></td>
                                                <td class="px-4 py-2.5 text-gray-500 font-mono">{{ $t->created_at->format('d M Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-400 text-xs">
                                <i data-lucide="check-circle" class="h-6 w-6 mx-auto mb-2 text-emerald-500"></i>
                                No maintenance or service tickets recorded for this component.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
