@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Edit Renewal Item" :items="[['label' => 'Expiry & Renewals', 'url' => route('renewals.index')], ['label' => 'Edit Item']]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Update expiration date, cost, or assigned responsible user.
        </p>

        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('renewals.update', $renewal) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf
            @method('PUT')

            <!-- Left Column: Primary Form Fields -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                            General Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Item / Domain / License Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $renewal->name) }}" required
                                    class="dark:bg-gray-950 h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Renewal Type <span class="text-error-500">*</span>
                                </label>
                                <div class="relative z-20 bg-transparent">
                                    <select name="type" required
                                        class="dark:bg-gray-950 shadow-sm focus:border-brand-500 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-200 bg-gray-50 bg-none px-3 py-1.5 pr-11 text-xs text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                        <option value="ssl_certificate" {{ $renewal->type == 'ssl_certificate' ? 'selected' : '' }}>SSL Certificate</option>
                                        <option value="domain" {{ $renewal->type == 'domain' ? 'selected' : '' }}>Web Domain</option>
                                        <option value="license" {{ $renewal->type == 'license' ? 'selected' : '' }}>Software License</option>
                                        <option value="vehicle_tax" {{ $renewal->type == 'vehicle_tax' ? 'selected' : '' }}>GA Vehicle Tax</option>
                                        <option value="vehicle_insurance" {{ $renewal->type == 'vehicle_insurance' ? 'selected' : '' }}>GA Vehicle Insurance</option>
                                        <option value="custom" {{ $renewal->type == 'custom' ? 'selected' : '' }}>Custom / Other</option>
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                @error('type') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Expiration Date <span class="text-error-500">*</span>
                                </label>
                                <input type="date" name="expiration_date" value="{{ old('expiration_date', $renewal->expiration_date->format('Y-m-d')) }}" required
                                    class="dark:bg-gray-950 h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('expiration_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800">

                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="clock" class="w-5 h-5 text-brand-500"></i>
                            Settings & Assignment
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Reminder Days (Before Expiry) <span class="text-error-500">*</span>
                                </label>
                                <input type="number" name="reminder_days_before" value="{{ old('reminder_days_before', $renewal->reminder_days_before) }}" min="1" required
                                    class="dark:bg-gray-950 h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('reminder_days_before') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Estimated Renewal Cost (Rp)
                                </label>
                                <input type="number" name="cost" value="{{ old('cost', $renewal->cost) }}" step="0.01"
                                    class="dark:bg-gray-950 h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('cost') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                @php
                                    $vendorOpts = array_merge([['id' => '', 'text' => 'Select Vendor (Optional)']], $vendors->map(fn($v) => ['id' => $v->id, 'text' => $v->name])->toArray());
                                @endphp
                                <x-form.searchable-select name="vendor_id" label="Vendor / Provider" :options="$vendorOpts" :selected="old('vendor_id', $renewal->vendor_id)" placeholder="Select Vendor" />
                            </div>

                            <div>
                                @php
                                    $userOpts = array_merge([['id' => '', 'text' => 'Select Responsible User']], $users->map(fn($u) => ['id' => $u->id, 'text' => $u->name])->toArray());
                                @endphp
                                <x-form.searchable-select name="responsible_user_id" label="Responsible User" :options="$userOpts" :selected="old('responsible_user_id', $renewal->responsible_user_id)" placeholder="Select User" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar Actions -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Update Item
                        </button>
                        <a href="{{ route('renewals.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
