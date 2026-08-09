@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Create License" />
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <form action="{{ route('licenses.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        @csrf

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="key" class="w-5 h-5 text-brand-500"></i> License Information
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Name <span class="text-error-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Microsoft 365 Business" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Product Key / Serial</label>
                            <input type="text" name="product_key" value="{{ old('product_key') }}" placeholder="XXXXX-XXXXX-XXXXX"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Seats <span class="text-error-500">*</span></label>
                            <input type="number" name="seats" value="{{ old('seats', 1) }}" min="1" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('seats') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">License Email</label>
                            <input type="email" name="license_email" value="{{ old('license_email') }}" placeholder="licenses@company.com"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Manufacturer / Brand</label>
                            <select name="manufacturer"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">-- Select Brand --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->name }}" {{ old('manufacturer') == $brand->name ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Purchase Date</label>
                            <input type="date" name="purchase_date" value="{{ old('purchase_date') }}"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Expiration Date</label>
                            <input type="date" name="expiration_date" value="{{ old('expiration_date') }}"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Purchase Cost (Rp)</label>
                            <input type="number" name="purchase_cost" value="{{ old('purchase_cost') }}" step="0.01" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notes</label>
                            <textarea name="notes" rows="3" placeholder="Additional notes"
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
                        Create License
                    </button>
                    <a href="{{ route('licenses.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

