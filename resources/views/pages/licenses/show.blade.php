@extends('layouts.app')

@section('content')
    @php
        $breadcrumbLabel = $license->name;
        $breadcrumbItems = [
            ['label' => 'Licenses', 'url' => route('licenses.index')],
            ['label' => $breadcrumbLabel],
        ];
    @endphp

    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="License Detail" :items="$breadcrumbItems" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            View software or appliance license seats, keys, and assigned users/hardware.
        </p>

        @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
        @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <i data-lucide="key" class="w-5 h-5 text-brand-500"></i> License Info
                </h3>
                <dl class="space-y-3">
                    <div><dt class="text-[11px] font-medium text-gray-400">Name</dt><dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $license->name }}</dd></div>
                    <div><dt class="text-[11px] font-medium text-gray-400">Product Key</dt><dd class="text-xs font-mono font-medium text-gray-800 dark:text-gray-200">{{ $license->product_key ?? '-' }}</dd></div>
                    <div><dt class="text-[11px] font-medium text-gray-400">Seats</dt><dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $license->seats }} total / <span class="{{ $license->availableSeats() <= 0 ? 'text-red-600 dark:text-red-400' : 'text-brand-600 dark:text-brand-400' }}">{{ $license->availableSeats() }} available</span></dd></div>
                    <div><dt class="text-[11px] font-medium text-gray-400">Manufacturer</dt><dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $license->manufacturer ?? '-' }}</dd></div>
                    <div><dt class="text-[11px] font-medium text-gray-400">Purchase Date</dt><dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $license->purchase_date?->format('d M Y') ?? '-' }}</dd></div>
                    <div><dt class="text-[11px] font-medium text-gray-400">Expiration</dt><dd class="text-xs font-medium {{ $license->expiration_date?->isPast() ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-800 dark:text-gray-200' }}">{{ $license->expiration_date?->format('d M Y') ?? '-' }}</dd></div>
                    <div><dt class="text-[11px] font-medium text-gray-400">Cost</dt><dd class="text-xs font-semibold text-gray-900 dark:text-white">Rp {{ number_format($license->purchase_cost, 0, ',', '.') ?: '-' }}</dd></div>
                </dl>
                <div class="mt-6 flex gap-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('licenses.edit', $license) }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit</a>
                    <a href="{{ route('licenses.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">Back</a>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <i data-lucide="users" class="w-5 h-5 text-brand-500"></i> Seat Assignments
                </h3>

                @if($license->availableSeats() > 0)
                <form action="{{ route('licenses.checkout', $license) }}" method="POST" class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950 mb-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Assign To Type</label>
                            <select name="assigned_to_type" class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                <option value="user">User</option>
                                <option value="asset">Asset / Hardware</option>
                            </select>
                        </div>
                        <div>
                            <x-form.searchable-select name="assigned_to_id" label="Target" :options="$userOpts" placeholder="Select User" required="true" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Notes (Optional)</label>
                        <input type="text" name="notes" class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200" placeholder="e.g. Primary office laptop seat">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-1.5 h-9 rounded-lg bg-brand-500 px-4 text-xs font-semibold text-white hover:bg-brand-600 transition shadow-sm">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Assign Seat
                        </button>
                    </div>
                </form>
                @else
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-xs font-medium text-red-700 mb-6 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> All seats are currently assigned.
                </div>
                @endif

                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Current Active Assignments</h4>
                @if($license->seats()->exists())
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50/50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th class="px-4 py-2.5 text-start font-medium text-gray-500">Assigned To</th>
                                    <th class="px-4 py-2.5 text-start font-medium text-gray-500">Type</th>
                                    <th class="px-4 py-2.5 text-start font-medium text-gray-500">Date</th>
                                    <th class="px-4 py-2.5 text-end font-medium text-gray-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($license->seats as $seat)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $seat->assignedTo?->name ?? $seat->assignedTo?->asset_tag ?? 'N/A' }}</td>
                                        <td class="px-4 py-2.5 text-gray-500 text-[11px]">{{ class_basename($seat->assigned_to_type) }}</td>
                                        <td class="px-4 py-2.5 text-gray-500">{{ $seat->checked_out_at?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-4 py-2.5 text-end">
                                            <form action="{{ route('licenses.checkin', ['license' => $license, 'seat' => $seat]) }}" method="POST" class="inline-flex">
                                                @csrf
                                                <button class="rounded-lg bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100 dark:bg-orange-500/10 dark:text-orange-400 transition">Checkin</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-6 text-center dark:border-gray-800 dark:bg-gray-950"><p class="text-xs text-gray-400">No seats assigned yet.</p></div>
                @endif
            </div>
        </div>
    </div>
@endsection
