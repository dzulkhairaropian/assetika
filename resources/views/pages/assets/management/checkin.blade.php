@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Asset Check-in" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)]]" />

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-lucide="log-in" class="h-5 w-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Check-in Asset</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Returning <strong>{{ $asset->asset_tag }}</strong> from <strong>{{ $asset->currentCheckout->checkedOutToUser->name ?? 'User' }}</strong>.</p>
                </div>
            </div>

            <form action="{{ route('assets.checkin.process', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section: Return Details -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                        Return Details
                    </h3>
                    <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
                        <!-- Check-in Date -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Check-in Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="checked_in_at" value="{{ old('checked_in_at', date('Y-m-d')) }}" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            @error('checked_in_at') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Status After Return -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Status After Check-in <span class="text-red-500">*</span>
                            </label>
                            <select name="status_id" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                @foreach($statuses as $st)
                                    <option value="{{ $st->id }}" {{ old('status_id', $st->name == 'Ready to Deploy' ? $st->id : '') == $st->id ? 'selected' : '' }}>
                                        {{ $st->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Supervisor (Knowing) -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Supervisor / Knowing (Optional)
                            </label>
                            <select name="supervisor_user_id"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                <option value="">Select Supervisor</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" {{ old('supervisor_user_id', $asset->currentCheckout?->supervisor_user_id) == $supervisor->id ? 'selected' : '' }}>
                                        {{ $supervisor->name }}{{ $supervisor->department ? ' - ' . $supervisor->department->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supervisor_user_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Asset Condition -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Asset Condition <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="condition_report" value="{{ old('condition_report', 'Good') }}" required placeholder="e.g. Good, Scratched, Screen broken"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            @error('condition_report') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Component Condition (Stripped / Laptop Kosong) -->
                        <div class="lg:col-span-2 rounded-lg border border-amber-200/60 bg-amber-50/50 p-3 dark:border-amber-900/30 dark:bg-amber-950/20">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_components_stripped" value="1" {{ old('is_components_stripped', $asset->is_components_stripped) ? 'checked' : '' }}
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                <div>
                                    <span class="text-xs font-semibold text-gray-900 dark:text-white">Components Stripped / Empty Laptop (RAM & SSD removed)</span>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Check this option if the laptop is returned in an empty state (RAM, SSD, Hard Drive, etc. removed).</p>
                                </div>
                            </label>
                        </div>

                        <!-- Notes -->
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Return Notes
                            </label>
                            <textarea name="checkin_notes" rows="3" placeholder="Any additional comments on return..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('checkin_notes') }}</textarea>
                            @error('checkin_notes') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-800">

                <!-- Section: Handover Documentation -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i>
                        Handover Documentation
                    </h3>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Evidence (Optional)
                        </label>
                        <div class="relative flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-950 hover:bg-gray-100 dark:border-gray-800 transition">
                                <div class="flex flex-col items-center justify-center pt-4 pb-4">
                                    <i data-lucide="cloud-upload" class="w-6 h-6 mb-1 text-gray-400"></i>
                                    <p class="mb-1 text-xs text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-[11px] text-gray-400">PNG, JPG or PDF (MAX. 5MB)</p>
                                </div>
                                <input type="file" name="checkin_evidence" class="hidden" accept="image/*,.pdf" />
                            </label>
                        </div>
                        @error('checkin_evidence') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <a href="{{ route('assets.show', $asset) }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        Process Check-in
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
