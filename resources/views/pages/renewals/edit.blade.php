@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Edit Renewal Item" :items="[['label' => 'Expiry & Renewals', 'url' => route('renewals.index')], ['label' => 'Edit Item']]" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Update expiration date and renewal details.
    </p>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('renewals.update', $renewal) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Item / Domain / License Name <span class="text-error-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $renewal->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Renewal Type <span class="text-error-500">*</span></label>
                        <select name="type" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="ssl_certificate" {{ $renewal->type == 'ssl_certificate' ? 'selected' : '' }}>SSL Certificate</option>
                            <option value="domain" {{ $renewal->type == 'domain' ? 'selected' : '' }}>Web Domain</option>
                            <option value="license" {{ $renewal->type == 'license' ? 'selected' : '' }}>Software License</option>
                            <option value="vehicle_tax" {{ $renewal->type == 'vehicle_tax' ? 'selected' : '' }}>GA Vehicle Tax</option>
                            <option value="vehicle_insurance" {{ $renewal->type == 'vehicle_insurance' ? 'selected' : '' }}>GA Vehicle Insurance</option>
                            <option value="custom" {{ $renewal->type == 'custom' ? 'selected' : '' }}>Custom / Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Expiration Date <span class="text-error-500">*</span></label>
                        <input type="date" name="expiration_date" value="{{ old('expiration_date', $renewal->expiration_date->format('Y-m-d')) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Reminder Days (Before Expiry) <span class="text-error-500">*</span></label>
                        <input type="number" name="reminder_days_before" value="{{ old('reminder_days_before', $renewal->reminder_days_before) }}" min="1" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Renewal Cost (Rp)</label>
                        <input type="number" name="cost" value="{{ old('cost', $renewal->cost) }}" step="0.01" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Vendor / Provider</label>
                        <select name="vendor_id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">-- Optional --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ $renewal->vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Responsible User</label>
                        <select name="responsible_user_id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">-- Optional --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $renewal->responsible_user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('renewals.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                    <i data-feather="check-circle" class="w-4 h-4"></i>
                    Update Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
