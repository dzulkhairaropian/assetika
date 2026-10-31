@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Asset Transfers" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Track and manage inter-company and inter-department asset transfers.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Tabs + Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                @php
                    $currentStatus = request('status', request('tab'));
                    $tabFilters = array_filter(request()->except(['page', 'status', 'tab']));
                    $tabs = [
                        ['key' => null,       'label' => 'All Transfers', 'count' => $summary['total'] ?? 0,    'icon' => 'layers',       'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'pending',  'label' => 'Pending',       'count' => $summary['pending'] ?? 0,  'icon' => 'clock',        'badge' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
                        ['key' => 'approved', 'label' => 'Approved',      'count' => $summary['approved'] ?? 0, 'icon' => 'circle-check', 'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                        ['key' => 'rejected', 'label' => 'Rejected',      'count' => $summary['rejected'] ?? 0, 'icon' => 'x-circle',     'badge' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'],
                    ];
                @endphp
                <nav class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-track]:bg-transparent">
                    @foreach($tabs as $tab)
                        @php
                            $isActive = $currentStatus === $tab['key'];
                            $tabUrl = route('asset-transfers.index', $tab['key'] ? array_merge($tabFilters, ['status' => $tab['key']]) : $tabFilters);
                        @endphp
                        <a href="{{ $tabUrl }}"
                           class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all duration-150 {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                            {{ $tab['label'] }}
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('asset-transfers.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, serial, hardware..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('company_id')) $activeCount++;
                        @endphp

                        <div class="relative" @click.away="filterOpen = false">
                            <button type="button" @click="filterOpen = !filterOpen"
                                class="relative inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                Filters
                                @if($activeCount > 0)
                                    <span class="inline-flex items-center justify-center min-w-[18px] h-4 px-1 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                                @endif
                            </button>

                            <div x-show="filterOpen" x-transition
                                class="absolute left-0 top-full z-50 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                style="display: none;">
                                <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="space-y-3 text-left">
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Company (Origin / Destination)</label>
                                        <select name="company_id" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Companies</option>
                                            @foreach($companies as $cmp)
                                                <option value="{{ $cmp->id }}" {{ request('company_id') == $cmp->id ? 'selected' : '' }}>{{ $cmp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('asset-transfers.index', request('status') ? ['status' => request('status')] : []) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(request()->filled('search') || request()->filled('company_id'))
                            <a href="{{ route('asset-transfers.index', request('status') ? ['status' => request('status')] : []) }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('asset-transfers.create') }}"
                           class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Transfer Asset
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Asset & Hardware</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Transfer Route (From &rarr; To)</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Requested By</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Reason / Notes</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($transfers as $transfer)
                            @php
                                $statusSlug = strtolower($transfer->status);
                                if ($statusSlug === 'pending') {
                                    $rowBg = 'bg-amber-50/30 dark:bg-amber-500/5 hover:bg-amber-100/30 dark:hover:bg-amber-500/10';
                                } elseif ($statusSlug === 'rejected') {
                                    $rowBg = 'bg-red-50/30 dark:bg-red-500/5 hover:bg-red-100/30 dark:hover:bg-red-500/10';
                                } else {
                                    $rowBg = 'hover:bg-gray-50/80 dark:hover:bg-white/[0.02]';
                                }
                            @endphp
                            <tr class="transition-colors duration-150 {{ $rowBg }}">
                                {{-- Asset Info --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        @if($transfer->asset)
                                            <a href="{{ route('assets.show', $transfer->asset->id) }}" class="font-bold font-mono text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400">
                                                {{ $transfer->asset->asset_tag }}
                                            </a>
                                            <span class="text-[11px] font-medium text-gray-800 dark:text-gray-200">
                                                {{ optional($transfer->asset->product)->name ?? $transfer->asset->device_name ?? 'Hardware' }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 font-mono">S/N: {{ $transfer->asset->serial_number ?? '-' }}</span>
                                        @else
                                            <span class="text-gray-400 italic">Deleted Asset</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Transfer Route --}}
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex flex-col min-w-[110px]">
                                            <span class="text-[10px] uppercase font-semibold text-gray-400">From</span>
                                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $transfer->fromCompany->name ?? '-' }}</span>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $transfer->fromDepartment->name ?? '-' }}</span>
                                        </div>

                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i>

                                        <div class="flex flex-col min-w-[110px]">
                                            <span class="text-[10px] uppercase font-semibold text-brand-500">To</span>
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $transfer->toCompany->name ?? '-' }}</span>
                                            <span class="text-[11px] text-gray-600 dark:text-gray-300">{{ $transfer->toDepartment->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Requested By --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $transfer->requestedBy->name ?? '-' }}</span>
                                        <span class="text-[11px] text-gray-400 font-mono">{{ $transfer->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col gap-0.5">
                                        <div>
                                            @if($transfer->status === 'approved')
                                                <x-ui.badge color="success" variant="light" size="sm">Approved</x-ui.badge>
                                            @elseif($transfer->status === 'rejected')
                                                <x-ui.badge color="danger" variant="light" size="sm">Rejected</x-ui.badge>
                                            @else
                                                <x-ui.badge color="warning" variant="light" size="sm">Pending</x-ui.badge>
                                            @endif
                                        </div>
                                        @if($transfer->approvedBy)
                                            <span class="text-[10px] text-gray-400">
                                                by {{ $transfer->approvedBy->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Reason / Notes --}}
                                <td class="px-4 py-2.5 max-w-xs">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-800 dark:text-gray-200 line-clamp-1" title="{{ $transfer->reason }}">
                                            {{ $transfer->reason ?: '-' }}
                                        </span>
                                        @if($transfer->notes)
                                            <span class="text-[11px] text-gray-400 italic line-clamp-1" title="{{ $transfer->notes }}">
                                                {{ $transfer->notes }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    @if($transfer->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form action="{{ route('asset-transfers.approve', $transfer) }}" method="POST" onsubmit="return confirm('Approve asset transfer and relocate asset?')">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 active:scale-[0.98] transition">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('asset-transfers.reject', $transfer) }}" method="POST" onsubmit="return confirm('Reject asset transfer request?')">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-rose-50 border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-400 transition">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        @if($transfer->asset)
                                            <a href="{{ route('assets.show', $transfer->asset->id) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                                <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                                                View
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                    No asset transfers recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
@endsection
