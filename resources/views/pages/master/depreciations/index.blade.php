@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Depreciations" />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form action="{{ route('depreciations.index') }}" method="GET" class="flex items-center gap-3">
            <div class="relative flex items-center"><span class="absolute left-3 text-gray-400"><i data-feather="search" class="w-4 h-4"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
            </div>
            @if(request()->filled('search')) <a href="{{ route('depreciations.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a> @endif
        </form>
        <a href="{{ route('depreciations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"><i data-feather="plus" class="w-4 h-4"></i> Add Depreciation</a>
    </div>
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{ selectedRows: [], selectAll: false, handleSelectAll() { this.selectAll = !this.selectAll; this.selectedRows = this.selectAll ? {{ json_encode($depreciations->pluck('id')->toArray()) }} : []; }, handleRowSelect(id) { this.selectedRows.includes(id) ? this.selectedRows = this.selectedRows.filter(r => r !== id) : this.selectedRows.push(id); this.selectAll = this.selectedRows.length === {{ $depreciations->count() }}; } }">
        <form id="bulkDeleteForm" action="{{ route('depreciations.bulk-delete') }}" method="POST" class="hidden">@csrf<template x-for="id in selectedRows" :key="id"><input type="hidden" name="ids[]" :value="id"></template></form>
        <div x-show="selectedRows.length &gt; 0" x-transition class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 m-4" style="display: none;">
            <span class="text-sm font-medium text-brand-700"><span x-text="selectedRows.length"></span> items selected.</span>
            <button @click="if (confirm('Delete selected?')) { document.getElementById('bulkDeleteForm').submit(); }" class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white hover:bg-error-600">Delete Selected</button>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full">
                <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs text-start"><div class="flex items-center gap-3"><div @click="handleSelectAll()" class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]" :class="selectAll ? 'border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'"><svg :class="selectAll ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14"><path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Name</span></div></th>
                        <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs text-start">Months</th>
                        <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs text-start">Rate / Month</th>
                        <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs text-start">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($depreciations as $d)
                        <tr>
                            <td class="px-4 sm:px-6 py-3.5"><div class="flex items-center gap-3"><div @click="handleRowSelect({{ $d->id }})" class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]" :class="selectedRows.includes({{ $d->id }}) ? 'border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'"><svg :class="selectedRows.includes({{ $d->id }}) ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14"><path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $d->name }}</span></div></td>
                            <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-700">{{ $d->months }} mo</td>
                            <td class="px-4 sm:px-6 py-3.5 text-theme-sm text-gray-500">100 / {{ $d->months }} = {{ round(100 / $d->months, 2) }}%</td>
                            <td class="px-4 sm:px-6 py-3.5"><div class="flex items-center gap-3 text-gray-500"><a href="{{ route('depreciations.edit', $d) }}" class="hover:text-brand-500"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a><form action="{{ route('depreciations.destroy', $d) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">@csrf @method('DELETE')<button class="hover:text-error-500"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No depreciations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-gray-100 dark:border-gray-800">{{ $depreciations->links() }}</div>
    </div>
</div>
@endsection
