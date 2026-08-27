@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Barcode Label Generator" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Generate and print QR sticker labels for physical assets.
    </p>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
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

        <form id="generateForm" action="{{ route('labels.generate', [], false) }}" method="POST" target="_blank" class="hidden">
            @csrf
            <template x-for="id in selectedRows" :key="id">
                <div>
                    <input type="hidden" name="ids[]" :value="id">
                    <input type="hidden" :name="'quantities[' + id + ']'" :value="quantities[id] || 1">
                </div>
            </template>
        </form>

        {{-- Card Header: Toolbar --}}
        <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('labels.index') }}" method="GET" class="flex items-center gap-2">
                    <div class="relative flex items-center">
                        <span class="absolute left-2.5 text-gray-400">
                            <i data-feather="search" class="w-3.5 h-3.5"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, device name, serial..."
                            class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('labels.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Clear</a>
                    @endif
                </form>

                <div x-show="selectedRows.length > 0" x-transition class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">
                        <span x-text="selectedRows.length"></span> asset(s) selected
                    </span>
                    <button type="button" @click="document.getElementById('generateForm').submit()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="printer" class="w-3.5 h-3.5"></i> Print Selected Labels
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">
                            <label class="flex cursor-pointer items-center select-none">
                                <div class="relative">
                                    <input type="checkbox" :checked="selectAll" @click="handleSelectAll()" class="sr-only">
                                    <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                        class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                        <span :class="selectAll ? '' : 'opacity-0'">
                                            <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset Tag</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Device / Model</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Serial Number</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Copies</th>
                        <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Print</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5">
                                <label class="flex cursor-pointer items-center select-none">
                                    <div class="relative">
                                        <input type="checkbox" :checked="selectedRows.includes({{ $asset->id }})" @click="handleRowSelect({{ $asset->id }})" class="sr-only">
                                        <div :class="selectedRows.includes({{ $asset->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                            class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                            <span :class="selectedRows.includes({{ $asset->id }}) ? '' : 'opacity-0'">
                                                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </td>
                            <td class="px-4 py-2.5 font-mono font-semibold text-gray-900 dark:text-white">
                                {{ $asset->asset_tag }}
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $asset->device_name }}</span>
                                    <span class="text-[11px] text-gray-400">{{ optional($asset->product?->brand)->name }} {{ optional($asset->product)->model }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 font-mono text-gray-600 dark:text-gray-400">
                                {{ $asset->serial_number ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                {{ optional($asset->product?->category)->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5">
                                <x-ui.badge :color="optional($asset->status)->color" variant="light" size="sm">{{ optional($asset->status)->name ?? 'Draft' }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" min="1" max="10" x-model.number="quantities[{{ $asset->id }}]"
                                    @focus="if(!selectedRows.includes({{ $asset->id }})) handleRowSelect({{ $asset->id }})"
                                    class="h-7 w-14 rounded border border-gray-200 bg-gray-50 px-1.5 text-center text-xs font-semibold text-gray-800 focus:border-brand-500 focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            </td>
                            <td class="px-4 py-2.5 text-end">
                                <a href="{{ route('labels.print-single', $asset) }}" target="_blank"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                                    <i data-feather="printer" class="w-3.5 h-3.5 text-brand-500"></i> Print
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-xs text-gray-400">
                                No assets found for label printing.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
