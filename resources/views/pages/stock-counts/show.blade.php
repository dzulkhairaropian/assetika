@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Stock Count Detail" />
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold">{{ $stockCount->name }}</h3>
                <p class="text-xs text-gray-500">Location: {{ $stockCount->location?->name ?? 'All' }} | Status: {{ ucfirst($stockCount->status) }}</p>
            </div>
            <div class="flex gap-2">
                @if($stockCount->status !== 'completed')
                    <a href="{{ route('stock-counts.add-assets', $stockCount) }}" class="inline-flex items-center gap-1 rounded bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600"><i data-feather="plus" class="w-4 h-4"></i> Add Assets</a>
                    <form action="{{ route('stock-counts.complete', $stockCount) }}" method="POST" class="inline-flex">@csrf<button class="inline-flex items-center gap-1 rounded bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">Mark Completed</button></form>
                @endif
                <a href="{{ route('stock-counts.index') }}" class="inline-flex items-center gap-1 rounded border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Back</a>
            </div>
        </div>

        <h4 class="text-sm font-semibold mb-3">Audit Items ({{ $stockCount->items->count() }})</h4>
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr><th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Asset</th><th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Expected</th><th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Verification Result</th><th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Verified By</th><th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($stockCount->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm"><span class="font-mono text-xs font-bold">{{ $item->asset?->asset_tag }}</span> - {{ $item->asset?->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ ucfirst($item->expected_status) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($item->found_status)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $item->found_status === 'found' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst($item->found_status) }}</span>
                                @else
                                    <span class="text-gray-400 italic">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $item->verifiedBy?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($stockCount->status !== 'completed')
                                    <form action="{{ route('stock-counts.items.verify', ['stockCount' => $stockCount, 'item' => $item]) }}" method="POST" class="inline-flex gap-1">
                                        @csrf
                                        <button name="found_status" value="found" class="rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-700 hover:bg-green-200">Found</button>
                                        <button name="found_status" value="missing" class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200">Missing</button>
                                        <button name="found_status" value="damaged" class="rounded bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 hover:bg-amber-200">Damaged</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No assets added to this stock count yet. Click "Add Assets" above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
