@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Create Asset Reservation" :items="[['label' => 'Reservations', 'url' => route('reservations.index')], ['label' => 'Create']]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Schedule temporary asset borrowing for upcoming activities.
        </p>

        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('reservations.store') }}" method="POST" class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_340px]">
            @csrf

            <!-- Left Column: Primary Form Fields -->
            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                            <i data-feather="info" class="w-4 h-4 text-brand-500"></i>
                            Reservation Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
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
                                @error('reservable_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Start Time <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                @error('start_time') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    End Time <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                @error('end_time') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800">

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                            <i data-feather="file-text" class="w-4 h-4 text-brand-500"></i>
                            Purpose & Notes
                        </h3>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Reservation Purpose <span class="text-red-500">*</span>
                            </label>
                            <textarea name="purpose" rows="4" required placeholder="Describe the purpose of using this asset..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('purpose') }}</textarea>
                            @error('purpose') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar Actions -->
            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                        <i data-feather="save" class="w-4 h-4 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            Submit Reservation
                        </button>
                        <a href="{{ route('reservations.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
