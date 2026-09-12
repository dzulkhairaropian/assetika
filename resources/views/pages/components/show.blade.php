@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Component Detail" :items="[['label' => 'Components', 'url' => route('components.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            View stock, specification, and assigned assets for component {{ $component->name }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Component Info --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <i data-lucide="cpu" class="w-4 h-4 text-brand-500"></i>
                    Component Info
                </h3>

                <dl class="space-y-2.5">
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Component Tag</dt>
                        <dd class="text-xs font-mono font-bold text-gray-900 dark:text-white">{{ $component->component_tag ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Name</dt>
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
                        <dt class="text-[11px] font-medium text-gray-400">Model Number</dt>
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
                        <dt class="text-[11px] font-medium text-gray-400">Warranty</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->warranty_months ? $component->warranty_months . ' Months' : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Purchase Cost</dt>
                        <dd class="text-xs font-semibold text-gray-900 dark:text-white">Rp {{ number_format($component->purchase_cost, 0, ',', '.') }}</dd>
                    </div>
                    @if($component->notes)
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Notes</dt>
                        <dd class="text-xs text-gray-600 dark:text-gray-400 italic">{{ $component->notes }}</dd>
                    </div>
                    @endif
                </dl>

                <div class="mt-5 flex gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('components.edit', $component) }}" class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                    </a>
                    <a href="{{ route('components.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        Back
                    </a>
                </div>
            </div>

            {{-- Unit Status & Asset Assignment --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-brand-500"></i>
                        Unit Status & Assignment
                    </h3>
                    <div>
                        @if($component->status === 'Installed')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Installed
                            </span>
                        @elseif($component->status === 'Maintenance')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Maintenance
                            </span>
                        @elseif($component->status === 'Retired')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span> Retired
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> Ready (In Stock)
                            </span>
                        @endif
                    </div>
                </div>

                @if($component->assignedAsset)
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-2">
                                <i data-lucide="laptop" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                Installed on Asset: {{ $component->assignedAsset->asset_tag }}
                            </h4>
                            <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-medium">
                                Installed: {{ $component->release_date ? $component->release_date->format('d M Y') : '-' }}
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

                        <div class="pt-2 border-t border-emerald-200/80 dark:border-emerald-900/40 flex justify-end">
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
                    {{-- Checkout Form --}}
                    <form action="{{ route('components.checkout', $component) }}" method="POST" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950 space-y-3">
                        @csrf
                        <div class="flex items-center justify-between border-b border-gray-200/60 pb-2 dark:border-gray-800">
                            <h4 class="text-xs font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-brand-500"></i>
                                Attach Component Unit to Asset
                            </h4>
                            <span class="text-[11px] text-gray-400">Generates auto-ticket for maintenance</span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Target Asset <span class="text-red-500">*</span></label>
                                <x-form.searchable-select
                                    name="asset_id"
                                    :options="$allAssets->map(fn($a) => ['id' => $a->id, 'text' => $a->asset_tag . ' - ' . ($a->product?->name ?? $a->device_name) . ($a->assignedToUser ? ' ('.$a->assignedToUser->name.')' : '')])->toArray()"
                                    placeholder="Search & select Asset..."
                                    required="true"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Installation Date (Release Date)</label>
                                <input type="date" name="release_date" value="{{ date('Y-m-d') }}"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-white px-2.5 text-xs dark:border-gray-800 dark:bg-gray-900">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Installation Notes (Optional)</label>
                            <input type="text" name="notes" placeholder="e.g. Replaced swollen battery with new Latitude 3410 battery"
                                class="h-8 w-full rounded-lg border border-gray-200 bg-white px-2.5 text-xs dark:border-gray-800 dark:bg-gray-900">
                        </div>

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="inline-flex items-center gap-1.5 h-8 rounded-lg bg-brand-500 px-4 text-xs font-semibold text-white hover:bg-brand-600 transition shadow-sm">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                Install / Attach Component
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
