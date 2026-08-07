@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Depreciation Report" />
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><div class="text-xs text-gray-500">Total Purchase Cost</div><div class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalPurchase, 0, ',', '.') }}</div></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><div class="text-xs text-gray-500">Total Book Value</div><div class="text-2xl font-bold text-brand-500">Rp {{ number_format($totalBook, 0, ',', '.') }}</div></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><div class="text-xs text-gray-500">Total Depreciated</div><div class="text-2xl font-bold text-error-500">Rp {{ number_format($totalPurchase - $totalBook, 0, ',', '.') }}</div></div>
    </div>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto"><table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-4 py-3 text-xs text-left text-gray-500">Asset</th><th class="px-4 py-3 text-xs text-left text-gray-500">Purchase Cost</th><th class="px-4 py-3 text-xs text-left text-gray-500">Profile</th><th class="px-4 py-3 text-xs text-left text-gray-500">Months</th><th class="px-4 py-3 text-xs text-left text-gray-500">Book Value</th><th class="px-4 py-3 text-xs text-left text-gray-500">Depreciated</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                @forelse($assets as $a)
                <tr><td class="px-4 py-3 text-sm">{{ $a->name }}</td><td class="px-4 py-3 text-sm">Rp {{ number_format($a->purchase_cost, 0, ',', '.') }}</td><td class="px-4 py-3 text-sm">{{ $a->depreciation?->name }}</td><td class="px-4 py-3 text-sm">{{ $a->depreciation?->months }} mo</td><td class="px-4 py-3 text-sm font-semibold text-brand-500">Rp {{ number_format($a->bookValue, 0, ',', '.') }}</td><td class="px-4 py-3 text-sm text-error-500">Rp {{ number_format($a->purchase_cost - $a->bookValue, 0, ',', '.') }}</td></tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No assets with depreciation profiles found.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="p-6 border-t border-gray-100 dark:border-gray-800">{{ $assets->links() }}</div>
    </div>
</div>
@endsection
