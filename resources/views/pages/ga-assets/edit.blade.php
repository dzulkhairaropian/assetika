@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{ 
        step: 1,
        totalSteps: 4,
        nextStep() { if (this.step < this.totalSteps) this.step++; },
        prevStep() { if (this.step > 1) this.step--; }
    }">
        <x-common.page-breadcrumb pageTitle="Edit GA Asset" :items="[['label' => 'GA Assets', 'url' => route('ga-assets.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Update GA asset details for {{ $ga_asset->asset_tag ?? $ga_asset->name }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            
            <!-- Wizard Headers -->
            <div class="mb-8 grid grid-cols-4 border-b border-gray-200 dark:border-gray-800 text-center pb-4 gap-2">
                <button type="button" @click="step = 1" :class="step === 1 ? 'text-brand-500 font-bold border-b-2 border-brand-500 pb-4' : 'text-gray-400 pb-4'" class="text-sm">
                    1. General
                </button>
                <button type="button" @click="step = 2" :class="step === 2 ? 'text-brand-500 font-bold border-b-2 border-brand-500 pb-4' : 'text-gray-400 pb-4'" class="text-sm">
                    2. Acquisition
                </button>
                <button type="button" @click="step = 3" :class="step === 3 ? 'text-brand-500 font-bold border-b-2 border-brand-500 pb-4' : 'text-gray-400 pb-4'" class="text-sm">
                    3. Vehicle Specs
                </button>
                <button type="button" @click="step = 4" :class="step === 4 ? 'text-brand-500 font-bold border-b-2 border-brand-500 pb-4' : 'text-gray-400 pb-4'" class="text-sm">
                    4. Media
                </button>
            </div>

            <form action="{{ route('ga-assets.update', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Step 1: General Info -->
                <div x-show="step === 1" class="space-y-5">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">General Information</h3>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Asset Name <span class="text-error-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $asset->name) }}" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Qty <span class="text-error-500">*</span></label>
                            <input type="number" name="qty" value="{{ old('qty', $asset->qty) }}" min="1" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('qty') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Asset Code <span class="text-error-500">*</span></label>
                            <input type="text" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('asset_code') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Inventory Number</label>
                            <input type="text" name="inventory_number" value="{{ old('inventory_number', $asset->inventory_number) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('inventory_number') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Category <span class="text-error-500">*</span></label>
                            <select name="category_id" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Subcategory</label>
                            <select name="subcategory_id"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                <option value="">Select Subcategory</option>
                                @foreach($subcategories as $sub)
                                    <option value="{{ $sub->id }}" {{ old('subcategory_id', $asset->subcategory_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                            @error('subcategory_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Brand</label>
                            <input type="text" name="brand" value="{{ old('brand', $asset->brand) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Model</label>
                            <input type="text" name="model" value="{{ old('model', $asset->model) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Serial Number</label>
                            <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Status <span class="text-error-500">*</span></label>
                            <select name="status" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ old('status', $asset->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Financials & Acquisition -->
                <div x-show="step === 2" class="space-y-5">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Acquisition & Financials</h3>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Acquisition Year</label>
                            <input type="number" name="acquisition_year" value="{{ old('acquisition_year', $asset->acquisition_year) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Acquisition Source</label>
                            <select name="acquisition_source" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                <option value="Pembelian" {{ old('acquisition_source', $asset->acquisition_source) == 'Pembelian' ? 'selected' : '' }}>Pembelian</option>
                                <option value="Hibah" {{ old('acquisition_source', $asset->acquisition_source) == 'Hibah' ? 'selected' : '' }}>Hibah</option>
                                <option value="Sewa" {{ old('acquisition_source', $asset->acquisition_source) == 'Sewa' ? 'selected' : '' }}>Sewa</option>
                                <option value="Lainnya" {{ old('acquisition_source', $asset->acquisition_source) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Purchase Date <span class="text-error-500">*</span></label>
                            <input type="date" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '') }}" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Vendor Name</label>
                            <input type="text" name="vendor_name" value="{{ old('vendor_name', $asset->vendor_name) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Invoice Number</label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number', $asset->invoice_number) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Purchase Price (Rp) <span class="text-error-500">*</span></label>
                            <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price) }}" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Additional Cost (Rp)</label>
                            <input type="number" step="0.01" name="additional_cost" value="{{ old('additional_cost', $asset->additional_cost) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Warranty (Months)</label>
                            <input type="number" name="warranty_period_month" value="{{ old('warranty_period_month', $asset->warranty_period_month) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Warranty Expiry Date</label>
                            <input type="date" name="warranty_expiry_date" value="{{ old('warranty_expiry_date', $asset->warranty_expiry_date ? $asset->warranty_expiry_date->format('Y-m-d') : '') }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>
                    </div>
                </div>

                <!-- Step 3: Vehicle Specs -->
                <div x-show="step === 3" class="space-y-5">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Vehicle Details (Optional)</h3>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">License Plate</label>
                            <input type="text" name="vehicle[license_plate]" value="{{ old('vehicle.license_plate', optional($asset->vehicle)->license_plate) }}" placeholder="e.g. B 1234 ABC"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Chassis Number</label>
                            <input type="text" name="vehicle[chassis_number]" value="{{ old('vehicle.chassis_number', optional($asset->vehicle)->chassis_number) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Engine Number</label>
                            <input type="text" name="vehicle[engine_number]" value="{{ old('vehicle.engine_number', optional($asset->vehicle)->engine_number) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">BPKB Number</label>
                            <input type="text" name="vehicle[bpkb_number]" value="{{ old('vehicle.bpkb_number', optional($asset->vehicle)->bpkb_number) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">STNK Number</label>
                            <input type="text" name="vehicle[stnk_number]" value="{{ old('vehicle.stnk_number', optional($asset->vehicle)->stnk_number) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Tax Due Date</label>
                            <input type="date" name="vehicle[tax_due_date]" value="{{ old('vehicle.tax_due_date', optional($asset->vehicle)->tax_due_date ? optional($asset->vehicle)->tax_due_date->format('Y-m-d') : '') }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Last Service Date</label>
                            <input type="date" name="vehicle[last_service_date]" value="{{ old('vehicle.last_service_date', optional($asset->vehicle)->last_service_date ? optional($asset->vehicle)->last_service_date->format('Y-m-d') : '') }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Last Odometer Reading (km)</label>
                            <input type="number" name="vehicle[last_odometer]" value="{{ old('vehicle.last_odometer', optional($asset->vehicle)->last_odometer) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Insurance Policy Number</label>
                            <input type="text" name="vehicle[insurance_policy_number]" value="{{ old('vehicle.insurance_policy_number', optional($asset->vehicle)->insurance_policy_number) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Insurance Expiry Date</label>
                            <input type="date" name="vehicle[insurance_expiry_date]" value="{{ old('vehicle.insurance_expiry_date', optional($asset->vehicle)->insurance_expiry_date ? optional($asset->vehicle)->insurance_expiry_date->format('Y-m-d') : '') }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Fuel Consumption (km/l)</label>
                            <input type="number" step="0.1" name="vehicle[fuel_consumption]" value="{{ old('vehicle.fuel_consumption', optional($asset->vehicle)->fuel_consumption) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>
                    </div>
                </div>

                <!-- Step 4: Media -->
                <div x-show="step === 4" class="space-y-5">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Asset Media</h3>
                    @if($asset->image)
                        <div class="mb-4">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Current Image</label>
                            <img src="{{ Storage::disk('public')->url($asset->image) }}" class="h-32 w-32 rounded-lg object-cover border border-gray-250">
                        </div>
                    @endif
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Replace Image</label>
                        <input type="file" name="image" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-500/10 dark:file:text-brand-400" />
                    </div>
                </div>

                <!-- Wizard Actions -->
                <div class="flex items-center justify-between mt-8 border-t border-gray-200 dark:border-gray-800 pt-5">
                    <div>
                        <button type="button" x-show="step > 1" @click="prevStep()" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Previous
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('ga-assets.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                        <button type="button" x-show="step < totalSteps" @click="nextStep()" class="inline-flex justify-center rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600 transition">
                            Next
                        </button>
                        <button type="submit" x-show="step === totalSteps" class="inline-flex justify-center rounded-lg bg-success-500 px-6 py-2 text-sm font-medium text-white hover:bg-success-600 transition">
                            Update GA Asset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
