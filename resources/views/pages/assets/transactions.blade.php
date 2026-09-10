@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Transactions History" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('asset-transactions.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                <div class="relative flex items-center">
                    <span class="absolute left-2.5 text-gray-400">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, serial, product..."
                        class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                </div>

                @php $activeCount = request('type') ? 1 : 0; @endphp

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
                        class="absolute left-0 top-full z-50 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                        style="display: none;">
                        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                            <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Transaction Type</label>
                            <select name="type"
                                class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('type') == 'active' ? 'selected' : '' }}>Active Checkout</option>
                                <option value="returned" {{ request('type') == 'returned' ? 'selected' : '' }}>Returned</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ route('asset-transactions.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
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
            <div>
                <a href="{{ route('asset-transactions.export', request()->query()) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-gray-500"></i>
                    Export Excel
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset Info</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Recipient</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Handled By</th>
                            <th class="px-4 py-2.5 text-center font-medium text-gray-500 dark:text-gray-400">BAST</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-400 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span>Out: <strong class="font-medium text-gray-900 dark:text-white">{{ $tx->checked_out_at?->format('d M Y H:i') ?? '-' }}</strong></span>
                                        @if($tx->checked_in_at)
                                            <span class="text-[11px] text-gray-400">In: {{ $tx->checked_in_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        @if($tx->asset)
                                            <a href="{{ route('assets.show', $tx->asset->id) }}" class="font-medium text-brand-600 hover:text-brand-700">
                                                {{ optional($tx->asset->product)->name ?? 'Deleted Asset' }}
                                            </a>
                                            <span class="text-[11px] text-gray-400">Tag: <strong>{{ $tx->asset->asset_tag }}</strong> | S/N: {{ $tx->asset->serial_number ?? '-' }}</span>
                                        @else
                                            <span class="text-gray-400 italic">Deleted Asset</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $transactionStatusName = $tx->checked_in_at ? 'Returned' : 'Checked Out';
                                        $transactionStatus = \App\Models\Status::where('name', $transactionStatusName)->first();
                                    @endphp
                                    @if($transactionStatus)
                                        <x-ui.badge :color="optional($transactionStatus)->color" variant="light" size="sm" :featherIcon="optional($transactionStatus)->icon">{{ optional($transactionStatus)->name }}</x-ui.badge>
                                    @else
                                        @if($tx->checked_in_at)
                                            <x-ui.badge color="success" variant="light" size="sm" featherIcon="rotate-ccw">Returned</x-ui.badge>
                                        @else
                                            <x-ui.badge color="primary" variant="light" size="sm" featherIcon="circle-check">Active Checkout</x-ui.badge>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="text-gray-900 dark:text-white font-medium">{{ $tx->checkedOutToUser->name ?? '-' }}</span>
                                        <span class="text-[11px] text-gray-400">{{ $tx->department->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="text-gray-700 dark:text-gray-300">Out: {{ $tx->checkedOutByUser->name ?? '-' }}</span>
                                        @if($tx->checkedInByUser)
                                            <span class="text-[11px] text-gray-400">In: {{ $tx->checkedInByUser->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    @if($tx->asset)
                                        <a href="{{ route('assets.bast', [$tx->asset->id, $tx->id]) }}" target="_blank" class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-700 text-xs font-semibold" title="Download BAST PDF">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                            <span>PDF</span>
                                        </a>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-700">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 max-w-xs truncate" title="{{ $tx->checkout_notes ?? $tx->checkin_notes }}">
                                    @if($tx->checkout_notes)
                                        <span class="block">Out: {{ $tx->checkout_notes }}</span>
                                    @endif
                                    @if($tx->checkin_notes)
                                        <span class="block text-[11px] text-gray-400">In: {{ $tx->checkin_notes }}</span>
                                    @endif
                                    @if(!$tx->checkout_notes && !$tx->checkin_notes)
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="clipboard" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No transactions found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">IT Asset checkout and checkin records will be displayed here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
