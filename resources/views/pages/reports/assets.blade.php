@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Asset Report" />
    <form action="{{ route('reports.assets') }}" method="GET" class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 items-end">
            <div><label class="mb-1 block text-xs font-medium">Search</label><input type="text" name="search" value="{{ request('search') }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900"></div>
            <div><label class="mb-1 block text-xs font-medium">Status</label><select name="status_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900"><option value="">All</option>@foreach($statuses as $s)<option value="{{ $s->id }}" {{ request('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-xs font-medium">Department</label><select name="department_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900"><option value="">All</option>@foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach</select></div>
            <div><button type="submit" class="h-10 w-full rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Filter</button></div>
        </div>
    </form>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto"><table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-4 py-3 text-xs text-left text-gray-500">Tag</th><th class="px-4 py-3 text-xs text-left text-gray-500">Name</th><th class="px-4 py-3 text-xs text-left text-gray-500">Category</th><th class="px-4 py-3 text-xs text-left text-gray-500">Status</th><th class="px-4 py-3 text-xs text-left text-gray-500">Dept</th><th class="px-4 py-3 text-xs text-left text-gray-500">Location</th><th class="px-4 py-3 text-xs text-left text-gray-500">Cost</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                @forelse($assets as $a)
                <tr><td class="px-4 py-3 text-sm font-mono">{{ $a->asset_tag }}</td><td class="px-4 py-3 text-sm">{{ $a->name }}</td><td class="px-4 py-3 text-sm text-gray-500">{{ $a->product?->category?->name }}</td><td class="px-4 py-3 text-sm">{{ $a->status?->name }}</td><td class="px-4 py-3 text-sm">{{ $a->department?->company?->code }} {{ $a->department?->name }}</td><td class="px-4 py-3 text-sm">{{ $a->location?->name }}</td><td class="px-4 py-3 text-sm">Rp {{ number_format($a->purchase_cost, 0, ',', '.') }}</td></tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No assets found.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="p-6 border-t border-gray-100 dark:border-gray-800">{{ $assets->links() }}</div>
    </div>
</div>
@endsection
