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
        $categoryName = strtolower(optional($asset->product?->category)->name ?? 'hardware');
        $manufacturerName = optional($asset->product?->brand)->name ?? 'Lenova';
        $modelName = optional($asset->product)->model ?: optional($asset->product)->name;
        $holderUser = $asset->currentCheckout?->checkedOutToUser ?? $asset->assignedToUser;
    @endphp

    <div class="space-y-4" x-data="{ activeTab: 'details', retireModalOpen: false }">
        <x-common.page-breadcrumb pageTitle="Asset Detail & CMDB" :items="[['label' => 'Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag]]" />

        @if($isAged)
            <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-red-800 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400">
                <i data-lucide="triangle-alert" class="h-4 w-4 shrink-0"></i>
                <div class="text-xs">
                    <strong>Aging Alert:</strong> This asset is {{ $ageDisplay }} (Released on {{ $asset->release_date->format('d M Y') }}). It has reached or exceeded its 4-year lifecycle since release and may require replacement or retirement.
                </div>
            </div>
        @endif

        <!-- Hero Entity Header Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/3 lg:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-500 border border-brand-100 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <i data-lucide="monitor" class="h-8 w-8"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $asset->asset_tag }} &mdash; {{ $modelName }}</h2>
                            @php
                                $statusName = optional($asset->status)->name;
                                $color = optional($asset->status)->color ?? 'gray';
                            @endphp
                            <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="optional($asset->status)->icon">{{ $statusName }}</x-ui.badge>
                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $categoryName }} &middot; CMDB
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">
                            {{ $asset->device_name }} &bull; {{ $manufacturerName }} {{ $modelName }} &bull; S/N: <span class="font-mono text-gray-700 dark:text-gray-300">{{ $asset->serial_number ?? 'PF3XK2L4' }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        <i data-lucide="truck" class="h-3.5 w-3.5 text-brand-500"></i>
                        Ship / RMA
                    </button>
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        <i data-lucide="monitor-play" class="h-3.5 w-3.5 text-purple-500"></i>
                        Remote session
                    </button>
                    <a href="{{ route('assets.print-label', $asset) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        <i data-lucide="printer" class="h-3.5 w-3.5"></i>
                        Print Sticker
                    </a>
                    <a href="{{ route('assets.edit', $asset) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    <a href="{{ route('assets.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        &larr; Assets
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <!-- Left Main Details (8 cols) -->
            <div class="space-y-4 lg:col-span-8">

                <!-- Hardware & Configuration Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-white flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                        <i data-lucide="cpu" class="w-4 h-4 text-brand-500"></i>
                        Hardware Information
                    </h3>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Type</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $categoryName }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Manufacturer</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $manufacturerName }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Model</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $modelName }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Serial Number</span>
                            <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $asset->serial_number ?? 'PF3XK2L4' }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">MAC Address</span>
                            <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">A4:83:E7:2C:91:44</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">OS</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $asset->operatingSystem?->name ?? 'Windows 11 Enterprise 23H2' }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950 col-span-2">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Department</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $asset->department?->name ?? 'Sales / Operations' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Lifecycle & Warranty Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-white flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
                        Lifecycle & Warranty
                    </h3>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Lifecycle</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ optional($asset->status)->name ?? 'in use' }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Warranty Status</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">active</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Warranty Expires</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $asset->warranty_expiry_date?->format('Y-m-d') ?? '2027-09-12' }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Purchase Date</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $asset->purchase_date?->format('Y-m-d') ?? '2024-09-12' }}</span>
                        </div>

                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Encryption</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">encrypted</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Compliance</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">compliant</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Domain</span>
                            <span class="font-semibold text-blue-600 dark:text-blue-400">domain joined</span>
                        </div>
                        <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Last Imaged</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">Sep 15, 2024, 5:00 PM</span>
                        </div>
                    </div>
                </div>

                <!-- Related Support Tickets Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-white flex items-center gap-2">
                            <i data-lucide="life-buoy" class="w-4 h-4 text-brand-500"></i>
                            Related Tickets
                        </h3>
                        <a href="{{ route('tickets.create') }}?asset_id={{ $asset->id }}" class="text-[11px] font-semibold text-brand-600 hover:underline">+ New Ticket</a>
                    </div>

                    <div class="space-y-2">
                        @forelse($asset->tickets as $relTicket)
                            <div class="flex items-center justify-between p-2.5 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950">
                                <div>
                                    <a href="{{ route('tickets.show', $relTicket) }}" class="font-mono text-xs font-bold text-brand-600 hover:underline">#{{ $relTicket->request_id }}</a>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 font-medium">{{ \Illuminate\Support\Str::limit($relTicket->problem_description, 55) }}</p>
                                </div>
                                <x-ui.badge :color="$relTicket->status?->color ?? 'primary'" size="sm">
                                    {{ $relTicket->status?->name ?? 'new' }}
                                </x-ui.badge>
                            </div>
                        @empty
                            <div class="p-3 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950 text-xs text-gray-500">
                                INC6157073 &mdash; Cannot reach the internet &mdash; websites won't load <span class="ml-2 font-semibold text-amber-600">(new)</span>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950 text-xs text-gray-500">
                                INC8826182 &mdash; NorthMail closes itself a few times a day <span class="ml-2 font-semibold text-amber-600">(new)</span>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Right Sidebar Columns (4 cols) -->
            <div class="space-y-4 lg:col-span-4">

                <!-- Assignment & stock -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3 text-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2 dark:border-gray-800 flex items-center gap-1.5">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-brand-500"></i>
                        Assignment & stock
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Assigned to</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $holderUser?->name ?? 'Jordan Reyes' }}</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Email</span>
                            <span class="font-mono text-[11px] text-gray-700 dark:text-gray-300">{{ $holderUser?->email ?? 'jordan.reyes@northline.corp' }}</span>
                        </div>
                        <div class="flex justify-between py-0.5 border-b border-gray-100 dark:border-gray-800 pb-1.5">
                            <span class="text-gray-500">AD Link</span>
                            <a href="#" class="text-brand-600 font-semibold hover:underline">AD user &rarr;</a>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Shelf / cage</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">deployed (with user)</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Location note</span>
                            <span class="text-gray-400">&mdash;</span>
                        </div>
                    </div>
                </div>

                <!-- AD computer object -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3 text-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2 dark:border-gray-800 flex items-center gap-1.5">
                        <i data-lucide="server" class="w-3.5 h-3.5 text-brand-500"></i>
                        AD computer object
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Name</span>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">NL-LPT-0447</span>
                        </div>
                        <div class="flex flex-col py-0.5">
                            <span class="text-gray-500">OU</span>
                            <span class="font-mono text-[10px] text-gray-700 dark:text-gray-300 break-all bg-gray-50 p-1 rounded border border-gray-100 dark:border-gray-800 mt-1 dark:bg-gray-950">OU=Workstations,OU=Corp,DC=northline,DC=corp</span>
                        </div>
                        <div class="flex justify-between py-0.5 border-b border-gray-100 dark:border-gray-800 pb-1.5">
                            <span class="text-gray-500">Enabled</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Yes</span>
                        </div>
                        <div class="flex justify-between py-0.5 pt-1">
                            <span class="text-gray-500">ADUC</span>
                            <a href="#" class="text-brand-600 font-semibold hover:underline">Open in ADUC &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Network (live) -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3 text-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2 dark:border-gray-800 flex items-center gap-1.5">
                        <i data-lucide="wifi" class="w-3.5 h-3.5 text-brand-500"></i>
                        Network (live)
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">IP</span>
                            <span class="font-mono font-semibold text-gray-900 dark:text-white">10.20.14.88</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Gateway</span>
                            <span class="font-mono text-gray-700 dark:text-gray-300">10.20.14.1</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">DNS</span>
                            <span class="font-mono text-[11px] text-gray-700 dark:text-gray-300">10.20.14.10, 10.20.14.11</span>
                        </div>
                        <div class="flex justify-between py-0.5 border-t border-gray-100 dark:border-gray-800 pt-1.5">
                            <span class="text-gray-500">Connectivity</span>
                            <span class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> connected
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cross-links -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3 text-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2 dark:border-gray-800 flex items-center gap-1.5">
                        <i data-lucide="link" class="w-3.5 h-3.5 text-brand-500"></i>
                        Cross-links
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Bench / deployments</span>
                            <span class="text-gray-400">&mdash;</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Shipments</span>
                            <span class="text-gray-400">&mdash;</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
