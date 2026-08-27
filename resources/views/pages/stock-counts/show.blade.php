@extends('layouts.app')
@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Stock Count Detail" :items="[['label' => 'Stock Audits', 'url' => route('stock-counts.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Review audit findings, verified assets, and status reconciliations.
    </p>
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3 dark:border-gray-800">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $stockCount->name }}</h3>
                <p class="text-xs text-gray-400">Location: {{ $stockCount->location?->name ?? 'All Locations' }} | Status: {{ ucfirst($stockCount->status) }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($stockCount->status !== 'completed')
                    <a href="{{ route('stock-counts.add-assets', $stockCount) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition"><i data-feather="plus" class="w-3.5 h-3.5"></i> Add Assets</a>
                    <form action="{{ route('stock-counts.complete', $stockCount) }}" method="POST" class="inline-flex">@csrf<button class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-green-700 transition">Mark Completed</button></form>
                @endif
                <a href="{{ route('stock-counts.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">Back</a>
            </div>
        </div>

        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Audit Items ({{ $stockCount->items->count() }})</h4>
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
            <table class="w-full text-xs">
                <thead class="bg-gray-50/50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-800">
                    <tr><th class="px-4 py-2.5 text-start font-medium text-gray-500">Asset</th><th class="px-4 py-2.5 text-start font-medium text-gray-500">Expected</th><th class="px-4 py-2.5 text-start font-medium text-gray-500">Verification Result</th><th class="px-4 py-2.5 text-start font-medium text-gray-500">Verified By</th><th class="px-4 py-2.5 text-end font-medium text-gray-500">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($stockCount->items as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-2.5"><span class="font-mono text-xs font-bold text-gray-900 dark:text-white">{{ $item->asset?->asset_tag }}</span> - {{ $item->asset?->name }}</td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ ucfirst($item->expected_status) }}</td>
                            <td class="px-4 py-2.5">
                                @if($item->found_status)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium {{ $item->found_status === 'found' ? 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">{{ ucfirst($item->found_status) }}</span>
                                @else
                                    <span class="text-gray-400 italic">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ $item->verifiedBy?->name ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-end">
                                @if($stockCount->status !== 'completed')
                                    <form action="{{ route('stock-counts.items.verify', ['stockCount' => $stockCount, 'item' => $item]) }}" method="POST" class="inline-flex gap-1 justify-end">
                                        @csrf
                                        <button name="found_status" value="found" class="rounded bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400 transition">Found</button>
                                        <button name="found_status" value="missing" class="rounded bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 transition">Missing</button>
                                        <button name="found_status" value="damaged" class="rounded bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100 dark:bg-orange-500/10 dark:text-orange-400 transition">Damaged</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-xs text-gray-400">No assets added to this stock count yet. Click "Add Assets" above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
