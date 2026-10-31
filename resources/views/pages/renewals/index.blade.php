@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Expiry & Renewal Tracker" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Track expiration dates for SSL certificates, domains, software licenses, and GA vehicle taxes.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Main Table Container (Exact Asset Index Layout) --}}
        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('renewals.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <select name="type" onchange="this.form.submit()"
                                class="h-9 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                <option value="">All Renewal Types</option>
                                <option value="ssl_certificate" {{ request('type') == 'ssl_certificate' ? 'selected' : '' }}>SSL Certificate</option>
                                <option value="domain" {{ request('type') == 'domain' ? 'selected' : '' }}>Web Domain</option>
                                <option value="license" {{ request('type') == 'license' ? 'selected' : '' }}>Software License</option>
                                <option value="vehicle_tax" {{ request('type') == 'vehicle_tax' ? 'selected' : '' }}>GA Vehicle Tax</option>
                                <option value="vehicle_insurance" {{ request('type') == 'vehicle_insurance' ? 'selected' : '' }}>GA Vehicle Insurance</option>
                                <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom / Other</option>
                            </select>
                        </div>

                        @if(request()->filled('type'))
                            <a href="{{ route('renewals.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('renewals.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Renewal Item
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Item Name</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Renewal Scope / Type</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Expiration Target</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Responsible User</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Renewal Cost</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($renewals as $item)
                            @php
                                $color = match($item->status) {
                                    'active' => 'success',
                                    'expired' => 'danger',
                                    default => 'warning',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-4 py-2.5 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                    {{ $item->name }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-[10px] font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400 uppercase font-mono">
                                        {{ str_replace('_', ' ', $item->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap font-mono">
                                    <span class="{{ $item->expiration_date->isPast() ? 'text-rose-600 font-bold' : ($item->expiration_date->diffInDays(now()) <= $item->reminder_days_before ? 'text-amber-600 font-bold' : 'text-gray-700 dark:text-gray-300') }}">
                                        {{ $item->expiration_date->format('d M Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap font-medium text-gray-800 dark:text-gray-200">
                                    {{ $item->responsibleUser->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap font-mono text-gray-700 dark:text-gray-300">
                                    {{ $item->cost ? 'Rp ' . number_format($item->cost, 0, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <x-ui.badge :color="$color" variant="light" size="sm">
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('renewals.edit', $item) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="Edit Renewal">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <i data-lucide="calendar" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No Renewals Found</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        No upcoming domain, license, or vehicle tax renewals match your query.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $renewals->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
