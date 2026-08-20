@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Depreciation Report" :items="[['label' => 'Reports', 'url' => route('reports.index')]]" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Track straight-line asset depreciation, original purchase costs vs current book value.
    </p>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-common.stat-card title="Total Purchase Cost" value="Rp {{ number_format($totalPurchase, 0, ',', '.') }}" description="Sum of original purchase cost" />
        <x-common.stat-card title="Total Current Book Value" value="Rp {{ number_format($totalBook, 0, ',', '.') }}" description="Current remaining book value" />
        <x-common.stat-card title="Total Depreciated Amount" value="Rp {{ number_format($totalPurchase - $totalBook, 0, ',', '.') }}" description="Accumulated depreciation" />
    </div>

    {{-- Unified Table Card --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                <i data-feather="trending-down" class="w-5 h-5 text-purple-500"></i>
                Depreciation Schedule
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $assets->total() }} assets tracked</span>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Purchase Cost</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Depreciation Profile</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Term (Months)</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Book Value</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Depreciated Amount</p>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($assets as $a)
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6">
                                <a href="{{ route('assets.show', $a) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 text-theme-sm">
                                    {{ $a->asset_tag }} - {{ optional($a->product->brand)->name ?? '' }} {{ optional($a->product)->model ?? $a->name }}
                                </a>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                Rp {{ number_format($a->purchase_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-600 dark:text-gray-400">
                                {{ $a->depreciation?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-600 dark:text-gray-400">
                                {{ $a->depreciation?->months ? $a->depreciation->months . ' mo' : '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm font-semibold text-brand-500">
                                Rp {{ number_format($a->bookValue, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm font-semibold text-error-500">
                                Rp {{ number_format($a->purchase_cost - $a->bookValue, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="trending-down" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No assets with depreciation profiles found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Assign depreciation profiles to assets to track book values.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
