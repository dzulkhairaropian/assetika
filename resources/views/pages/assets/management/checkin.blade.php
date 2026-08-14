@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Asset Check-in" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)]]" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="mb-8 flex items-center gap-4 border-b border-gray-100 pb-6 dark:border-gray-800">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-feather="log-in" class="h-6 w-6"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">Check-in Asset</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Returning <strong>{{ $asset->asset_tag }}</strong> from <strong>{{ $asset->currentCheckout->checkedOutToUser->name ?? 'User' }}</strong>.</p>
                </div>
            </div>

            <form action="{{ route('assets.checkin.process', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Section: Return Details -->
                <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                        Return Details
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <!-- Check-in Date -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Check-in Date <span class="text-error-500">*</span>
                            </label>
                            <input type="date" name="checked_in_at" value="{{ old('checked_in_at', date('Y-m-d')) }}" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('checked_in_at') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Asset Condition -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Asset Condition <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="condition_report" value="{{ old('condition_report', 'Good') }}" required placeholder="e.g. Good, Scratched, Screen broken"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('condition_report') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Notes -->
                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Return Notes
                            </label>
                            <textarea name="checkin_notes" rows="3" placeholder="Any additional comments on return..."
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('checkin_notes') }}</textarea>
                            @error('checkin_notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-800">

                <!-- Section: Handover Documentation -->
                <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="file-text" class="w-5 h-5 text-brand-500"></i>
                        Handover Documentation
                    </h3>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Evidence (Optional)
                        </label>
                        <div class="relative flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:border-gray-700 dark:hover:border-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i data-feather="upload-cloud" class="w-8 h-8 mb-2 text-gray-400"></i>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-400">PNG, JPG or PDF (MAX. 5MB)</p>
                                </div>
                                <input type="file" name="checkin_evidence" class="hidden" accept="image/*,.pdf" />
                            </label>
                        </div>
                        @error('checkin_evidence') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('assets.show', $asset) }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                        Process Check-in
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
