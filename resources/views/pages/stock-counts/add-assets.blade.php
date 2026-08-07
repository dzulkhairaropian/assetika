@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Add Assets to Stock Count" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <form action="{{ route('stock-counts.process-add-assets', $stockCount) }}" method="POST">@csrf
            <h4 class="text-sm font-semibold mb-3">Select Assets to Audit</h4>
            @php $allAssets = \App\Models\Asset::orderBy('asset_tag')->get(); @endphp
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto mb-6">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0"><tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-12">Select</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tag</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Name</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @foreach($allAssets as $asset)
                        <tr>
                            <td class="px-4 py-2"><input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" class="rounded text-brand-500"></td>
                            <td class="px-4 py-2 text-sm font-mono">{{ $asset->asset_tag }}</td>
                            <td class="px-4 py-2 text-sm">{{ $asset->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('stock-counts.show', $stockCount) }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Add Selected</button>
            </div>
        </form>
    </div>
</div>
@endsection
