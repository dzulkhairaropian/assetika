@extends('layouts.app')

@section('content')
    @php
        $selectedBrand = old('manufacturer');
        $selectedAssetId = old('asset_id');
    @endphp

    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create License" :items="[['label' => 'Licenses', 'url' => route('licenses.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Add a new software or appliance license (e.g. FortiGate, Cisco, Microsoft 365) to inventory.
        </p>

        @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

        <form action="{{ route('licenses.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-8">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-lucide="key" class="w-5 h-5 text-brand-500"></i> License Information
                        </h3>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Name <span class="text-error-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Microsoft 365 Business Premium" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                                @error('name') <p class="mt-1 text-xs text-error-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product Key / Serial</label>
                                <input type="text" name="product_key" value="{{ old('product_key') }}" placeholder="XXXXX-XXXXX-XXXXX"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-mono text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Seats <span class="text-error-500">*</span></label>
                                <input type="number" name="seats" value="{{ old('seats', 1) }}" min="1" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                                @error('seats') <p class="mt-1 text-xs text-error-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">License Email</label>
                                <input type="email" name="license_email" value="{{ old('license_email') }}" placeholder="licenses@company.com"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                            </div>
                            <div>
                                <x-form.searchable-select name="manufacturer" label="Manufacturer / Brand" :options="$brandOpts" :selected="$selectedBrand" placeholder="Select Brand" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Purchase Date</label>
                                <input type="date" name="purchase_date" value="{{ old('purchase_date') }}"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Expiration Date</label>
                                <input type="date" name="expiration_date" value="{{ old('expiration_date') }}"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Purchase Cost (Rp)</label>
                                <input type="number" name="purchase_cost" value="{{ old('purchase_cost') }}" step="0.01" placeholder="0.00"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                            </div>
                            <div>
                                <x-form.searchable-select name="asset_id" label="Link to Asset / Hardware" :options="$assetOpts" :selected="$selectedAssetId" placeholder="Select Hardware" />
                            </div>
                            <div class="lg:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Notes</label>
                                <textarea name="notes" rows="3" placeholder="Additional notes or hardware target..."
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500">{{ old('notes') }}</textarea>
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
                            Save License
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center items-center gap-2 rounded-xl border border-brand-500 bg-white px-4 py-2.5 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:border-brand-500 dark:bg-gray-950 dark:text-brand-400 transition">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            Save & Create Another
                        </button>
                        <a href="{{ route('licenses.index') }}" class="inline-flex w-full justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
