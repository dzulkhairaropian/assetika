@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Edit Renewal Item" :items="[['label' => 'Expiry & Renewals', 'url' => route('renewals.index')], ['label' => 'Edit']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Update expiration target date, renewal budget, or accountable owner for <strong class="text-gray-900 dark:text-white">{{ $renewal->name }}</strong>.
                </p>
            </div>
            <a href="{{ route('renewals.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Renewals
            </a>
        </div>

        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('renewals.update', $renewal) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf
            @method('PUT')

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card: General Information --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Renewal Item Profile</h3>
                                <p class="text-[11px] text-gray-400">Target service name, tracking classification, and accountable person</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Item / Domain / License Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $renewal->name) }}" required placeholder="e.g. SSL Wildcard *.domain.co.id / Google Workspace Renewal"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Renewal Type <span class="text-error-500">*</span>
                            </label>
                            <select name="type" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                <option value="ssl_certificate" {{ old('type', $renewal->type) == 'ssl_certificate' ? 'selected' : '' }}>SSL Certificate</option>
                                <option value="domain" {{ old('type', $renewal->type) == 'domain' ? 'selected' : '' }}>Web Domain</option>
                                <option value="license" {{ old('type', $renewal->type) == 'license' ? 'selected' : '' }}>Software License</option>
                                <option value="vehicle_tax" {{ old('type', $renewal->type) == 'vehicle_tax' ? 'selected' : '' }}>GA Vehicle Tax</option>
                                <option value="vehicle_insurance" {{ old('type', $renewal->type) == 'vehicle_insurance' ? 'selected' : '' }}>GA Vehicle Insurance</option>
                                <option value="custom" {{ old('type', $renewal->type) == 'custom' ? 'selected' : '' }}>Custom / Other</option>
                            </select>
                            @error('type') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select
                                name="responsible_user_id"
                                label="Responsible PIC / User"
                                :options="$users->map(fn($u) => ['id' => $u->id, 'text' => $u->name])->toArray()"
                                :selected="old('responsible_user_id', $renewal->responsible_user_id)"
                                placeholder="Select Responsible User"
                            />
                        </div>

                        <div>
                            <x-form.date-picker
                                id="expiration_date"
                                name="expiration_date"
                                label="Expiration Target Date"
                                placeholder="Select expiration date"
                                defaultDate="{{ old('expiration_date', $renewal->expiration_date?->format('Y-m-d')) }}"
                            />
                            @error('expiration_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Reminder Notice Window (Days)
                            </label>
                            <input type="number" name="reminder_days_before" value="{{ old('reminder_days_before', $renewal->reminder_days_before ?? 30) }}" min="1" max="180"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Notes & Instructions
                            </label>
                            <textarea name="notes" rows="3" placeholder="Vendor portal credentials, registrar account, or payment terms..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('notes', $renewal->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Financials --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                            Editing
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Update Renewal Item
                        </button>

                        <a href="{{ route('renewals.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Cost & Status Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-500"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Budget & State</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Estimated Renewal Cost
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                                <input type="number" step="0.01" name="cost" value="{{ old('cost', $renewal->cost) }}" placeholder="0.00"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Current Status
                            </label>
                            <select name="status" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                <option value="active" {{ old('status', $renewal->status) == 'active' ? 'selected' : '' }}>Active Schedule</option>
                                <option value="pending" {{ old('status', $renewal->status) == 'pending' ? 'selected' : '' }}>Pending Payment</option>
                                <option value="expired" {{ old('status', $renewal->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
