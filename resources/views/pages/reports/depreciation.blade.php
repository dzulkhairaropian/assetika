@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Depreciation Report" :items="[['label' => 'Reports', 'url' => route('reports.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Track straight-line asset depreciation, original purchase costs vs current book value.
    </p>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 min-w-0">
        <x-common.stat-card title="Total Purchase Cost" icon="dollar-sign" value="Rp {{ number_format($totalPurchase, 0, ',', '.') }}" description="Sum of original purchase cost" />
        <x-common.stat-card title="Total Current Book Value" icon="trending-up" value="Rp {{ number_format($totalBook, 0, ',', '.') }}" description="Current remaining book value" />
        <x-common.stat-card title="Total Depreciated Amount" icon="trending-down" value="Rp {{ number_format($totalPurchase - $totalBook, 0, ',', '.') }}" description="Accumulated depreciation" />
    </div>

    {{-- Unified Table Card --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-feather="trending-down" class="w-4 h-4 text-purple-500"></i>
                Depreciation Schedule
            </h3>
            <span class="text-xs text-gray-400 font-medium">{{ $assets->total() }} assets tracked</span>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Purchase Cost</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Depreciation Profile</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Term (Months)</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Book Value</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Depreciated Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($assets as $a)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                <a href="{{ route('assets.show', $a) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                    {{ $a->asset_tag }} - {{ optional($a->product->brand)->name ?? '' }} {{ optional($a->product)->model ?? $a->name }}
                                </a>
                            </td>
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                Rp {{ number_format($a->purchase_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                {{ $a->depreciation?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                {{ $a->depreciation?->months ? $a->depreciation->months . ' mo' : '-' }}
                            </td>
                            <td class="px-4 py-2.5 font-semibold text-brand-600 dark:text-brand-400">
                                Rp {{ number_format($a->bookValue, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 font-semibold text-red-600 dark:text-red-400">
                                Rp {{ number_format($a->purchase_cost - $a->bookValue, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-1.5">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="trending-down" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No assets with depreciation profiles found</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Assign depreciation profiles to assets to track book values.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
