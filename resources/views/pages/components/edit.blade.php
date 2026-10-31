@extends('layouts.app')

@section('content')
    @php
        $selectedProductId = old('product_id', $component->product_id);
        $selectedStatusId = old('status_id', $component->status_id);
        $selectedVendorId = old('vendor_id', $component->vendor_id);
        $selectedLocationId = old('location_id', $component->location_id);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Edit Component" :items="[['label' => 'Components', 'url' => route('components.index')], ['label' => 'Edit']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Update component details for <strong class="font-mono text-gray-900 dark:text-white">{{ $component->component_tag ?? $component->name }}</strong>.
                </p>
            </div>
            <a href="{{ route('components.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Components
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('components.update', $component) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf
            @method('PUT')

            {{-- Left Column: Core Details --}}
            <div class="space-y-6">
                {{-- Card 1: Identification & Model --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Component Identification</h3>
                                <p class="text-[11px] text-gray-400">Category model, serial/batch number, and storage location</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <x-form.searchable-select name="product_id" label="Product Model / Category" :options="$productOpts" :selected="$selectedProductId" placeholder="Select Product Model" required="true" />
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
                            <input type="text" name="component_tag" value="{{ old('component_tag', $component->component_tag) }}" placeholder="e.g. DB/BATT/260912/001"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('component_tag') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Model Number / Specification
                            </label>
                            <input type="text" name="model_number" value="{{ old('model_number', $component->model_number) }}" placeholder="e.g. Latitude 3410 Battery"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Serial Number / Batch No.
                            </label>
                            <input type="text" name="serial" value="{{ old('serial', $component->serial) }}" placeholder="Serial / Batch number"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <x-form.searchable-select name="location_id" label="Storage Location" :options="$locationOpts" :selected="$selectedLocationId" placeholder="Select Location" />
                        </div>

                        <div>
                            <x-form.searchable-select name="vendor_id" label="Vendor / Supplier" :options="$vendorOpts" :selected="$selectedVendorId" placeholder="Select Vendor" />
                        </div>
                    </div>
                </div>

                {{-- Card 2: Inventory Stock & Alert --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i data-lucide="package" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Quantity & Threshold</h3>
                                <p class="text-[11px] text-gray-400">Stock count available and low quantity threshold alert</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Total Qty <span class="text-error-500">*</span>
                            </label>
                            <input type="number" name="qty" value="{{ old('qty', $component->qty) }}" min="0" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('qty') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Minimum Stock Alert
                            </label>
                            <input type="number" name="min_qty" value="{{ old('min_qty', $component->min_qty) }}" min="0"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Internal Notes
                            </label>
                            <textarea name="notes" rows="3" placeholder="Compatibility details, packaging reference, or supplier notes..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('notes', $component->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Financials --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                {{-- Action Card --}}
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
                            Update Component
                        </button>

                        <a href="{{ route('components.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Financial & Warranty Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-500"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Procurement & Value</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <x-form.date-picker
                                id="purchase_date"
                                name="purchase_date"
                                label="Purchase Date"
                                placeholder="Select purchase date"
                                defaultDate="{{ old('purchase_date', optional($component->purchase_date)->format('Y-m-d')) }}"
                            />
                            @error('purchase_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.date-picker
                                id="release_date"
                                name="release_date"
                                label="Release / Installation Date"
                                placeholder="Select release date"
                                defaultDate="{{ old('release_date', optional($component->release_date)->format('Y-m-d')) }}"
                            />
                            @error('release_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Purchase Cost (Unit)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                                <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost', $component->purchase_cost) }}" placeholder="0.00"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Warranty Period (Months)
                            </label>
                            <input type="number" name="warranty_months" value="{{ old('warranty_months', $component->warranty_months) }}" min="0" placeholder="e.g. 12"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
