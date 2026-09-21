@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Edit BAST Document" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)]]" />

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-lucide="edit-3" class="h-5 w-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit BAST Document</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Update handover parties, dates, and notes for <strong>{{ $asset->asset_tag }}</strong> ({{ $asset->device_name }}).</p>
                </div>
            </div>

            <form action="{{ route('assets.bast.update', [$asset, $checkout]) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-brand-500"></i>
                        Handover Parties & Signatories
                    </h3>
                    <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
                        <!-- Giver / First Party -->
                        <div>
                            <x-form.searchable-select name="checked_out_by_user_id" label="First Party (Giver / IT Admin)" :options="$userOpts" :selected="old('checked_out_by_user_id', $checkout->checked_out_by_user_id)" placeholder="Select Giver" required="true" />
                            @error('checked_out_by_user_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Receiver / Second Party -->
                        <div>
                            <x-form.searchable-select name="checked_out_to_user_id" label="Second Party (Receiver / User)" :options="$userOpts" :selected="old('checked_out_to_user_id', $checkout->checked_out_to_user_id)" placeholder="Select Receiver" required="true" />
                            @error('checked_out_to_user_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Supervisor / Knowing -->
                        <div class="lg:col-span-2">
                            <x-form.searchable-select name="supervisor_user_id" label="Knowing Party (Receiver Supervisor / Department Manager)" :options="$userOpts" :selected="old('supervisor_user_id', $checkout->supervisor_user_id)" placeholder="-- Select Supervisor / Leave Blank for IT Manager --" />
                            @error('supervisor_user_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-800">

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-brand-500"></i>
                        Handover Dates & Completeness
                    </h3>
                    <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
                        <!-- Checkout Date -->
                        <div>
                            <label for="checked_out_at" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Handover Date <span class="text-red-500">*</span>
                            </label>
                            <x-form.date-picker
                                id="checked_out_at"
                                name="checked_out_at"
                                placeholder="Select date"
                                defaultDate="{{ old('checked_out_at', optional($checkout->checked_out_at)->format('Y-m-d')) }}"
                            />
                            @error('checked_out_at') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Notes / Completeness -->
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Completeness & Notes
                            </label>
                            <textarea name="checkout_notes" rows="3" placeholder="e.g. Unit, Charger, Bag included..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('checkout_notes', $checkout->checkout_notes) }}</textarea>
                            @error('checkout_notes') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <a href="{{ route('assets.show', $asset) }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        Update BAST Document
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
