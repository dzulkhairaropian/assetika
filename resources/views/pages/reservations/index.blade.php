@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Asset Reservations" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Manage temporary asset borrowing schedules and requests.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    {{-- Unified Table/Card Container --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Card Header --}}
        <div class="space-y-4 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div></div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('reservations.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                        <i data-feather="plus" class="w-4 h-4"></i>
                        New Reservation
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requester</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Reservation Time</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Purpose</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6 text-right">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($reservations as $res)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6 font-medium text-gray-800 dark:text-white/90">
                                {{ $res->reservable->name ?? 'Asset' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-600 dark:text-gray-400">
                                {{ $res->user->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-xs text-gray-600 dark:text-gray-400">
                                <div>{{ $res->start_time->format('M d, Y H:i') }}</div>
                                <div class="text-gray-400">to {{ $res->end_time->format('M d, Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                {{ $res->purpose }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @php
                                    $color = match($res->status) {
                                        'approved' => 'success',
                                        'checked_out' => 'primary',
                                        'completed' => 'success',
                                        'rejected' => 'error',
                                        default => 'warning',
                                    };
                                @endphp
                                <x-ui.badge :color="$color" variant="light" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $res->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-right">
                                @if($res->status == 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('reservations.approve', $res) }}">
                                            @csrf
                                            <button type="submit" class="rounded-md bg-success-500 px-2.5 py-1 text-xs font-medium text-white hover:bg-success-600 transition">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('reservations.reject', $res) }}">
                                            @csrf
                                            <button type="submit" class="rounded-md bg-error-500 px-2.5 py-1 text-xs font-medium text-white hover:bg-error-600 transition">Reject</button>
                                        </form>
                                    </div>
                                @elseif($res->status == 'approved')
                                    <span class="text-xs text-brand-500 font-medium">Scheduled for Auto Checkout</span>
                                @elseif($res->status == 'checked_out')
                                    <span class="text-xs text-success-500 font-medium">Active (Auto Checkin Pending)</span>
                                @else
                                    <span class="text-xs text-gray-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-gray-800/50">
                                        <i data-feather="inbox" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No asset reservations found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Create a new reservation request to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $reservations->links() }}
        </div>
    </div>
</div>
@endsection
