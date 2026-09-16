@extends('layouts.app')

@section('content')
    @php
        $selectedProductId = old('product_id');
        $selectedStatusId = old('status_id', $readyStatus?->id);
        $selectedVendorId = old('vendor_id');
        $selectedLocationId = old('location_id');
    @endphp

    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create Component" :items="[['label' => 'Components', 'url' => route('components.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Add a new hardware component unit to your organization's inventory.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('components.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-8">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>
                            General Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-4 gap-y-3 lg:grid-cols-2">
                            <div>
                                <x-form.searchable-select name="product_id" label="Product Model" :options="$productOpts" :selected="$selectedProductId" placeholder="Select Product" required="true" />
                                @error('product_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-form.searchable-select name="status_id" label="Status" :options="$statusOpts" :selected="$selectedStatusId" placeholder="Select Status" required="true" />
                                @error('status_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Component Tag
                                </label>
                                <input type="text" name="component_tag" value="{{ old('component_tag') }}" placeholder="Auto-generated (e.g. DB/BATT/260912/001)"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 font-mono" />
                                @error('component_tag') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Model Number / Spec
                                </label>
                                <input type="text" name="model_number" value="{{ old('model_number') }}" placeholder="e.g. Latitude 3410 Battery"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Serial Number / Batch No.
                                </label>
                                <input type="text" name="serial" value="{{ old('serial') }}" placeholder="e.g. SN-BAT-3410-001"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Quantity Purchased <span class="text-error-500">*</span>
                                </label>
                                <input type="number" name="qty" value="{{ old('qty', 1) }}" min="1" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('qty') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Min Qty Alert
                                </label>
                                <input type="number" name="min_qty" value="{{ old('min_qty', 0) }}" min="0"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Purchase Cost (Rp)
                                </label>
                                <input type="number" name="purchase_cost" value="{{ old('purchase_cost') }}" step="0.01" placeholder="0.00"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <label for="purchase_date" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Purchase Date
                                </label>
                                <x-form.date-picker
                                    id="purchase_date"
                                    name="purchase_date"
                                    placeholder="Select purchase date"
                                    defaultDate="{{ old('purchase_date') }}"
                                />
                                @error('purchase_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="release_date" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Release Date (Pemasangan)
                                </label>
                                <x-form.date-picker
                                    id="release_date"
                                    name="release_date"
                                    placeholder="Select release date"
                                    defaultDate="{{ old('release_date') }}"
                                />
                                @error('release_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Warranty (Months)
                                </label>
                                <input type="number" name="warranty_months" value="{{ old('warranty_months') }}" min="0" placeholder="e.g. 12"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <x-form.searchable-select name="vendor_id" label="Vendor / Supplier" :options="$vendorOpts" :selected="$selectedVendorId" placeholder="Select Vendor" />
                            </div>

                            <div>
                                <x-form.searchable-select name="location_id" label="Location" :options="$locationOpts" :selected="$selectedLocationId" placeholder="Select Location" />
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Notes
                                </label>
                                <textarea name="notes" rows="3" placeholder="Additional notes..."
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="save" class="w-4 h-4 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Save Component
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center items-center gap-2 rounded-xl border border-brand-500 bg-white px-4 py-2.5 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:border-brand-500 dark:bg-gray-950 dark:text-brand-400 transition">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            Save & Create Another
                        </button>
                        <a href="{{ route('components.index') }}" class="inline-flex w-full justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
