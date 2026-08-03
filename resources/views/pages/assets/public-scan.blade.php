@extends('layouts.fullscreen-layout')

@section('content')
    @php
        $statusName = $asset->status->name ?? 'Unknown';
        $badgeColor = match($asset->status->color ?? 'default') {
            'success' => 'success',
            'primary' => 'primary',
            'warning' => 'warning',
            'danger' => 'error',
            default => 'light',
        };
        $icon = match($statusName) {
            'Ready', 'Ready to Deploy', 'Available' => 'circle-check',
            'Deployed', 'In Use', 'Borrowed' => 'user',
            'Broken' => 'x-circle',
            'In Repair', 'Service' => 'wrench',
            'Archived', 'Retired' => 'archive',
            default => 'help-circle',
        };
        $iconHtml = new \Illuminate\Support\HtmlString("<i data-lucide='{$icon}' class='w-3 h-3'></i>");
        $holderName = $asset->currentCheckout->checkedOutToUser->name ?? '-';
        $holderEmail = $asset->currentCheckout->checkedOutToUser->email ?? '-';
        $holderInitial = strtoupper(substr($holderName !== '-' ? $holderName : 'N', 0, 1));
        $activeRepair = $asset->status && $asset->status->name === 'In Repair' ? $asset->maintenances->where('completion_date', null)->first() : null;
        $scanTarget = url("/asset/{$asset->id}");
    @endphp

    <div class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8 dark:bg-[#121212]">
        <div class="mx-auto w-full max-w-2xl space-y-6">
            <div class="text-center space-y-1.5">
                <h2 class="text-xl font-bold uppercase tracking-wide text-gray-850 dark:text-white/95">{{ config('app.name') }}</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500">Public asset scan view</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 bg-gray-900 p-6 dark:border-gray-800 dark:bg-black/30">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 space-y-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex rounded bg-white/10 px-2.5 py-1 text-xs font-semibold uppercase tracking-wider text-white">{{ $asset->asset_tag }}</span>
                                <x-ui.badge :color="$badgeColor" variant="light" size="sm" :startIcon="$iconHtml">{{ $statusName }}</x-ui.badge>
                            </div>

                            <div class="space-y-1">
                                <h3 class="text-2xl font-bold leading-tight text-white">{{ $asset->device_name ?? '-' }}</h3>
                                <p class="text-xs text-gray-300">{{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-xs text-gray-300 sm:grid-cols-4">
                                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                                    <span class="block text-[10px] uppercase tracking-widest text-gray-400">Category</span>
                                    <strong class="mt-1 block text-sm text-white">{{ optional($asset->product->category)->name ?? '-' }}</strong>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                                    <span class="block text-[10px] uppercase tracking-widest text-gray-400">OS</span>
                                    <strong class="mt-1 block text-sm text-white">{{ optional($asset->operatingSystem)->name ?? '-' }}</strong>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                                    <span class="block text-[10px] uppercase tracking-widest text-gray-400">Brand</span>
                                    <strong class="mt-1 block text-sm text-white">{{ optional($asset->product->brand)->name ?? '-' }}</strong>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                                    <span class="block text-[10px] uppercase tracking-widest text-gray-400">Vendor</span>
                                    <strong class="mt-1 block text-sm text-white">{{ optional($asset->vendor)->name ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-col items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                            <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-white p-2">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($scanTarget) }}" alt="QR Code" class="h-full w-full" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Scan</p>
                                <p class="text-xs text-white">Public asset details</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-100 p-6 dark:divide-gray-800">
                    <div class="space-y-4 pb-6">
                        <h4 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-gray-400">
                            <i data-lucide="info" class="h-4 w-4 text-brand-500"></i>
                            Asset Identity
                        </h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-0.5">
                                <span class="block text-[10px] font-semibold uppercase text-gray-400">Serial Number</span>
                                <span class="select-all font-bold text-gray-800 dark:text-white/90">{{ $asset->serial_number ?? '-' }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="block text-[10px] font-semibold uppercase text-gray-400">Product Number</span>
                                <span class="font-bold text-gray-800 dark:text-white/90">{{ $asset->product_number ?? '-' }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="block text-[10px] font-semibold uppercase text-gray-400">Purchase Date</span>
                                <span class="font-bold text-gray-800 dark:text-white/90">{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('d M Y') : '-' }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="block text-[10px] font-semibold uppercase text-gray-400">Release Date</span>
                                <span class="font-bold text-gray-800 dark:text-white/90">{{ $asset->release_date ? \Carbon\Carbon::parse($asset->release_date)->format('d M Y') : '-' }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="block text-[10px] font-semibold uppercase text-gray-400">Location</span>
                                <span class="font-bold text-gray-800 dark:text-white/90">{{ $asset->currentCheckout->location->name ?? ($asset->location->name ?? '-') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 py-6">
                        <h4 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-gray-400">
                            <i data-lucide="user" class="h-4 w-4 text-brand-500"></i>
                            Current Holder
                        </h4>
                        @if($asset->isCurrentlyCheckedOut())
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.01]">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 font-bold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">{{ $holderInitial }}</div>
                                    <div class="min-w-0 space-y-1">
                                        <p class="text-sm font-bold text-gray-850 dark:text-white">{{ $holderName }}</p>
                                        <p class="text-xs text-gray-500">{{ $holderEmail }}</p>
                                        <div class="flex flex-wrap gap-x-3 gap-y-1 pt-1.5 text-xs text-gray-400">
                                            <span>Dept: <strong class="text-gray-650 dark:text-gray-300">{{ $asset->currentCheckout->department->name ?? '-' }}</strong></span>
                                            <span>•</span>
                                            <span>Checked out by: <strong class="text-gray-650 dark:text-gray-300">{{ $asset->currentCheckout->checkedOutByUser->name ?? '-' }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2.5 rounded-2xl border border-gray-100 bg-gray-50 p-4 text-xs italic text-gray-500 dark:border-gray-800 dark:bg-white/[0.01]">
                                <i data-lucide="info" class="h-4 w-4 text-gray-400"></i>
                                This asset is currently in stock.
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4 py-6">
                        <h4 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-gray-400">
                            <i data-lucide="sliders" class="h-4 w-4 text-brand-500"></i>
                            Hardware Specifications
                        </h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @if($asset->processor)
                                <div class="space-y-0.5">
                                    <span class="block text-[10px] font-medium uppercase text-gray-400">Processor</span>
                                    <span class="font-bold text-gray-800 dark:text-white/90">{{ $asset->processor }}</span>
                                </div>
                            @endif
                            @if($asset->ram)
                                <div class="space-y-0.5">
                                    <span class="block text-[10px] font-medium uppercase text-gray-400">RAM / Memory</span>
                                    <span class="font-bold text-gray-800 dark:text-white/90">{{ $asset->ram }} GB</span>
                                </div>
                            @endif
                            @if($asset->storage)
                                <div class="space-y-0.5">
                                    <span class="block text-[10px] font-medium uppercase text-gray-400">Storage</span>
                                    <span class="font-bold text-gray-800 dark:text-white/90">{{ $asset->storage }}</span>
                                </div>
                            @endif
                            @if($asset->specification)
                                <div class="space-y-0.5 sm:col-span-2">
                                    <span class="block text-[10px] font-medium uppercase text-gray-400">Other Specifications</span>
                                    <p class="whitespace-pre-line text-sm text-gray-600 dark:text-gray-400">{{ $asset->specification }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($activeRepair)
                        <div class="space-y-4 py-6">
                            <h4 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-red-500">
                                <i data-lucide="wrench" class="h-4 w-4"></i>
                                Maintenance Information
                            </h4>
                            <div class="space-y-3 rounded-2xl border border-red-100 bg-red-50/30 p-4 text-sm dark:border-red-950/20 dark:bg-red-950/5">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <span class="block text-[10px] uppercase text-gray-400">Service Provider</span>
                                        <strong class="text-gray-700 dark:text-gray-300">{{ $activeRepair->service_provider ?? '-' }}</strong>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase text-gray-400">Start Date</span>
                                        <strong class="text-gray-700 dark:text-gray-300">{{ $activeRepair->start_date?->format('d M Y') ?? '-' }}</strong>
                                    </div>
                                </div>
                                @if($activeRepair->description)
                                    <div class="border-t border-gray-100 pt-2 dark:border-gray-800">
                                        <span class="mb-0.5 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Issue Details</span>
                                        <p class="text-xs leading-relaxed text-gray-650 dark:text-gray-450">{{ $activeRepair->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
