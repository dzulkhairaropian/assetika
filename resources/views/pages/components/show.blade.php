@extends('layouts.app')

@section('content')
    @php
        $breadcrumbLabel = $component->component_tag ?? $component->name;
        $breadcrumbItems = [
            ['label' => 'Components', 'url' => route('components.index')],
            ['label' => $breadcrumbLabel],
        ];
    @endphp

    <div class="space-y-4" x-data="{ activeTab: 'details' }">
        <x-common.page-breadcrumb pageTitle="Component Details" :items="$breadcrumbItems" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Detailed specification, assignment status, and maintenance tickets for component unit {{ $component->component_tag ?? $component->name }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <!-- Header Hero Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <i data-lucide="cpu" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold font-mono text-gray-900 dark:text-white">{{ $component->component_tag ?? $component->name }}</h2>
                            @php
                                $statusObj = $component->status;
                                $color = $statusObj->color ?? 'primary';
                                $icon = $statusObj->icon ?? null;
                                $name = $statusObj->name ?? $component->status ?? 'Ready';
                            @endphp
                            <x-ui.badge :color="$color" size="sm" :featherIcon="$icon">
                                {{ $name }}
                            </x-ui.badge>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $component->name }} • {{ $component->manufacturer ?? 'Dell' }} {{ $component->model_number ?? '' }} • S/N: {{ $component->serial ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('components.edit', $component) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    <a href="{{ route('components.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="-mb-px flex space-x-6">
                <button @click="activeTab = 'details'"
                    :class="activeTab === 'details' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="flex items-center gap-2 border-b-2 py-3 text-xs font-semibold transition">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    Overview & Specifications
                </button>
                <button @click="activeTab = 'assignment'"
                    :class="activeTab === 'assignment' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="flex items-center gap-2 border-b-2 py-3 text-xs font-semibold transition">
                    <i data-lucide="laptop" class="w-4 h-4"></i>
                    Installed Asset
                </button>
                <button @click="activeTab = 'tickets'"
                    :class="activeTab === 'tickets' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="flex items-center gap-2 border-b-2 py-3 text-xs font-semibold transition">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    Maintenance & Tickets Log
                </button>
            </nav>
        </div>

        <!-- Tab 1: Overview & Specifications -->
        <div x-show="activeTab === 'details'" class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                    <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i>
                    Component Information
                </h3>

                <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Component Tag</dt>
                        <dd class="text-xs font-mono font-bold text-gray-900 dark:text-white">{{ $component->component_tag ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Component Name</dt>
                        <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $component->name }}</dd>
                    </div>
                    @if($component->product)
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Master Product Catalog</dt>
                        <dd class="text-xs font-medium text-brand-600 dark:text-brand-400 flex items-center gap-1">
                            <i data-lucide="package" class="w-3.5 h-3.5 text-brand-500"></i>
                            {{ $component->product->name }}
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Category</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Manufacturer / Brand</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->manufacturer ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Model Number / Spec</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->model_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Serial Number / Batch No.</dt>
                        <dd class="text-xs font-mono font-medium text-gray-800 dark:text-gray-200">{{ $component->serial ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Vendor / Supplier</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->vendor?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Location</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->location?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Purchase Date</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->purchase_date ? $component->purchase_date->format('d M Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Release Date (Pemasangan)</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->release_date ? $component->release_date->format('d M Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Warranty</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->warranty_months ? $component->warranty_months . ' Months' : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Purchase Cost</dt>
                        <dd class="text-xs font-semibold text-gray-900 dark:text-white">Rp {{ number_format($component->purchase_cost, 0, ',', '.') }}</dd>
                    </div>
                    @if($component->notes)
                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="text-[11px] font-medium text-gray-400">Notes</dt>
                        <dd class="text-xs text-gray-600 dark:text-gray-400 italic">{{ $component->notes }}</dd>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tab 2: Installed Asset -->
        <div x-show="activeTab === 'assignment'" class="space-y-4" style="display: none;">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
                @if($component->assignedAsset)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-2">
                                <i data-lucide="laptop" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                Installed on Asset: {{ $component->assignedAsset->asset_tag }}
                            </h4>
                            <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-medium">
                                Release / Installation Date: {{ $component->release_date ? $component->release_date->format('d M Y') : '-' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 text-xs">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Asset Device Name:</span>
                                <a href="{{ route('assets.show', $component->assignedAsset) }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">
                                    {{ $component->assignedAsset->product?->name ?? $component->assignedAsset->device_name ?? $component->assignedAsset->asset_tag }}
                                </a>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Assigned User:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">
                                    {{ $component->assignedAsset->assignedToUser?->name ?? 'Unassigned' }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-emerald-200/80 dark:border-emerald-900/40 flex justify-end">
                            <form action="{{ route('components.checkin', $component) }}" method="POST" onsubmit="return confirm('Detach component from asset?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-orange-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-orange-600 transition shadow-sm">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                    Detach / Checkin Component
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <form action="{{ route('components.checkout', $component) }}" method="POST" class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950 space-y-4">
                        @csrf
                        <div class="flex items-center justify-between border-b border-gray-200/60 pb-2.5 dark:border-gray-800">
                            <h4 class="text-xs font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                <i data-lucide="plus-circle" class="w-4 h-4 text-brand-500"></i>
                                Attach Component Unit to Asset
                            </h4>
                            <span class="text-[11px] text-gray-400">Generates auto-ticket for maintenance</span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-form.searchable-select name="asset_id" label="Target Asset" :options="$assetOpts" placeholder="Search & select Asset" required="true" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Installation Date (Release Date)</label>
                                <input type="date" name="release_date" value="{{ date('Y-m-d') }}"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Installation Notes (Optional)</label>
                            <input type="text" name="notes" placeholder="e.g. Replaced swollen battery with new Latitude 3410 battery"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                        </div>

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="inline-flex items-center gap-1.5 h-9 rounded-lg bg-brand-500 px-4 text-xs font-semibold text-white hover:bg-brand-600 transition shadow-sm">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Install / Attach Component
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Tab 3: Maintenance & Tickets Log -->
        <div x-show="activeTab === 'tickets'" class="space-y-4" style="display: none;">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 p-4 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="wrench" class="w-4 h-4 text-brand-500"></i>
                        Maintenance & Ticket History
                    </h3>
                </div>

                <div class="max-w-full overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500">Ticket ID</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500">Description</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500">Status</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500">Assigned To</th>
                                <th class="px-4 py-2.5 text-end font-medium text-gray-500">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($tickets as $ticket)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                    <td class="px-4 py-2.5 font-mono font-bold text-brand-600 dark:text-brand-400">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="hover:underline">{{ $ticket->request_id }}</a>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-800 dark:text-gray-200">
                                        {{ Str::limit($ticket->problem_description, 70) }}
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <x-ui.badge :color="optional($ticket->status)->color ?? 'success'" size="xs">
                                            {{ optional($ticket->status)->name ?? 'Completed' }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                        {{ optional($ticket->assignedTo)->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-end text-gray-500">
                                        {{ $ticket->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                        No maintenance or ticket records found for this component unit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
