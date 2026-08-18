@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Barcode Label Generator" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Generate and print QR sticker labels for physical assets.
    </p>

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
        <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('labels.index') }}" method="GET" class="flex items-center gap-3">
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-gray-400">
                            <i data-feather="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, device name, serial..."
                            class="h-10 w-72 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('labels.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                    @endif
                </form>

                <div x-show="selectedRows.length > 0" x-transition class="flex items-center gap-3">
                    <span class="text-xs font-medium text-brand-600 dark:text-brand-400">
                        <span x-text="selectedRows.length"></span> asset(s) selected
                    </span>
                    <button type="button" @click="document.getElementById('generateForm').submit()"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        <i data-feather="printer" class="w-3.5 h-3.5"></i> Print Selected Labels
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <label class="flex cursor-pointer items-center select-none">
                                    <div class="relative">
                                        <input type="checkbox" :checked="selectAll" @click="handleSelectAll()" class="sr-only">
                                        <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                            class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                            <span :class="selectAll ? '' : 'opacity-0'">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset Tag</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Device Name</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Product</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Department</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Serial Number</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Copies</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center justify-end">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($assets as $asset)
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" value="{{ $asset->id }}" class="row-checkbox sr-only"
                                                :checked="selectedRows.includes({{ $asset->id }})" @click="handleRowSelect({{ $asset->id }})">
                                            <div :class="selectedRows.includes({{ $asset->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                <span :class="selectedRows.includes({{ $asset->id }}) ? '' : 'opacity-0'">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6 font-mono text-theme-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $asset->asset_tag }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $asset->device_name ?? $asset->name }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col">
                                    <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $asset->product?->brand?->name ?? '' }} {{ $asset->product?->model ?? '-' }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $asset->product?->category?->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @php
                                    $statusName = optional($asset->status)->name;
                                    $statusColor = optional($asset->status)->color ?? 'gray';
                                @endphp
                                <x-ui.badge :color="$statusColor" variant="light" size="sm" :featherIcon="optional($asset->status)->icon">{{ $statusName ?? 'Available' }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col text-theme-sm">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $asset->department->name ?? '-' }}</span>
                                    <span class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $asset->department->company->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6 font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $asset->serial_number ?? '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <input type="number" min="1" max="50" x-model.number="quantities[{{ $asset->id }}]" x-init="quantities[{{ $asset->id }}] = 1"
                                    class="w-16 rounded-lg border border-gray-300 bg-transparent px-2 py-1 text-center text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center justify-end gap-3 text-gray-500">
                                    <a :href="'{{ route('labels.print-single', $asset) }}?copies=' + (quantities[{{ $asset->id }}] || 1)" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition">
                                        <i data-feather="printer" class="w-4 h-4"></i> Print
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="tag" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No assets found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or add a new asset.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
