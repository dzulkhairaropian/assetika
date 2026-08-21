@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Add Renewal Item" :items="[['label' => 'Expiry & Renewals', 'url' => route('renewals.index')], ['label' => 'Add Item']]" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Create a new item to track expiration and renewal schedules.
    </p>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('renewals.store') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Item / Domain / License Name <span class="text-error-500">*</span></label>
                    <input type="text" name="name" required placeholder="Example: SSL Wildcard *.diastika.co.id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Renewal Type <span class="text-error-500">*</span></label>
                        <select name="type" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="ssl_certificate">SSL Certificate</option>
                            <option value="domain">Web Domain</option>
                            <option value="license">Software License</option>
                            <option value="vehicle_tax">GA Vehicle Tax</option>
                            <option value="vehicle_insurance">GA Vehicle Insurance</option>
                            <option value="custom">Custom / Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Expiration Date <span class="text-error-500">*</span></label>
                        <input type="date" name="expiration_date" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Reminder Days (Before Expiry) <span class="text-error-500">*</span></label>
                        <input type="number" name="reminder_days_before" value="30" min="1" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Renewal Cost (Rp)</label>
                        <input type="number" name="cost" step="0.01" placeholder="0" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Vendor / Provider</label>
                        <select name="vendor_id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">-- Optional --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Responsible User</label>
                        <select name="responsible_user_id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">-- Optional --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('renewals.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                    <i data-feather="check-circle" class="w-4 h-4"></i>
                    Save Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
