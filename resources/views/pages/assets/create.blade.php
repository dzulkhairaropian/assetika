@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Create IT Asset" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => 'Create']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Register a new hardware asset, assign location and holder, and define lifecycle specifications.
                </p>
            </div>
            <a href="{{ route('assets.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Inventory
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        @if(isset($clonedAsset) && $clonedAsset)
            <div class="flex items-center gap-3 rounded-xl border border-brand-200 bg-brand-50 p-3.5 text-brand-900 dark:border-brand-900/30 dark:bg-brand-500/10 dark:text-brand-300">
                <i data-lucide="copy" class="h-4 w-4 shrink-0 text-brand-500"></i>
                <div class="text-xs">
                    <strong>Cloning Asset:</strong> Pre-filled from <strong>{{ $clonedAsset->asset_tag }}</strong> ({{ optional($clonedAsset->product->brand)->name }} {{ optional($clonedAsset->product)->model }}).
                    Enter a new unique <strong>Serial Number</strong>. The system will automatically generate a new Asset Tag and Device Name upon saving.
                </div>
            </div>
        @endif

        <form action="{{ route('assets.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]"
              x-data="{
                  status: '{{ old('status_id', '') }}',
                  isSold() {
                      const sel = document.querySelector('select[name=status_id]');
                      if (!sel) return false;
                      const text = sel.options[sel.selectedIndex]?.text?.toLowerCase() || '';
                      return text.includes('sold');
                  }
              }">
            @csrf

            {{-- Left Column: Core Cards --}}
            <div class="space-y-6">
                {{-- Card 1: Identification & Assignment --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="tag" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Identification & Assignment</h3>
                                <p class="text-[11px] text-gray-400">Hardware model, serial number, and organizational placement</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <x-form.searchable-select name="product_id" label="Product Model" :options="$productOpts" :selected="old('product_id', $clonedAsset?->product_id)" placeholder="Select Product Model" required="true" />
                            @error('product_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            @php
                                $readyToDeployStatus = $statuses->where('name', 'Ready to Deploy')->first();
                                $defaultStatusId = $readyToDeployStatus ? $readyToDeployStatus->id : '';
                            @endphp
                            <x-form.searchable-select name="status_id" label="Initial Status" :options="$statusOpts" :selected="old('status_id', $defaultStatusId)" placeholder="Select Status" required="true" />
                            @error('status_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Serial Number (S/N) <span class="text-error-500">*</span>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <input type="text" name="serial_number" value="{{ old('serial_number') }}" placeholder="Enter hardware serial number" required {{ isset($clonedAsset) && $clonedAsset ? 'autofocus' : '' }}
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                <button type="button" onclick="generateSN()" title="Generate Random Serial Number" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-brand-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                                    <i data-lucide="shuffle" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            @error('serial_number') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select name="department_id" label="Department" :options="$deptOpts" :selected="old('department_id', $clonedAsset?->department_id)" placeholder="Select Department" required="true" />
                            @error('department_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select name="location_id" label="Physical Location" :options="$locationOpts" :selected="old('location_id', $clonedAsset?->location_id)" placeholder="Select Location" />
                            @error('location_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Assigned User Name (Holder)
                            </label>
                            <input type="text" name="user_name" value="{{ old('user_name') }}" placeholder="e.g. Grace Ega / IT Staff"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                            @error('user_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="release_date" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Release / Deployment Date <span class="text-error-500">*</span>
                            </label>
                            <x-form.date-picker
                                id="release_date"
                                name="release_date"
                                placeholder="Select release date"
                                defaultDate="{{ old('release_date', $clonedAsset?->release_date ? $clonedAsset->release_date->format('Y-m-d') : date('Y-m-d')) }}"
                            />
                            @error('release_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Technical Specifications & Licenses --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Technical Specs & Software</h3>
                                <p class="text-[11px] text-gray-400">Hardware configuration, installed OS, and license keys</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <x-form.searchable-select name="operating_system_id" label="Operating System" :options="$osOpts" :selected="old('operating_system_id', $clonedAsset?->operating_system_id)" placeholder="Select OS" />
                            @error('operating_system_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select name="vendor_id" label="Procurement Vendor" :options="$vendorOpts" :selected="old('vendor_id', $clonedAsset?->vendor_id)" placeholder="Select Vendor" />
                            @error('vendor_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Windows License Key
                            </label>
                            <input type="text" name="license_windows" value="{{ old('license_windows', $clonedAsset?->license_windows) }}" placeholder="e.g. W269N-WFGWX-YVC9B-4J6C9-T83GX"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('license_windows') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Office License Key
                            </label>
                            <input type="text" name="license_office" value="{{ old('license_office', $clonedAsset?->license_office) }}" placeholder="Enter Office license / account"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('license_office') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Specification Summary
                            </label>
                            <textarea name="specification" rows="3" placeholder="e.g. Intel Core i5-1135G7, 16GB DDR4 RAM, 512GB NVMe SSD, 14 inch FHD Display"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('specification', $clonedAsset?->specification) }}</textarea>
                            @error('specification') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Included Accessories
                            </label>
                            <input type="text" name="accessories" value="{{ old('accessories', $clonedAsset?->accessories) }}" placeholder="e.g. 65W USB-C Adapter, Laptop Bag, Wireless Mouse"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('accessories') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Stripped Condition Notice --}}
                        <div class="lg:col-span-2 rounded-xl border border-amber-200/70 bg-amber-50/50 p-3.5 dark:border-amber-900/30 dark:bg-amber-950/20">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_components_stripped" value="1" {{ old('is_components_stripped', $clonedAsset?->is_components_stripped) ? 'checked' : '' }}
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                <div>
                                    <span class="text-xs font-semibold text-gray-900 dark:text-white">Components Stripped / Skeleton Unit (RAM & SSD removed)</span>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Check this box if this device is currently stored without internal components for maintenance or donor parts.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Additional Notes --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-3 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Internal Remarks / Notes</h3>
                    </div>
                    <textarea name="notes" rows="3" placeholder="Optional internal notes, condition remarks, or purchase background..."
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('notes', $clonedAsset?->notes) }}</textarea>
                    @error('notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Right Column: Actions & Financials --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                {{-- Action Card (Primary on Top) --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Form Actions</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            New Record
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Save Asset
                        </button>

                        <button type="submit" name="create_another" value="1" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-500/30 bg-brand-50/50 px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-100/60 active:scale-[0.98] dark:bg-brand-950/20 dark:text-brand-400 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Save & Add Another
                        </button>

                        <button type="submit" name="is_draft" value="1" formnovalidate class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-gray-400"></i>
                            Save as Draft
                        </button>

                        <a href="{{ route('assets.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Financial & Valuation Card --}}
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
                                defaultDate="{{ old('purchase_date', $clonedAsset?->purchase_date ? $clonedAsset->purchase_date->format('Y-m-d') : '') }}"
                            />
                            @error('purchase_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Purchase Cost
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                                <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost', $clonedAsset?->purchase_cost) }}" placeholder="0.00"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            </div>
                            @error('purchase_cost') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select
                                name="depreciation_id"
                                label="Depreciation Profile"
                                :options="$depreciations->map(fn($d) => ['id' => $d->id, 'text' => $d->name . ' (' . $d->months . ' Mo)'])->toArray()"
                                :selected="old('depreciation_id', $clonedAsset?->depreciation_id)"
                                placeholder="Select Depreciation Profile"
                            />
                            @error('depreciation_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.date-picker
                                id="warranty_expiry_date"
                                name="warranty_expiry_date"
                                label="Warranty Expiry Date"
                                placeholder="Auto-calculated (1y) or select"
                                defaultDate="{{ old('warranty_expiry_date', $clonedAsset?->warranty_expiry_date ? $clonedAsset->warranty_expiry_date->format('Y-m-d') : '') }}"
                            />
                            @error('warranty_expiry_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Collapsible Sale Section (If Sold) --}}
                        <div x-data="{ open: {{ old('sale_price') || old('buyer_name') ? 'true' : 'false' }} }" class="pt-3 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" @click="open = !open" class="flex w-full items-center justify-between text-xs font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="badge-dollar-sign" class="w-3.5 h-3.5 text-gray-400"></i>
                                    Disposal / Sale Details
                                </span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="open" x-transition class="mt-3 space-y-3 pt-1">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Sale Price</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                                        <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price') }}" placeholder="0.00"
                                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Buyer Name</label>
                                    <input type="text" name="buyer_name" value="{{ old('buyer_name') }}" placeholder="e.g. PT Buyer"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                                </div>
                                <div>
                                    <x-form.date-picker id="sale_date" name="sale_date" label="Sale Date" placeholder="Select sale date" defaultDate="{{ old('sale_date') }}" />
                                </div>
                            </div>
                        </div>
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
        const input = document.querySelector('input[name="serial_number"]');
        if (input) input.value = sn;
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
