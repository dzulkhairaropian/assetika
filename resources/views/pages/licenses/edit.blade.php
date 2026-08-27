@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Edit License" :items="[['label' => 'Licenses', 'url' => route('licenses.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Update software or hardware appliance license details for {{ $license->name }}.
    </p>

    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <form action="{{ route('licenses.update', $license) }}" method="POST" class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_340px]">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                        <i data-feather="key" class="w-4 h-4 text-brand-500"></i> License Information
                    </h3>
                    <div class="grid grid-cols-1 gap-x-4 gap-y-3 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $license->name) }}" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Product Key</label>
                            <input type="text" name="product_key" value="{{ old('product_key', $license->product_key) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Seats <span class="text-red-500">*</span></label>
                            <input type="number" name="seats" value="{{ old('seats', $license->seats) }}" min="1" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">License Email</label>
                            <input type="email" name="license_email" value="{{ old('license_email', $license->license_email) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Manufacturer / Brand</label>
                            <select name="manufacturer"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                <option value="">-- Select Brand --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->name }}" {{ old('manufacturer', $license->manufacturer) == $brand->name ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Purchase Date</label>
                            <input type="date" name="purchase_date" value="{{ old('purchase_date', $license->purchase_date?->format('Y-m-d')) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Expiration Date</label>
                            <input type="date" name="expiration_date" value="{{ old('expiration_date', $license->expiration_date?->format('Y-m-d')) }}"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Purchase Cost (Rp)</label>
                            <input type="number" name="purchase_cost" value="{{ old('purchase_cost', $license->purchase_cost) }}" step="0.01"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea name="notes" rows="3"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('notes', $license->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i data-feather="save" class="w-4 h-4 text-brand-500"></i>
                    Actions
                </h3>
                <div class="space-y-2.5">
                    <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        Update License
                    </button>
                    <a href="{{ route('licenses.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
