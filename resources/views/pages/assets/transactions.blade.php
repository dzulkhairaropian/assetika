@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Transactions History" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('asset-transactions.index') }}" method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, serial, product..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                </div>

                @php $activeCount = request('type') ? 1 : 0; @endphp

                <div class="relative" @click.away="filterOpen = false">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 transition">
                        <svg class="stroke-current fill-white dark:fill-gray-900" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5" />
                            <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5" />
                        </svg>
                        Filters
                        @if($activeCount > 0)
                            <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                        @endif
                    </button>

                    <div x-show="filterOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-0 top-full z-50 mt-2 w-72 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                        style="display: none;">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Filter Options</h4>
                            <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Transaction Type</label>
                            <select name="type"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('type') == 'active' ? 'selected' : '' }}>Active Checkout</option>
                                <option value="returned" {{ request('type') == 'returned' ? 'selected' : '' }}>Returned</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ route('asset-transactions.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">
                                Clear All Filters
                            </a>
                            <button type="submit" @click="filterOpen = false"
                                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <div>
                <a href="{{ route('asset-transactions.export', request()->query()) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i data-feather="download" class="w-4 h-4 text-gray-500"></i>
                    Export Excel
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset Info</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Recipient</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Handled By</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center justify-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">BAST</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Notes</p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($transactions as $tx)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-700 dark:text-gray-400 whitespace-nowrap">
                                    <div class="flex flex-col space-y-0.5">
                                        <span>Checkout: <strong class="font-normal text-gray-900 dark:text-white">{{ $tx->checked_out_at?->format('d M Y H:i') ?? '-' }}</strong></span>
                                        @if($tx->checked_in_at)
                                            <span class="text-xs text-gray-500">Checkin: {{ $tx->checked_in_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col">
                                        @if($tx->asset)
                                            <a href="{{ route('assets.show', $tx->asset->id) }}" class="font-medium text-brand-600 hover:text-brand-700 text-theme-sm">
                                                {{ optional($tx->asset->product)->name ?? 'Deleted Asset' }}
                                            </a>
                                            <span class="text-xs text-gray-500">Tag: <strong>{{ $tx->asset->asset_tag }}</strong> | S/N: {{ $tx->asset->serial_number ?? '-' }}</span>
                                        @else
                                            <span class="text-gray-400 italic">Deleted Asset</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    @php
                                        // Tentukan nama status transaksi
                                        $transactionStatusName = $tx->checked_in_at ? 'Returned' : 'Checked Out';
                                        // Ambil objek Status dari database
                                        $transactionStatus = \App\Models\Status::where('name', $transactionStatusName)->first();
                                        // Buat HTML untuk ikon jika ada
                                        $iconHtml = optional($transactionStatus)->icon ? new \Illuminate\Support\HtmlString("<i data-feather='" . optional($transactionStatus)->icon . "' class='w-3 h-3'></i>") : null;
                                    @endphp
                                    @if($transactionStatus)
                                        <x-ui.badge :color="optional($transactionStatus)->color" variant="light" size="sm" :startIcon="$iconHtml">
                                            {{ optional($transactionStatus)->name }}
                                        </x-ui.badge>
                                    @else
                                        {{-- Fallback ke badge statis jika Status tidak ditemukan --}}
                                        @if($tx->checked_in_at)
                                            <x-ui.badge color="success" variant="light" size="sm" :startIcon="new \Illuminate\Support\HtmlString('<i data-feather=\'rotate-ccw\' class=\'w-3 h-3\'></i>')">Returned</x-ui.badge>
                                        @else
                                            <x-ui.badge color="primary" variant="light" size="sm" :startIcon="new \Illuminate\Support\HtmlString('<i data-feather=\'check-circle\' class=\'w-3 h-3\'></i>')">Active Checkout</x-ui.badge>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col text-theme-sm">
                                        <span class="text-gray-700 dark:text-gray-400 font-medium">{{ $tx->checkedOutToUser->name ?? '-' }}</span>
                                        <span class="text-xs text-gray-500">{{ $tx->department->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col text-theme-sm">
                                        <span class="text-gray-700 dark:text-gray-400">Out: {{ $tx->checkedOutByUser->name ?? '-' }}</span>
                                        @if($tx->checkedInByUser)
                                            <span class="text-xs text-gray-500">In: {{ $tx->checkedInByUser->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6 text-center">
                                    @if($tx->asset)
                                        <a href="{{ route('assets.bast', [$tx->asset->id, $tx->id]) }}" target="_blank" class="inline-flex items-center gap-1 text-brand-500 hover:text-brand-600" title="Download BAST PDF">
                                            <i data-feather="file-text" class="w-5 h-5"></i>
                                            <span class="text-xs font-semibold uppercase">PDF</span>
                                        </a>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-700">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-500 max-w-xs truncate" title="{{ $tx->checkout_notes ?? $tx->checkin_notes }}">
                                    @if($tx->checkout_notes)
                                        <span class="block">Out: {{ $tx->checkout_notes }}</span>
                                    @endif
                                    @if($tx->checkin_notes)
                                        <span class="block text-xs text-gray-400">In: {{ $tx->checkin_notes }}</span>
                                    @endif
                                    @if(!$tx->checkout_notes && !$tx->checkin_notes)
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-feather="clipboard" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No transactions found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">IT Asset checkout and checkin records will be displayed here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
