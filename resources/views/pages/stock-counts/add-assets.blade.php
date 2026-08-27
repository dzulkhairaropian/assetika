@extends('layouts.app')
@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Add Assets to Stock Count" :items="[['label' => 'Stock Audits', 'url' => route('stock-counts.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Select assets to include in audit session {{ $stockCount->name }}.
    </p>

    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('stock-counts.process-add-assets', $stockCount) }}" method="POST">
            @csrf
            <h4 class="text-xs font-semibold text-gray-900 dark:text-white mb-2">Select Assets to Audit</h4>
            @php $allAssets = \App\Models\Asset::orderBy('asset_tag')->get(); @endphp
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 max-h-96 overflow-y-auto mb-4">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50/50 dark:bg-gray-950 sticky top-0 border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-start font-medium text-gray-500 w-12">Select</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-500">Tag</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-500">Name</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($allAssets as $asset)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-3 py-1.5"><input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" class="rounded text-brand-500"></td>
                            <td class="px-3 py-1.5 font-mono font-medium text-gray-900 dark:text-white">{{ $asset->asset_tag }}</td>
                            <td class="px-3 py-1.5 text-gray-800 dark:text-gray-200">{{ $asset->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-gray-100 dark:border-gray-800">
                <a href="{{ route('stock-counts.show', $stockCount) }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">Add Selected</button>
            </div>
        </form>
    </div>
</div>
@endsection
