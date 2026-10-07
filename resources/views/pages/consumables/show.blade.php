@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Consumable Details" :items="[['label' => 'Consumables', 'url' => route('consumables.index')], ['label' => $consumable->product?->name ?? 'Consumable']]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Stock overview and transaction history for this consumable item.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <!-- Hero Entity Header Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-500 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <i data-lucide="package" class="h-8 w-8"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $consumable->product?->name ?? 'Unknown Product' }}</h2>
                            <x-ui.badge :color="$consumable->status?->color ?? 'primary'" variant="light" size="sm" :featherIcon="$consumable->status?->icon ?? 'info'">
                                {{ $consumable->status?->name ?? 'Unknown' }}
                            </x-ui.badge>
                        </div>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ optional($consumable->product->brand)->name ?? '-' }} • {{ optional($consumable->product->category)->name ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                    <a href="{{ route('consumables.restock', $consumable) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-success-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-success-600 transition">
                        <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                        Restock
                    </a>
                    @if($consumable->quantity > 0)
                        <a href="{{ route('consumables.checkout', $consumable) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                            Checkout
                        </a>
                    @endif
                    <a href="{{ route('consumables.edit', $consumable) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Sidebar: Stock Summary -->
            <div class="space-y-4 lg:col-span-1">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Stock Information</h3>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Current Stock</span>
                            <span class="font-bold {{ $consumable->isOutOfStock() ? 'text-error-600 dark:text-error-400' : ($consumable->isLowStock() ? 'text-warning-600 dark:text-warning-400' : 'text-success-600 dark:text-success-400') }}">
                                {{ number_format($consumable->quantity) }} units
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Low Stock Threshold</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($consumable->low_threshold) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Unit Cost</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($consumable->purchase_cost ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs border-t border-gray-100 pt-2 dark:border-gray-800">
                            <span class="text-gray-500">Stock Value</span>
                            <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($consumable->quantity * ($consumable->purchase_cost ?? 0), 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @if(! $consumable->isInStock())
                        <div class="mt-3 rounded-lg border p-2.5 text-[11px] font-medium {{ $consumable->isOutOfStock() ? 'border-error-200 bg-error-50 text-error-700 dark:border-error-900/30 dark:bg-error-500/10 dark:text-error-400' : 'border-warning-200 bg-warning-50 text-warning-700 dark:border-warning-900/30 dark:bg-warning-500/10 dark:text-warning-400' }}">
                            @if($consumable->isOutOfStock())
                                Out of stock — restock required before checkout.
                            @else
                                Low stock — below the threshold of {{ number_format($consumable->low_threshold) }} units.
                            @endif
                        </div>
                    @endif
                </div>

                @if($consumable->notes)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Notes</h3>
                        <p class="text-xs italic text-gray-600 dark:text-gray-400">{{ $consumable->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Main: Transaction History -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                <div class="border-b border-gray-100 p-4 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Transaction History</h3>
                    <p class="text-[11px] text-gray-400">Latest 20 stock movements for this item.</p>
                </div>
                <div class="max-w-full overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Date</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Qty</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Stock</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">To User</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Processed By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($transactions as $tx)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                    <td class="px-4 py-2.5 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                        {{ $tx->transaction_date?->format('d M Y, H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <x-ui.badge :color="$tx->isRestock() ? 'success' : 'primary'" variant="light" size="sm" :featherIcon="$tx->isRestock() ? 'plus' : 'log-out'">
                                            {{ ucfirst($tx->type) }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-2.5 font-semibold {{ $tx->isRestock() ? 'text-success-600 dark:text-success-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ $tx->isRestock() ? '+' : '-' }}{{ number_format($tx->quantity) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 font-mono">
                                        {{ number_format($tx->quantity_before) }} → {{ number_format($tx->quantity_after) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                        {{ $tx->checkedOutToUser?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                        {{ $tx->user?->name ?? 'System' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-1.5">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-gray-800/50">
                                                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                                            </div>
                                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No transactions yet</p>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Restock or checkout activity will appear here.</p>
                                        </div>
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
