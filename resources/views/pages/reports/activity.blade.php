@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Activity Audit Report" :items="[['label' => 'Reports', 'url' => route('reports.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Detailed audit log of all asset checkouts, check-ins, and user assignment movements.
    </p>

    {{-- Unified Table Card --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        {{-- Card Header: Filter Toolbar --}}
        <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
            <form action="{{ route('reports.activity') }}" method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-3 flex-1 text-left">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Date From</label>
                        <input type="date" name="from" value="{{ request('from') }}"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Date To</label>
                        <input type="date" name="to" value="{{ request('to') }}"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Target User</label>
                        <select name="user_id"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if(request()->anyFilled(['from', 'to', 'user_id']))
                        <a href="{{ route('reports.activity') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 transition">Clear</a>
                    @endif
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="filter" class="w-3.5 h-3.5"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Target User</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Action</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Processed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($activities as $a)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                {{ $a->checked_out_at?->format('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5">
                                @if($a->asset)
                                    <a href="{{ route('assets.show', $a->asset) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                        {{ $a->asset->asset_tag }} - {{ optional($a->asset->product)->model ?? $a->asset->device_name }}
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                {{ $a->checkedOutToUser?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5">
                                @if($a->checked_in_at)
                                    <x-ui.badge color="success" variant="light" size="sm">Check-in</x-ui.badge>
                                @else
                                    <x-ui.badge color="primary" variant="light" size="sm">Check-out</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                {{ $a->checkedOutByUser?->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">
                                No activity audit records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
