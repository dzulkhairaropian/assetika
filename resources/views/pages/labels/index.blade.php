@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Label Generator" />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form action="{{ route('labels.index') }}" method="GET" class="flex items-center gap-3">
            <div class="relative flex items-center"><span class="absolute left-3 text-gray-400"><i data-feather="search" class="w-4 h-4"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by tag, name, serial..." class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
            </div>
            @if(request()->filled('search')) <a href="{{ route('labels.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a> @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{ selectedRows: [], selectAll: false, handleSelectAll() { this.selectAll = !this.selectAll; this.selectedRows = this.selectAll ? {{ json_encode($assets->pluck('id')->toArray()) }} : []; }, handleRowSelect(id) { this.selectedRows.includes(id) ? this.selectedRows = this.selectedRows.filter(r => r !== id) : this.selectedRows.push(id); this.selectAll = this.selectedRows.length === {{ $assets->count() }}; } }">
        <form id="generateForm" action="{{ route('labels.generate') }}" method="POST" class="hidden">@csrf<template x-for="id in selectedRows" :key="id"><input type="hidden" name="ids[]" :value="id"></template></form>
        <div x-show="selectedRows.length &gt; 0" x-transition class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 m-4" style="display: none;">
            <span class="text-sm font-medium text-brand-700"><span x-text="selectedRows.length"></span> assets selected.</span>
            <button @click="document.getElementById('generateForm').submit()" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white hover:bg-brand-600">Generate Labels</button>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full">
                <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs text-start"><div class="flex items-center gap-3"><div @click="handleSelectAll()" class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]" :class="selectAll ? 'border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'"><svg :class="selectAll ? 'block' : 'hidden'" width="14" height="14"><path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Asset Tag</span></div></th>
                        <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs text-start">Name</th>
                        <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs text-start">Category</th>
                        <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs text-start">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($assets as $asset)
                        <tr>
                            <td class="px-4 sm:px-6 py-3.5"><div class="flex items-center gap-3"><div @click="handleRowSelect({{ $asset->id }})" class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]" :class="selectedRows.includes({{ $asset->id }}) ? 'border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'"><svg :class="selectedRows.includes({{ $asset->id }}) ? 'block' : 'hidden'" width="14" height="14"><path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span class="font-mono text-theme-sm font-medium">{{ $asset->asset_tag }}</span></div></td>
                            <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-700">{{ $asset->name }}</td>
                            <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-500">{{ $asset->product?->category?->name ?? '-' }}</td>
                            <td class="px-4 sm:px-6 py-3.5"><a href="{{ route('labels.print-single', $asset) }}" class="text-brand-500 hover:underline text-sm" target="_blank">Print</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No assets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-gray-100 dark:border-gray-800">{{ $assets->links() }}</div>
    </div>
</div>
@endsection
