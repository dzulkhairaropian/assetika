@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Edit Component" :items="[['label' => 'Components', 'url' => route('components.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Update component details for {{ $component->name }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('components.update', $component) }}" method="POST" class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_340px]">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                            <i data-lucide="cpu" class="w-4 h-4 text-brand-500"></i>
                            Component Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-4 gap-y-3 lg:grid-cols-2">
                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Master Product
                                </label>
                                <x-form.searchable-select
                                    name="product_id"
                                    :options="$products->map(fn($p) => ['value' => $p->id, 'label' => $p->name . ($p->brand ? ' ('.$p->brand->name.')' : '')])->toArray()"
                                    :selected="old('product_id', $component->product_id)"
                                    placeholder="Select or search Master Product..."
                                />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Component Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $component->name) }}" placeholder="e.g., Baterai Latitude 3410" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Category
                                </label>
                                <x-form.searchable-select
                                    name="category_id"
                                    :options="$categories->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()"
                                    :selected="old('category_id', $component->category_id)"
                                    placeholder="Select Category..."
                                />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Manufacturer / Brand
                                </label>
                                <x-form.searchable-select
                                    name="manufacturer"
                                    :options="$brands->map(fn($b) => ['value' => $b->name, 'label' => $b->name])->toArray()"
                                    :selected="old('manufacturer', $component->manufacturer)"
                                    placeholder="Select Brand..."
                                />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Model Number
                                </label>
                                <input type="text" name="model_number" value="{{ old('model_number', $component->model_number) }}" placeholder="e.g., CMK16GX4M2B3200C16"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Serial Number / Batch No.
                                </label>
                                <input type="text" name="serial" value="{{ old('serial', $component->serial) }}" placeholder="Serial / Batch number"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Total Qty <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="qty" value="{{ old('qty', $component->qty) }}" min="0" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                @error('qty') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Min Qty Alert
                                </label>
                                <input type="number" name="min_qty" value="{{ old('min_qty', $component->min_qty) }}" min="0"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Purchase Cost (Rp)
                                </label>
                                <input type="number" name="purchase_cost" value="{{ old('purchase_cost', $component->purchase_cost) }}" step="0.01"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Purchase Date
                                </label>
                                <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($component->purchase_date)->format('Y-m-d')) }}"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Warranty (Months)
                                </label>
                                <input type="number" name="warranty_months" value="{{ old('warranty_months', $component->warranty_months) }}" min="0" placeholder="e.g. 12"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Vendor / Supplier
                                </label>
                                <x-form.searchable-select
                                    name="vendor_id"
                                    :options="$vendors->map(fn($v) => ['value' => $v->id, 'label' => $v->name])->toArray()"
                                    :selected="old('vendor_id', $component->vendor_id)"
                                    placeholder="Select Vendor..."
                                />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Location
                                </label>
                                <x-form.searchable-select
                                    name="location_id"
                                    :options="$locations->map(fn($l) => ['value' => $l->id, 'label' => $l->name])->toArray()"
                                    :selected="old('location_id', $component->location_id)"
                                    placeholder="Select Location..."
                                />
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Notes
                                </label>
                                <textarea name="notes" rows="3" placeholder="Additional notes..."
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('notes', $component->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                        <i data-lucide="save" class="w-4 h-4 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            Update Component
                        </button>
                        <a href="{{ route('components.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
