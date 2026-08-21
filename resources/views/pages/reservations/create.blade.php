@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="New Asset Reservation" :items="[['label' => 'Reservations', 'url' => route('reservations.index')], ['label' => 'New Reservation']]" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="mb-8 flex items-center gap-4 border-b border-gray-100 pb-6 dark:border-gray-800">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                <i data-feather="calendar" class="h-6 w-6"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">Asset Reservation Request</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Schedule temporary asset borrowing for upcoming activities.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('reservations.store') }}" class="space-y-8">
            @csrf

            <!-- Section: Reservation Details -->
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                    <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                    Reservation Details
                </h3>

                <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Select Asset <span class="text-error-500">*</span>
                        </label>
                        <div class="relative z-20 bg-transparent">
                            <select name="reservable_id" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">-- Select IT Asset --</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">[IT] {{ $asset->name }} ({{ $asset->asset_tag ?? '-' }})</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <input type="hidden" name="reservable_type" value="App\Models\Asset">
                        @error('reservable_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Start Time <span class="text-error-500">*</span>
                        </label>
                        <input type="datetime-local" name="start_time" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        @error('start_time') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            End Time <span class="text-error-500">*</span>
                        </label>
                        <input type="datetime-local" name="end_time" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        @error('end_time') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Reservation Purpose <span class="text-error-500">*</span>
                        </label>
                        <textarea name="purpose" rows="3" required placeholder="Describe the purpose of using this asset..."
                            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                        @error('purpose') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('reservations.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                    Submit Reservation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
