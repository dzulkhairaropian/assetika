@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="License Detail" />

    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 lg:col-span-1">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                <i data-feather="key" class="w-5 h-5 text-brand-500"></i> License Info
            </h3>
            <dl class="space-y-3">
                <div><dt class="text-xs font-medium text-gray-500">Name</dt><dd class="text-sm text-gray-800 dark:text-white/90">{{ $license->name }}</dd></div>
                <div><dt class="text-xs font-medium text-gray-500">Product Key</dt><dd class="text-sm font-mono text-gray-800 dark:text-white/90">{{ $license->product_key ?? '-' }}</dd></div>
                <div><dt class="text-xs font-medium text-gray-500">Seats</dt><dd class="text-sm text-gray-800 dark:text-white/90">{{ $license->seats }} total / <span class="{{ $license->availableSeats() <= 0 ? 'text-error-500' : 'text-brand-500' }}">{{ $license->availableSeats() }} available</span></dd></div>
                <div><dt class="text-xs font-medium text-gray-500">Manufacturer</dt><dd class="text-sm text-gray-800 dark:text-white/90">{{ $license->manufacturer ?? '-' }}</dd></div>
                <div><dt class="text-xs font-medium text-gray-500">Purchase Date</dt><dd class="text-sm text-gray-800 dark:text-white/90">{{ $license->purchase_date?->format('d M Y') ?? '-' }}</dd></div>
                <div><dt class="text-xs font-medium text-gray-500">Expiration</dt><dd class="text-sm {{ $license->expiration_date?->isPast() ? 'text-error-500' : 'text-gray-800' }}">{{ $license->expiration_date?->format('d M Y') ?? '-' }}</dd></div>
                <div><dt class="text-xs font-medium text-gray-500">Cost</dt><dd class="text-sm text-gray-800 dark:text-white/90">Rp {{ number_format($license->purchase_cost, 0, ',', '.') ?: '-' }}</dd></div>
            </dl>
            <div class="mt-6 flex gap-2">
                <a href="{{ route('licenses.edit', $license) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"><i data-feather="edit-2" class="w-4 h-4"></i> Edit</a>
                <a href="{{ route('licenses.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Back</a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                <i data-feather="users" class="w-5 h-5 text-brand-500"></i> Seat Assignments
            </h3>

            @if($license->availableSeats() > 0)
            <form action="{{ route('licenses.checkout', $license) }}" method="POST" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50 mb-6">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-end">
                    <div>
                        <label class="mb-1 block text-xs font-medium">Assign To</label>
                        <select name="assigned_to_type" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                            <option value="user">User</option>
                            <option value="asset">Asset</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium">Target</label>
                        <select name="assigned_to_id" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                            <option value="">-- Select --</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}" data-type="user">{{ $user->name }}</option>
                            @endforeach
                            @foreach(\App\Models\Asset::orderBy('device_name')->get() as $asset)
                                <option value="{{ $asset->id }}" data-type="asset">{{ $asset->name }} ({{ $asset->asset_tag }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="mb-1 block text-xs font-medium">Notes</label><input type="text" name="notes" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900" placeholder="Optional"></div>
                </div>
                <div class="mt-3"><button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Assign Seat</button></div>
            </form>
            @else
            <div class="rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-700 mb-6 dark:border-error-900/30 dark:bg-error-500/10 dark:text-error-400">All seats are currently assigned.</div>
            @endif

            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-400 mb-3">Current Assignments</h4>
            @if($license->seats->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Assigned To</th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Type</th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Date</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                            @foreach($license->seats as $seat)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-400">{{ $seat->assignedTo?->name ?? $seat->assignedTo?->asset_tag ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ class_basename($seat->assigned_to_type) }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $seat->checked_out_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form action="{{ route('licenses.checkin', ['license' => $license, 'seat' => $seat]) }}" method="POST" class="inline-flex">
                                            @csrf
                                            <button class="rounded bg-warning-100 px-2.5 py-1 text-xs font-medium text-warning-700 hover:bg-warning-200 dark:bg-warning-500/10 dark:text-warning-400">Checkin</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-900/50"><p class="text-sm text-gray-500">No seats assigned yet.</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
