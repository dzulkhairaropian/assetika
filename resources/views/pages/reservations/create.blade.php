@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create Asset Reservation" :items="[['label' => 'Reservations', 'url' => route('reservations.index')], ['label' => 'Create']]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Schedule temporary asset borrowing for upcoming activities.
        </p>

        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('reservations.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <!-- Left Column: Primary Form Fields -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                            Reservation Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div class="lg:col-span-2">
                                @php
                                    $assetOptions = array_merge(
                                        [['id' => '', 'text' => 'Select Asset']],
                                        $assets->map(function($asset) {
                                            $holder = $asset->currentCheckout?->checkedOutToUser?->name ?? $asset->assignedToUser?->name;
                                            $dept = $asset->department?->name;
                                            $statusName = $asset->status?->name;
                                            $brandModel = optional($asset->product?->brand)->name . ' ' . optional($asset->product)->model;
                                            $deviceName = $asset->device_name ?: trim($brandModel);
                                            return [
                                                'id' => $asset->id,
                                                'text' => '[' . $asset->asset_tag . '] ' . $deviceName . ' — ' . ($holder ? 'User: ' . $holder : 'Unassigned') . ($dept ? ' ('.$dept.')' : '') . ($statusName ? ' [' . $statusName . ']' : '')
                                            ];
                                        })->toArray()
                                    );
                                @endphp
                                <x-form.searchable-select
                                    name="reservable_id"
                                    label="Linked Asset (ITAM)"
                                    :options="$assetOptions"
                                    :selected="old('reservable_id')"
                                    placeholder="Select Asset"
                                    required="true"
                                />
                                <input type="hidden" name="reservable_type" value="App\Models\Asset">
                                @error('reservable_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Start Time <span class="text-error-500">*</span>
                                </label>
                                <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('start_time') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    End Time <span class="text-error-500">*</span>
                                </label>
                                <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('end_time') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800">

                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="file-text" class="w-5 h-5 text-brand-500"></i>
                            Purpose & Notes
                        </h3>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Reservation Purpose <span class="text-error-500">*</span>
                            </label>
                            <textarea name="purpose" rows="4" required placeholder="Describe the purpose of using this asset..."
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('purpose') }}</textarea>
                            @error('purpose') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
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
                            Submit Reservation
                        </button>
                        <a href="{{ route('reservations.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
