@extends('layouts.app')

@section('content')
    @php
        $breadcrumbLabel = $license->name;
        $breadcrumbItems = [
            ['label' => 'Licenses', 'url' => route('licenses.index')],
            ['label' => $breadcrumbLabel],
        ];
        $isExpired = $license->expiration_date && $license->expiration_date->isPast();
        $availableSeats = $license->availableSeats();
    @endphp

    <div class="space-y-5" x-data="{ activeTab: 'seats' }">
        <x-common.page-breadcrumb pageTitle="License Details" :items="$breadcrumbItems" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Software seat allocations, license activation keys, and assigned employees/hardware.
        </p>

        @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
        @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

        {{-- Entity Header Hero Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 lg:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-xs">
                        <i data-lucide="key" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{ $license->name }}
                            </h2>
                            @if($isExpired)
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                    Expired ({{ $license->expiration_date->format('d M Y') }})
                                </span>
                            @elseif($availableSeats <= 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                    All {{ $license->seats }} Seats Allocated
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    {{ $availableSeats }} of {{ $license->seats }} Seats Available
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-medium">
                            {{ $license->manufacturer ?? 'General Software' }} • 
                            Account: <span class="text-gray-900 dark:text-white font-medium">{{ $license->license_email ?: 'Unspecified' }}</span> • 
                            Key: <span class="font-mono text-gray-700 dark:text-gray-300">{{ $license->product_key ?? '—' }}</span>
                        </p>
                    </div>
                </div>

                {{-- Action Toolbar --}}
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                    <a href="{{ route('licenses.edit', $license) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                        Edit
                    </a>
                    <a href="{{ route('licenses.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- 3-Column Layout: Left (1 Col Context Sidebar) vs Right (2 Col Content Hub) --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            {{-- Left Column: License Overview & Terms --}}
            <div class="space-y-4 lg:col-span-1">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Subscription & Seats</h3>
                    </div>

                    <dl class="mt-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Total Seats:</dt>
                            <dd class="font-bold text-gray-900 dark:text-white">{{ number_format($license->seats) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Available:</dt>
                            <dd class="font-bold {{ $availableSeats > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ number_format($availableSeats) }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Purchase Date:</dt>
                            <dd class="font-mono text-gray-800 dark:text-gray-200">{{ $license->purchase_date?->format('d M Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-400">Expiration Date:</dt>
                            <dd class="font-mono {{ $isExpired ? 'text-rose-600 font-bold' : 'text-gray-800 dark:text-gray-200' }}">
                                {{ $license->expiration_date?->format('d M Y') ?? 'Never (Perpetual)' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-2.5 dark:border-gray-800">
                            <dt class="text-gray-400">Procurement Cost:</dt>
                            <dd class="font-mono font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($license->purchase_cost ?? 0, 0, ',', '.') }}
                            </dd>
                        </div>
                    </dl>
                </div>

                @if($license->notes)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Remarks</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $license->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Right Column: Seat Assignment Manager --}}
            <div class="lg:col-span-2 space-y-5">
                {{-- Seat Assignment Form Card --}}
                @if($availableSeats > 0)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                        <div class="border-b border-gray-100 pb-3 dark:border-gray-800 mb-4 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Assign License Seat</h3>
                                <p class="text-[11px] text-gray-400">Allocate an available seat to an employee or hardware appliance</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                {{ $availableSeats }} Seats Remaining
                            </span>
                        </div>

                        <form action="{{ route('licenses.checkout', $license) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Assignment Target Type</label>
                                    <select name="assigned_to_type" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                        <option value="user">Individual User / Employee</option>
                                        <option value="asset">Hardware Asset Device</option>
                                    </select>
                                </div>
                                <div>
                                    <x-form.searchable-select name="assigned_to_id" label="Recipient Entity" :options="$userOpts" placeholder="Select User" required="true" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Seat Notes (Optional)</label>
                                    <input type="text" name="notes" placeholder="e.g. Primary development machine / Office workstation"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-600 active:scale-[0.98] transition">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    Assign Seat
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Seat Assignments Table Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-gray-100 p-4 dark:border-gray-800">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Active Seat Holders</h3>
                            <p class="text-[11px] text-gray-400">List of deployed license instances</p>
                        </div>
                    </div>

                    <div class="max-w-full overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-950 text-[11px] text-gray-400 font-semibold uppercase">
                                    <th class="px-4 py-2.5 text-start">Assigned To</th>
                                    <th class="px-4 py-2.5 text-start">Type</th>
                                    <th class="px-4 py-2.5 text-start">Date</th>
                                    <th class="px-4 py-2.5 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($license->seats as $seat)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                        <td class="px-4 py-2.5 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                            {{ $seat->assignedTo?->name ?? $seat->assignedTo?->asset_tag ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2.5 whitespace-nowrap text-gray-500 text-[11px]">
                                            {{ class_basename($seat->assigned_to_type) }}
                                        </td>
                                        <td class="px-4 py-2.5 whitespace-nowrap font-mono text-gray-600 dark:text-gray-400">
                                            {{ $seat->checked_out_at?->format('d M Y') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                            <form action="{{ route('licenses.checkin', ['license' => $license, 'seat' => $seat]) }}" method="POST" class="inline-flex">
                                                @csrf
                                                <button class="rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-400 transition">
                                                    Revoke Seat
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                            No seats currently assigned.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
