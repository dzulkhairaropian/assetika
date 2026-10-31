@extends('layouts.app')

@section('content')
    @php
        $selectedProductId = old('product_id');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Create Consumable" :items="[['label' => 'Consumables', 'url' => route('consumables.index')], ['label' => 'Create']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Add a new consumable supply (toner, printing paper, peripheral cable) to your inventory.
                </p>
            </div>
            <a href="{{ route('consumables.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Consumables
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('consumables.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf

            {{-- Left Column: Core Cards --}}
            <div class="space-y-6">
                {{-- Card 1: Consumable Item Details --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="package" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Consumable Specifications</h3>
                                <p class="text-[11px] text-gray-400">Item catalog product, initial stock quantity, and reorder levels</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <x-form.searchable-select name="product_id" label="Product / Supply Model" :options="$productOpts" :selected="$selectedProductId" placeholder="Select Product Model" required="true" />
                            @error('product_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Initial In-Stock Quantity <span class="text-error-500">*</span>
                            </label>
                            <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0" required placeholder="0"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('quantity') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Low Stock Alert Threshold <span class="text-error-500">*</span>
                            </label>
                            <input type="number" name="low_threshold" value="{{ old('low_threshold', 5) }}" min="0" required placeholder="5"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('low_threshold') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Storage Notes / Location Details
                            </label>
                            <textarea name="notes" rows="3" placeholder="Cabinet shelf location, compatibility reference, or reorder notes..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('notes') }}</textarea>
                            @error('notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Financials --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            New Record
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Save Consumable
                        </button>

                        <button type="submit" name="create_another" value="1" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-500/30 bg-brand-50/50 px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-100/60 active:scale-[0.98] dark:bg-brand-950/20 dark:text-brand-400 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Save & Add Another
                        </button>

                        <a href="{{ route('consumables.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Cost & Valuation Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-500"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Unit Cost</h3>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                            Purchase Unit Cost
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                            <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost') }}" placeholder="0.00"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>
                        @error('purchase_cost') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
