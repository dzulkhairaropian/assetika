@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Barcode Label Generator" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form action="{{ route('labels.index') }}" method="GET" class="flex items-center gap-3">
            <div class="relative flex items-center">
                <span class="absolute left-3 text-gray-400"><i data-feather="search" class="w-4 h-4"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, device name, serial..." class="h-10 w-72 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            @if(request()->filled('search'))
                <a href="{{ route('labels.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{
            selectedRows: [],
            quantities: {},
            selectAll: false,
            handleSelectAll() {
                this.selectAll = !this.selectAll;
                if (this.selectAll) {
                    this.selectedRows = {{ json_encode($assets->pluck('id')->toArray()) }};
                    this.selectedRows.forEach(id => {
                        if (!this.quantities[id]) this.quantities[id] = 1;
                    });
                } else {
                    this.selectedRows = [];
                }
            },
            handleRowSelect(id) {
                if (this.selectedRows.includes(id)) {
                    this.selectedRows = this.selectedRows.filter(r => r !== id);
                } else {
                    this.selectedRows.push(id);
                    if (!this.quantities[id]) this.quantities[id] = 1;
                }
                this.selectAll = this.selectedRows.length === {{ $assets->count() }};
            }
        }">

        <form id="generateForm" action="{{ route('labels.generate') }}" method="POST" target="_blank" class="hidden">
            @csrf
            <template x-for="id in selectedRows" :key="id">
                <div>
                    <input type="hidden" name="ids[]" :value="id">
                    <input type="hidden" :name="'quantities[' + id + ']'" :value="quantities[id] || 1">
                </div>
            </template>
        </form>

        <div x-show="selectedRows.length > 0" x-transition class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 m-4" style="display: none;">
            <div class="flex items-center gap-2">
                <i data-feather="check-square" class="w-4 h-4 text-brand-600"></i>
                <span class="text-sm font-medium text-brand-700"><span x-text="selectedRows.length"></span> asset(s) selected for printing.</span>
            </div>
            <button @click="document.getElementById('generateForm').submit()" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                <i data-feather="printer" class="w-3.5 h-3.5"></i> Print Selected Labels
            </button>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr class="text-xs uppercase font-semibold text-gray-500">
                        <th class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div @click="handleSelectAll()" class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]" :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                    <svg :class="selectAll ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14"><path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span>Asset Tag</span>
                            </div>
                        </th>
                        <th class="px-6 py-3.5">Device Name</th>
                        <th class="px-6 py-3.5">Serial Number</th>
                        <th class="px-6 py-3.5">Copies</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div @click="handleRowSelect({{ $asset->id }})" class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]" :class="selectedRows.includes({{ $asset->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                        <svg :class="selectedRows.includes({{ $asset->id }}) ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14"><path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200">{{ $asset->asset_tag }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                                {{ $asset->device_name ?? $asset->name }}
                            </td>
                            <td class="px-6 py-3.5 font-mono text-xs text-gray-500">
                                {{ $asset->serial_number ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5">
                                <input type="number" min="1" max="50" x-model.number="quantities[{{ $asset->id }}]" x-init="quantities[{{ $asset->id }}] = 1" class="w-16 rounded-md border border-gray-300 px-2 py-1 text-center text-xs font-semibold dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a :href="'{{ route('labels.print-single', $asset) }}?copies=' + (quantities[{{ $asset->id }}] || 1)" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:underline">
                                    <i data-feather="printer" class="w-3.5 h-3.5"></i> Print
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No assets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-gray-100 dark:border-gray-800">{{ $assets->links() }}</div>
    </div>
</div>
@endsection
