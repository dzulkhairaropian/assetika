@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create Asset Transfer" :items="[['label' => 'Asset Transfers', 'url' => route('asset-transfers.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Submit a request to move an asset to another company, department, or location.
        </p>

        @if(session('error'))
            <x-ui.alert variant="danger" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('asset-transfers.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-lucide="repeat" class="w-5 h-5 text-brand-500"></i>
                            Transfer Details
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Asset to Transfer <span class="text-error-500">*</span>
                                </label>
                                <select name="asset_id" required class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                    <option value="">-- Select Asset --</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('asset_id', $selectedAsset?->id) == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->asset_tag }} - {{ $asset->name }} ({{ $asset->department?->company?->name ?? 'No Company' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Destination Company <span class="text-error-500">*</span>
                                </label>
                                <select name="to_company_id" required class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                    <option value="">-- Select Destination Company --</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('to_company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }} ({{ $company->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_company_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Reason for Transfer
                                </label>
                                <textarea name="reason" rows="3" placeholder="Reason for inter-company or department relocation"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('reason') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-lucide="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Submit Transfer Request
                        </button>
                        <a href="{{ route('asset-transfers.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
