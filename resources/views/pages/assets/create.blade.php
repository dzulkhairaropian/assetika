@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create IT Asset" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Add a new hardware asset to your organization's inventory.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('assets.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-8">
                    <!-- Section: General Information -->
                    <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                        General Information
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <x-form.searchable-select name="product_id" label="Product" :options="$productOpts" :selected="old('product_id')" placeholder="Select Product" required="true" />
                            @error('product_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            @php
                                $readyToDeployStatus = $statuses->where('name', 'Ready to Deploy')->first();
                                $defaultStatusId = $readyToDeployStatus ? $readyToDeployStatus->id : '';
                            @endphp
                            <x-form.searchable-select name="status_id" label="Status" :options="$statusOpts" :selected="old('status_id', $defaultStatusId)" placeholder="Select Status" required="true" />
                            @error('status_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select name="vendor_id" label="Vendor" :options="$vendorOpts" :selected="old('vendor_id')" placeholder="Select Vendor" />
                            @error('vendor_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select name="department_id" label="Department" :options="$deptOpts" :selected="old('department_id')" placeholder="Select Department" required="true" />
                            @error('department_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select name="location_id" label="Location" :options="$locationOpts" :selected="old('location_id')" placeholder="Select Location" />
                            @error('location_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Serial Number <span class="text-error-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="serial_number" value="{{ old('serial_number') }}" placeholder="Enter serial number" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                <button type="button" onclick="generateSN()" title="Generate Random Serial Number" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                                    <i data-feather="shuffle" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            @error('serial_number') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Product Number
                            </label>
                            <input type="text" name="product_number" value="{{ old('product_number') }}" placeholder="Enter product number"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                            @error('product_number') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                User Name
                            </label>
                            <input type="text" name="user_name" value="{{ old('user_name') }}" placeholder="e.g. John Doe"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                            @error('user_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="release_date" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Release Date <span class="text-error-500">*</span>
                            </label>
                            <x-form.date-picker
                                id="release_date"
                                name="release_date"
                                placeholder="Select release date"
                                defaultDate="{{ old('release_date', date('Y-m-d')) }}"
                            />
                            @error('release_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Network Information -->
                <hr class="border-gray-100 dark:border-gray-800">
                <div class="pt-2">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="wifi" class="w-5 h-5 text-brand-500"></i>
                        Network Information
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                MAC Address Wireless
                            </label>
                            <input type="text" name="mac_address_wireless" value="{{ old('mac_address_wireless') }}" placeholder="00:00:00:00:00:00"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('mac_address_wireless') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                MAC Address Ethernet
                            </label>
                            <input type="text" name="mac_address_ethernet" value="{{ old('mac_address_ethernet') }}" placeholder="00:00:00:00:00:00"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('mac_address_ethernet') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Software Licenses -->
                <hr class="border-gray-100 dark:border-gray-800">
                <div class="pt-2">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="key" class="w-5 h-5 text-brand-500"></i>
                        Software Licenses
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Windows License
                            </label>
                            <input type="text" name="license_windows" value="{{ old('license_windows') }}" placeholder="Enter Windows license key"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('license_windows') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Office License
                            </label>
                            <input type="text" name="license_office" value="{{ old('license_office') }}" placeholder="Enter Office license key"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('license_office') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Technical Specifications -->
                <hr class="border-gray-100 dark:border-gray-800">
                <div class="pt-2">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="cpu" class="w-5 h-5 text-brand-500"></i>
                        Technical Specifications
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <x-form.searchable-select name="operating_system_id" label="Operating System" :options="$osOpts" :selected="old('operating_system_id')" placeholder="Select OS" />
                            @error('operating_system_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Specification
                            </label>
                            <textarea name="specification" rows="4" placeholder="e.g. Core i7, 16GB RAM, 512GB SSD"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('specification') }}</textarea>
                            @error('specification') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Accessories
                            </label>
                            <textarea name="accessories" rows="3" placeholder="e.g. Mouse, Keyboard, Charger"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('accessories') }}</textarea>
                            @error('accessories') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                </div>
            </div>

            <div class="space-y-6">
                <!-- Section: Financial Information -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-feather="dollar-sign" class="w-5 h-5 text-brand-500"></i>
                        Financial Information
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <x-form.date-picker
                                id="purchase_date"
                                name="purchase_date"
                                label="Purchase Date"
                                placeholder="Select purchase date"
                                defaultDate="{{ old('purchase_date') }}"
                            />
                            @error('purchase_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Purchase Cost
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">Rp</span>
                                <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost') }}" placeholder="0.00"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            </div>
                            @error('purchase_cost') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select
                                name="depreciation_id"
                                label="Depreciation Profile"
                                :options="$depreciations->map(fn($d) => ['id' => $d->id, 'text' => $d->name . ' (' . $d->months . ' Months)'])->toArray()"
                                :selected="old('depreciation_id')"
                                placeholder="Select Depreciation"
                            />
                            @error('depreciation_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.date-picker
                                id="warranty_expiry_date"
                                name="warranty_expiry_date"
                                label="Warranty Expiry Date"
                                placeholder="Select warranty expiry date"
                                defaultDate="{{ old('warranty_expiry_date') }}"
                            />
                            @error('warranty_expiry_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Invoice Number
                            </label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="e.g. INV/2026/06/001"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('invoice_number') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Notes
                            </label>
                            <textarea name="notes" rows="3" placeholder="Additional notes..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('notes') }}</textarea>
                            @error('notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Create Asset
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                            Create & Create New
                        </button>
                        <button type="submit" name="is_draft" value="1" formnovalidate class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                            Save as Draft
                        </button>
                        <a href="{{ route('assets.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function generateSN() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let sn = 'DUMMY-SN-';
        for (let i = 0; i < 8; i++) sn += chars.charAt(Math.floor(Math.random() * chars.length));
        document.querySelector('input[name="serial_number"]').value = sn;
    }

    const warrantyInput = document.querySelector('input[name="warranty_expiry_date"]');
    const releaseDateInput = document.querySelector('input[name="release_date"]');

    if (releaseDateInput && warrantyInput) {
        releaseDateInput.addEventListener('change', (e) => {
            const releaseDate = new Date(e.target.value);
            if (!isNaN(releaseDate.getTime())) {
                const warrantyDate = new Date(releaseDate);
                warrantyDate.setFullYear(warrantyDate.getFullYear() + 1);
                warrantyInput.value = warrantyDate.toISOString().split('T')[0];
            }
        });
    }
</script>
@endpush
