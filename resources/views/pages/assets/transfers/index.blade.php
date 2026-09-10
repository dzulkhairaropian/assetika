@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Asset Transfers" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Track and manage inter-company/department asset transfers.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger" message="{{ session('error') }}" />
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('asset-transfers.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search asset tag/name..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>

                        <select name="status" onchange="this.form.submit()"
                            class="h-9 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>

                        @if(request()->filled('search') || request()->filled('status'))
                            <a href="{{ route('asset-transfers.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Clear</a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('asset-transfers.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-lucide="repeat" class="w-3.5 h-3.5"></i>
                            Transfer Asset
                        </a>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">From</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">To</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Requested By</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($transfers as $transfer)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                    <div>{{ $transfer->asset->name ?? '-' }}</div>
                                    <div class="text-[11px] font-mono text-gray-400">{{ $transfer->asset->asset_tag ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    <div>{{ $transfer->fromCompany->name ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $transfer->fromDepartment->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    <div>{{ $transfer->toCompany->name ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $transfer->toDepartment->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ $transfer->requestedBy->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($transfer->status === 'approved')
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Approved</span>
                                    @elseif($transfer->status === 'rejected')
                                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">Rejected</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    @if($transfer->status === 'pending')
                                        <div class="flex items-center justify-end gap-1">
                                            <form action="{{ route('asset-transfers.approve', $transfer) }}" method="POST" onsubmit="return confirm('Approve asset transfer?')">
                                                @csrf
                                                <button type="submit" class="rounded bg-emerald-500 px-2 py-1 text-[11px] font-medium text-white hover:bg-emerald-600">Approve</button>
                                            </form>
                                            <form action="{{ route('asset-transfers.reject', $transfer) }}" method="POST" onsubmit="return confirm('Reject asset transfer?')">
                                                @csrf
                                                <button type="submit" class="rounded bg-rose-500 px-2 py-1 text-[11px] font-medium text-white hover:bg-rose-600">Reject</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                    No asset transfers recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
@endsection
