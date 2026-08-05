@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Consumable Transactions History" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('consumable-transactions.index') }}" method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by product..."
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
                                <option value="">All Types</option>
                                <option value="restock" {{ request('type') == 'restock' ? 'selected' : '' }}>Restock</option>
                                <option value="checkout" {{ request('type') == 'checkout' ? 'selected' : '' }}>Checkout</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ route('consumable-transactions.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">
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
                <a href="{{ route('consumable-transactions.export', request()->query()) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i data-feather="download" class="w-4 h-4 text-gray-500"></i>
                    Export Excel
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Date</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Product</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Type</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Qty Before / After</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Cost</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Checked Out To / Processor</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Docs</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($transactions as $tx)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 whitespace-nowrap">
                                    {{ $tx->transaction_date->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ optional(optional($tx->consumable)->product)->name ?? 'Deleted Product' }}
                                        </span>
                                        <span class="text-xs text-gray-500">Brand: {{ optional(optional(optional($tx->consumable)->product)->brand)->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                        @php
                                            // Ambil objek Status untuk 'Restock' atau 'Checkout'
                                            $transactionStatusName = $tx->isRestock() ? 'Restock' : 'Checkout';
                                            $transactionStatus = \App\Models\Status::where('name', $transactionStatusName)->first();
                                            $iconHtml = optional($transactionStatus)->icon ? new \Illuminate\Support\HtmlString("<i data-feather='" . optional($transactionStatus)->icon . "' class='w-3 h-3'></i>") : null;
                                        @endphp
                                        @if($transactionStatus)
                                            <x-ui.badge :color="optional($transactionStatus)->color" variant="light" size="sm" :startIcon="$iconHtml">
                                                {{ optional($transactionStatus)->name }}
                                            </x-ui.badge>
                                        @else
                                            {{-- Fallback ke badge statis jika Status tidak ditemukan --}}
                                            @if($tx->isRestock())
                                                <x-ui.badge color="success" variant="light" size="sm">Restock</x-ui.badge>
                                            @else
                                                <x-ui.badge color="primary" variant="light" size="sm">Checkout</x-ui.badge>
                                            @endif
                                        @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-semibold {{ $tx->isRestock() ? 'text-success-600' : 'text-brand-600' }}">
                                            {{ $tx->isRestock() ? '+' : '-' }}{{ $tx->quantity }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            ({{ $tx->quantity_before }} &rarr; {{ $tx->quantity_after }})
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col text-theme-sm">
                                        <span class="text-gray-700 dark:text-gray-400">
                                            Total: Rp {{ number_format($tx->total_cost, 2, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            Unit: Rp {{ number_format($tx->unit_cost, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col text-theme-sm">
                                        @if($tx->checkedOutToUser)
                                            <span class="text-gray-700 dark:text-gray-400 font-medium">To: {{ $tx->checkedOutToUser->name }}</span>
                                        @else
                                            <span class="text-gray-400 italic">To: -</span>
                                        @endif
                                        <span class="text-xs text-gray-500">By: {{ $tx->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-center">
                                    @if($tx->evidence_file_path)
                                        <a href="{{ $tx->getEvidenceUrl() }}" target="_blank" class="inline-block text-brand-500 hover:text-brand-600" title="View Evidence">
                                            <i data-feather="file-text" class="w-5 h-5"></i>
                                        </a>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-700">-</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-500 max-w-xs truncate" title="{{ $tx->notes }}">
                                    {{ $tx->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No transactions found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Consumable stock changes and checkouts history will be displayed here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-800">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
