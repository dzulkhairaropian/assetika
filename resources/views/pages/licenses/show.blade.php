@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="License Detail" :items="[['label' => 'Licenses', 'url' => route('licenses.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        View software or appliance license seats, keys, and assigned users/hardware.
    </p>

    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <i data-feather="key" class="w-4 h-4 text-brand-500"></i> License Info
            </h3>
            <dl class="space-y-2.5">
                <div><dt class="text-[11px] font-medium text-gray-400">Name</dt><dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $license->name }}</dd></div>
                <div><dt class="text-[11px] font-medium text-gray-400">Product Key</dt><dd class="text-xs font-mono font-medium text-gray-800 dark:text-gray-200">{{ $license->product_key ?? '-' }}</dd></div>
                <div><dt class="text-[11px] font-medium text-gray-400">Seats</dt><dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $license->seats }} total / <span class="{{ $license->availableSeats() <= 0 ? 'text-red-600 dark:text-red-400' : 'text-brand-600 dark:text-brand-400' }}">{{ $license->availableSeats() }} available</span></dd></div>
                <div><dt class="text-[11px] font-medium text-gray-400">Manufacturer</dt><dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $license->manufacturer ?? '-' }}</dd></div>
                <div><dt class="text-[11px] font-medium text-gray-400">Purchase Date</dt><dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $license->purchase_date?->format('d M Y') ?? '-' }}</dd></div>
                <div><dt class="text-[11px] font-medium text-gray-400">Expiration</dt><dd class="text-xs font-medium {{ $license->expiration_date?->isPast() ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-800 dark:text-gray-200' }}">{{ $license->expiration_date?->format('d M Y') ?? '-' }}</dd></div>
                <div><dt class="text-[11px] font-medium text-gray-400">Cost</dt><dd class="text-xs font-semibold text-gray-900 dark:text-white">Rp {{ number_format($license->purchase_cost, 0, ',', '.') ?: '-' }}</dd></div>
            </dl>
            <div class="mt-5 flex gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                <a href="{{ route('licenses.edit', $license) }}" class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition"><i data-feather="edit-2" class="w-3.5 h-3.5"></i> Edit</a>
                <a href="{{ route('licenses.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">Back</a>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <i data-feather="users" class="w-4 h-4 text-brand-500"></i> Seat Assignments
            </h3>

            @if($license->availableSeats() > 0)
            <form action="{{ route('licenses.checkout', $license) }}" method="POST" class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950 mb-4">
                @csrf
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 items-end">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Assign To</label>
                        <select name="assigned_to_type" class="h-8 w-full rounded-lg border border-gray-200 bg-white px-2.5 text-xs dark:border-gray-800 dark:bg-gray-900">
                            <option value="user">User</option>
                            <option value="asset">Asset</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Target</label>
                        <select name="assigned_to_id" required class="h-8 w-full rounded-lg border border-gray-200 bg-white px-2.5 text-xs dark:border-gray-800 dark:bg-gray-900">
                            <option value="">-- Select --</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}" data-type="user">{{ $user->name }}</option>
                            @endforeach
                            @foreach(\App\Models\Asset::orderBy('device_name')->get() as $asset)
                                <option value="{{ $asset->id }}" data-type="asset">{{ $asset->name }} ({{ $asset->asset_tag }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Notes</label><input type="text" name="notes" class="h-8 w-full rounded-lg border border-gray-200 bg-white px-2.5 text-xs dark:border-gray-800 dark:bg-gray-900" placeholder="Optional"></div>
                </div>
                <div class="mt-3"><button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">Assign Seat</button></div>
            </form>
            @else
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-700 mb-4 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400">All seats are currently assigned.</div>
            @endif

            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Current Assignments</h4>
            @if($license->seats->isNotEmpty())
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50/50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-3 py-2 text-start font-medium text-gray-500">Assigned To</th>
                                <th class="px-3 py-2 text-start font-medium text-gray-500">Type</th>
                                <th class="px-3 py-2 text-start font-medium text-gray-500">Date</th>
                                <th class="px-3 py-2 text-end font-medium text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($license->seats as $seat)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $seat->assignedTo?->name ?? $seat->assignedTo?->asset_tag ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 text-gray-500 text-[11px]">{{ class_basename($seat->assigned_to_type) }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $seat->checked_out_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-3 py-2 text-end">
                                        <form action="{{ route('licenses.checkin', ['license' => $license, 'seat' => $seat]) }}" method="POST" class="inline-flex">
                                            @csrf
                                            <button class="rounded bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100 dark:bg-orange-500/10 dark:text-orange-400 transition">Checkin</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 text-center dark:border-gray-800 dark:bg-gray-950"><p class="text-xs text-gray-400">No seats assigned yet.</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
