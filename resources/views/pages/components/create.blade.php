@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create Component" />

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('components.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="cpu" class="w-5 h-5 text-brand-500"></i>
                            Component Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Corsair Vengeance 16GB DDR4" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Category
                                </label>
                                <select name="category_id"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Manufacturer / Brand
                                </label>
                                <select name="manufacturer"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="">-- Select Brand --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->name }}" {{ old('manufacturer') == $brand->name ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Model Number
                                </label>
                                <input type="text" name="model_number" value="{{ old('model_number') }}" placeholder="e.g., CMK16GX4M2B3200C16"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Serial
                                </label>
                                <input type="text" name="serial" value="{{ old('serial') }}" placeholder="Serial number"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Total Qty <span class="text-error-500">*</span>
                                </label>
                                <input type="number" name="qty" value="{{ old('qty', 0) }}" min="0" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('qty') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Min Qty Alert
                                </label>
                                <input type="number" name="min_qty" value="{{ old('min_qty', 0) }}" min="0"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Purchase Cost (Rp)
                                </label>
                                <input type="number" name="purchase_cost" value="{{ old('purchase_cost') }}" step="0.01" placeholder="0.00"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Location
                                </label>
                                <select name="location_id"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="">-- Select Location --</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Notes
                                </label>
                                <textarea name="notes" rows="3" placeholder="Additional notes..."
                                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white/90">
                        <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Create Component
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                            Create & Create New
                        </button>
                        <a href="{{ route('components.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

