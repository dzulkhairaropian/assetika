@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Activity Report" />
    <form action="{{ route('reports.activity') }}" method="GET" class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 items-end">
            <div><label class="mb-1 block text-xs font-medium">From</label><input type="date" name="from" value="{{ request('from') }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900"></div>
            <div><label class="mb-1 block text-xs font-medium">To</label><input type="date" name="to" value="{{ request('to') }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900"></div>
            <div><label class="mb-1 block text-xs font-medium">User</label><select name="user_id" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900"><option value="">All</option>@foreach($users as $u)<option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach</select></div>
            <div><button type="submit" class="h-10 w-full rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Filter</button></div>
        </div>
    </form>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-4 py-3 text-xs text-left text-gray-500">Date</th><th class="px-4 py-3 text-xs text-left text-gray-500">Asset</th><th class="px-4 py-3 text-xs text-left text-gray-500">User</th><th class="px-4 py-3 text-xs text-left text-gray-500">Action</th><th class="px-4 py-3 text-xs text-left text-gray-500">By</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                @forelse($activities as $a)
                <tr><td class="px-4 py-3 text-sm">{{ $a->checked_out_at?->format('d M Y') }}</td><td class="px-4 py-3 text-sm">{{ $a->asset?->name }}</td><td class="px-4 py-3 text-sm">{{ $a->checkedOutToUser?->name }}</td><td class="px-4 py-3 text-sm">{{ $a->checked_in_at ? 'Checkin' : 'Checkout' }}</td><td class="px-4 py-3 text-sm">{{ $a->checkedOutByUser?->name }}</td></tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No activity found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-6 border-t border-gray-100 dark:border-gray-800">{{ $activities->links() }}</div>
    </div>
</div>
@endsection
