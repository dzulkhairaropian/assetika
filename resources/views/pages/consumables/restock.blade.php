@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Restock Consumable" :items="[['label' => 'Consumables', 'url' => route('consumables.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Process stock refill for {{ optional($consumable->product)->name }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form action="{{ route('consumables.restock', $consumable) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Product
                        </label>
                        <input type="text" value="{{ optional($consumable->product)->name }} ({{ optional($consumable->product->brand)->name ?? '' }})" disabled
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-500 dark:border-gray-800 dark:bg-gray-950" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Current Stock Quantity
                        </label>
                        <input type="text" value="{{ $consumable->quantity }} items" disabled
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-500 dark:border-gray-800 dark:bg-gray-950" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Restock Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="quantity" min="1" required placeholder="e.g. 10"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        @error('quantity') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Unit Cost (Leave empty for current cost: Rp {{ number_format($consumable->purchase_cost, 0, ',', '.') }})
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                            <input type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost') }}" placeholder="0.00"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        @error('unit_cost') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Evidence File (Receipt, Invoice PDF/JPG)
                        </label>
                        <input type="file" name="evidence"
                            class="h-9 w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-brand-500/15 dark:file:text-brand-400" />
                        @error('evidence') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Restock Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        @error('transaction_date') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <textarea name="notes" rows="3" placeholder="Restock notes..."
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <a href="{{ route('consumables.index') }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        Process Restock
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
