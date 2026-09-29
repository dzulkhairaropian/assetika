@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Consumable Transactions History" :items="[['label' => 'Consumables', 'url' => route('consumables.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Track restock purchases, stock allocations, and user checkouts for IT consumable inventory.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Quick Filter Tabs + Search & Filters Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                @php
                    $currentTab = request('tab');
                    $tabFilters = array_filter(request()->except(['page', 'tab']));
                    $tabs = [
                        ['key' => null,       'label' => 'All Transactions', 'count' => $summary['total'] ?? 0,    'icon' => 'layers',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                        ['key' => 'restock',  'label' => 'Restock',           'count' => $summary['restock'] ?? 0,  'icon' => 'plus-circle',   'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                        ['key' => 'checkout', 'label' => 'Checkout / Use',    'count' => $summary['checkout'] ?? 0, 'icon' => 'log-out',       'badge' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                        ['key' => 'today',    'label' => 'Today',            'count' => $summary['today'] ?? 0,   'icon' => 'calendar',      'badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'],
                    ];
                @endphp
                <nav class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-track]:bg-white dark:[&::-webkit-scrollbar-track]:bg-transparent">
                    @foreach($tabs as $tab)
                        @php
                            $isActive = $currentTab === $tab['key'];
                            $tabUrl = route('consumable-transactions.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                        @endphp
                        <a href="{{ $tabUrl }}"
                           class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-colors duration-200 ease-in-out {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-white/5 dark:text-white font-semibold' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                            {{ $tab['label'] }}
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('consumable-transactions.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        @if(request('tab'))
                            <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product, user..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('type')) $activeCount++;
                            if (request('start_date') || request('from_date')) $activeCount++;
                            if (request('end_date') || request('to_date')) $activeCount++;
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

                            <div x-show="filterOpen"
                                x-transition
                                class="absolute left-0 top-full z-50 mt-2 w-80 sm:w-96 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                style="display: none;">
                                <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-2.5 text-left">
                                    <div class="col-span-2">
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Transaction Type</label>
                                        <select name="type" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Types</option>
                                            <option value="restock" {{ request('type') == 'restock' ? 'selected' : '' }}>Restock</option>
                                            <option value="checkout" {{ request('type') == 'checkout' ? 'selected' : '' }}>Checkout</option>
                                        </select>
                                    </div>

                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Start Date</label>
                                        <input type="date" name="start_date" value="{{ request('start_date', request('from_date')) }}"
                                            class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                    </div>

                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">End Date</label>
                                        <input type="date" name="end_date" value="{{ request('end_date', request('to_date')) }}"
                                            class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('consumable-transactions.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear All Filters
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('consumable-transactions.export', request()->query()) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-lucide="download" class="w-3.5 h-3.5 text-gray-500"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Date & Time</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Consumable Item</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Qty Change (Stock)</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Purchase Cost</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Recipient / Handled By</th>
                            <th class="px-4 py-2.5 text-center font-medium text-gray-500 dark:text-gray-400">Doc File</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Notes & Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 whitespace-nowrap font-mono">
                                    {{ $tx->transaction_date?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="block font-semibold text-gray-900 dark:text-white">
                                            {{ optional(optional($tx->consumable)->product)->name ?? 'Deleted Product' }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">Brand: {{ optional(optional(optional($tx->consumable)->product)->brand)->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($tx->isRestock())
                                        <x-ui.badge color="success" variant="light" size="sm" featherIcon="plus-circle">Restock</x-ui.badge>
                                    @else
                                        <x-ui.badge color="brand" variant="light" size="sm" featherIcon="log-out">Checkout</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-xs font-mono {{ $tx->isRestock() ? 'text-emerald-600 dark:text-emerald-400' : 'text-brand-600 dark:text-brand-400' }}">
                                            {{ $tx->isRestock() ? '+' : '-' }}{{ $tx->quantity }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-mono">
                                            ({{ $tx->quantity_before }} &rarr; {{ $tx->quantity_after }})
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            Rp {{ number_format($tx->total_cost, 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">
                                            Unit: Rp {{ number_format($tx->unit_cost, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        @if($tx->checkedOutToUser)
                                            <span class="text-gray-900 dark:text-white font-medium">To: {{ $tx->checkedOutToUser->name }}</span>
                                        @else
                                            <span class="text-gray-400 italic">To: -</span>
                                        @endif
                                        <span class="text-[10px] text-gray-400">By: {{ $tx->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                    @if($tx->evidence_file_path)
                                        <a href="{{ $tx->getEvidenceUrl() }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:border-gray-800 dark:bg-gray-950 dark:text-brand-400 transition" title="View Evidence Document">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                            <span>Doc</span>
                                        </a>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-700">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 max-w-xs truncate" title="{{ $tx->notes }}">
                                    {{ $tx->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="clipboard" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No transactions found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Consumable stock changes and checkouts history will be displayed here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Per page:</span>
                    <select onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();"
                        class="h-8 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
