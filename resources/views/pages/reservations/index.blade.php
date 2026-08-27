@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Asset Reservations" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Manage temporary asset borrowing schedules and requests.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    {{-- Unified Table/Card Container --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        {{-- Card Header --}}
        <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div></div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reservations.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="plus" class="w-3.5 h-3.5"></i>
                        New Reservation
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Requester</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Reservation Time</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Purpose</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($reservations as $res)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white">
                                {{ $res->reservable->name ?? 'Asset' }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 font-medium">
                                {{ $res->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                <div>{{ $res->start_time->format('M d, Y H:i') }}</div>
                                <div class="text-[11px] text-gray-400">to {{ $res->end_time->format('M d, Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                {{ $res->purpose }}
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $color = match($res->status) {
                                        'approved' => 'success',
                                        'checked_out' => 'primary',
                                        'completed' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning',
                                    };
                                @endphp
                                <x-ui.badge :color="$color" variant="light" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $res->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5 text-end">
                                @if($res->status == 'pending')
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('reservations.approve', $res) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700 transition">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('reservations.reject', $res) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-red-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-red-700 transition">Reject</button>
                                        </form>
                                    </div>
                                @elseif($res->status == 'approved')
                                    <form method="POST" action="{{ route('reservations.checkout', $res) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-brand-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-brand-600 transition">Checkout</button>
                                    </form>
                                @elseif($res->status == 'checked_out')
                                    <form method="POST" action="{{ route('reservations.checkin', $res) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-orange-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-orange-600 transition">Checkin</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-1.5">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="calendar" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No reservations found</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Create a reservation request to temporarily borrow equipment.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
            {{ $reservations->links() }}
        </div>
    </div>
</div>
@endsection
