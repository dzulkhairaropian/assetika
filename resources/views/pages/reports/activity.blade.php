@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Activity Audit Report" :items="[['label' => 'Reports', 'url' => route('reports.index')]]" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Detailed audit log of all asset checkouts, check-ins, and user assignment movements.
    </p>

    {{-- Unified Table Card --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Card Header: Filter Toolbar --}}
        <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            <form action="{{ route('reports.activity') }}" method="GET" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-3 flex-1">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Date From</label>
                        <input type="date" name="from" value="{{ request('from') }}"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Date To</label>
                        <input type="date" name="to" value="{{ request('to') }}"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Target User</label>
                        <div class="relative">
                            <select name="user_id"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                <option value="">All Users</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if(request()->anyFilled(['from', 'to', 'user_id']))
                        <a href="{{ route('reports.activity') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">Clear</a>
                    @endif
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                        <i data-feather="filter" class="w-4 h-4"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Target User</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Action</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Processed By</p>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($activities as $a)
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-600 dark:text-gray-400">
                                {{ $a->checked_out_at?->format('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if($a->asset)
                                    <a href="{{ route('assets.show', $a->asset) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 text-theme-sm">
                                        {{ $a->asset->asset_tag }} - {{ optional($a->asset->product)->model ?? $a->asset->device_name }}
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $a->checkedOutToUser?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if($a->checked_in_at)
                                    <x-ui.badge color="success" variant="light" size="sm" :startIcon="new \Illuminate\Support\HtmlString('<i data-feather=\'arrow-down-left\' class=\'w-3 h-3\'></i>')">
                                        Checked In
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge color="primary" variant="light" size="sm" :startIcon="new \Illuminate\Support\HtmlString('<i data-feather=\'arrow-up-right\' class=\'w-3 h-3\'></i>')">
                                        Checked Out
                                    </x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-500 dark:text-gray-400">
                                {{ $a->checkedOutByUser?->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="activity" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No activity logs found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your date range or user filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
