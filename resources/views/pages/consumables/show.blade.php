@extends('layouts.app')

@section('content')
    @php
        $breadcrumbLabel = $consumable->product?->name ?? 'Consumable';
        $breadcrumbItems = [
            ['label' => 'Consumables', 'url' => route('consumables.index')],
            ['label' => $breadcrumbLabel],
        ];
        $isOut = $consumable->isOutOfStock();
        $isLow = $consumable->isLowStock();
    @endphp

    <div class="space-y-5" x-data="{ activeTab: 'history' }">
        <x-common.page-breadcrumb pageTitle="Consumable Details" :items="$breadcrumbItems" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Stock availability, reorder thresholds, inventory valuation, and supply distribution logs.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Entity Header Hero Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 lg:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-xs">
                        <i data-lucide="package" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{ $consumable->product?->name ?? 'Consumable Item' }}
                            </h2>
                            @if($isOut)
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                    Out of Stock
                                </span>
                            @elseif($isLow)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Low Stock ({{ number_format($consumable->quantity) }} left)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    In Stock ({{ number_format($consumable->quantity) }} available)
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-medium">
                            {{ optional($consumable->product?->brand)->name ?? 'General Brand' }} • 
                            {{ optional($consumable->product?->category)->name ?? 'Office Supply' }} • 
                            Threshold: <span class="font-bold text-gray-800 dark:text-gray-200">{{ number_format($consumable->low_threshold) }} units</span>
                        </p>
                    </div>
                </div>

                {{-- Action Toolbar --}}
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                    <a href="{{ route('consumables.restock', $consumable) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 active:scale-[0.98] transition">
                        <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                        Restock
                    </a>
                    @if($consumable->quantity > 0)
                        <a href="{{ route('consumables.checkout', $consumable) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                            Checkout
                        </a>
                    @endif
                    <a href="{{ route('consumables.edit', $consumable) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    <a href="{{ route('consumables.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- 3-Column Layout: Left (1 Col Context Sidebar) vs Right (2 Col Content Hub) --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            {{-- Left Column: Stock Inventory & Financial Valuation --}}
            <div class="space-y-4 lg:col-span-1">
                {{-- 1. Inventory Summary Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Inventory Status</h3>
                        @if($isOut)
                            <span class="text-[10px] font-bold text-rose-600">Reorder Required</span>
                        @elseif($isLow)
                            <span class="text-[10px] font-bold text-amber-600">Low Stock Alert</span>
                        @else
                            <span class="text-[10px] font-bold text-emerald-600">Healthy Stock</span>
                        @endif
                    </div>

                    <dl class="mt-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Available Stock:</dt>
                            <dd class="font-bold text-sm {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-gray-900 dark:text-white') }}">
                                {{ number_format($consumable->quantity) }} units
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Minimum Threshold:</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-300 tabular-nums">{{ number_format($consumable->low_threshold) }} units</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-2.5 dark:border-gray-800">
                            <dt class="text-gray-400">Unit Cost:</dt>
                            <dd class="font-mono font-medium text-gray-800 dark:text-gray-200">Rp {{ number_format($consumable->purchase_cost ?? 0, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Total Stock Value:</dt>
                            <dd class="font-mono font-bold text-gray-900 dark:text-white">Rp {{ number_format($consumable->quantity * ($consumable->purchase_cost ?? 0), 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- 2. Remarks / Storage Card --}}
                @if($consumable->notes)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Storage Notes</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $consumable->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Right Column: Transaction Log Table --}}
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-gray-100 p-4 dark:border-gray-800">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Transaction & Dispatch History</h3>
                            <p class="text-[11px] text-gray-400">Chronological checkout and restock events for this consumable item</p>
                        </div>
                    </div>

                    <div class="max-w-full overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-950 text-[11px] text-gray-400 font-semibold uppercase">
                                    <th class="px-4 py-2.5 text-start">Date</th>
                                    <th class="px-4 py-2.5 text-start">Type</th>
                                    <th class="px-4 py-2.5 text-center">Qty</th>
                                    <th class="px-4 py-2.5 text-start">Recipient User</th>
                                    <th class="px-4 py-2.5 text-end">Processed By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($transactions as $tx)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                        <td class="px-4 py-2.5 whitespace-nowrap font-mono text-gray-600 dark:text-gray-400">
                                            {{ $tx->transaction_date?->format('d M Y, H:i') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2.5 whitespace-nowrap">
                                            @if(($tx->action_type ?? $tx->type) === 'checkout')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                                                    Checkout
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                    Restock
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-bold font-mono text-gray-900 dark:text-white tabular-nums">
                                            {{ $tx->quantity }}
                                        </td>
                                        <td class="px-4 py-2.5 whitespace-nowrap">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $tx->checkedOutToUser->name ?? '—' }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-end text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            {{ $tx->user->name ?? 'System' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                            No stock transactions recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
