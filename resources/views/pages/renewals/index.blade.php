@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Expiry & Renewal Tracker" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Track expiration dates for SSL certificates, domains, software licenses, and GA vehicle taxes.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    {{-- Unified Table/Card Container --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Card Header --}}
        <div class="space-y-4 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('renewals.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    <div class="relative flex items-center">
                        <select name="type" onchange="this.form.submit()"
                            class="h-10 rounded-lg border border-gray-300 bg-white px-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Types</option>
                            <option value="ssl_certificate" {{ request('type') == 'ssl_certificate' ? 'selected' : '' }}>SSL Certificate</option>
                            <option value="domain" {{ request('type') == 'domain' ? 'selected' : '' }}>Web Domain</option>
                            <option value="license" {{ request('type') == 'license' ? 'selected' : '' }}>Software License</option>
                            <option value="vehicle_tax" {{ request('type') == 'vehicle_tax' ? 'selected' : '' }}>GA Vehicle Tax</option>
                            <option value="vehicle_insurance" {{ request('type') == 'vehicle_insurance' ? 'selected' : '' }}>GA Vehicle Insurance</option>
                            <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom / Other</option>
                        </select>
                    </div>
                </form>

                <div class="flex items-center gap-3">
                    <a href="{{ route('renewals.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Add Renewal Item
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
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Item Name</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Type</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Expiration Date</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Responsible User</p>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Cost (Rp)</p>
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
                    @forelse($renewals as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6 font-medium text-gray-800 dark:text-white/90">
                                {{ $item->name }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-xs font-mono uppercase text-gray-500">
                                {{ str_replace('_', ' ', $item->type) }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-xs">
                                <span class="{{ $item->expiration_date->isPast() ? 'text-error-600 font-bold' : ($item->expiration_date->diffInDays(now()) <= $item->reminder_days_before ? 'text-warning-600 font-bold' : 'text-gray-600 dark:text-gray-400') }}">
                                    {{ $item->expiration_date->format('M d, Y') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-600 dark:text-gray-400">
                                {{ $item->responsibleUser->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($item->cost ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @php
                                    $color = match($item->status) {
                                        'active' => 'success',
                                        'expired' => 'error',
                                        default => 'warning',
                                    };
                                @endphp
                                <x-ui.badge :color="$color" variant="light" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-3 text-gray-500">
                                    <a href="{{ route('renewals.edit', $item) }}" class="transition-colors hover:text-brand-500" title="Edit">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </a>
                                    <form action="{{ route('renewals.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="transition-colors hover:text-error-500" title="Delete">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-gray-800/50">
                                        <i data-lucide="inbox" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No renewal items found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Add a new renewal item to start tracking expiration dates.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $renewals->links() }}
        </div>
    </div>
</div>
@endsection
