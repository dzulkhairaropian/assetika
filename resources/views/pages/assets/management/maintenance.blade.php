@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Send Asset to Maintenance" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)], ['label' => 'Maintenance']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Log maintenance ticket and dispatch asset <strong class="font-mono text-gray-900 dark:text-white">{{ $asset->asset_tag }}</strong> for repair or service.
                </p>
            </div>
            <a href="{{ route('assets.show', $asset) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Asset
            </a>
        </div>

        <form action="{{ route('assets.maintenance.process', $asset) }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card: Maintenance Ticket Details --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                <i data-lucide="wrench" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Service & Repair Details</h3>
                                <p class="text-[11px] text-gray-400">Vendor workshop, start timeline, issue diagnosis, and initial estimates</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Service Provider / Vendor Workshop <span class="text-error-500">*</span>
                            </label>
                            <input list="serviceProviderList" name="service_provider" value="{{ old('service_provider') }}" required placeholder="Type or select service provider..."
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            <datalist id="serviceProviderList">
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->name }}">
                                @endforeach
                            </datalist>
                            @error('service_provider') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.date-picker
                                id="start_date"
                                name="start_date"
                                label="Service Start Date"
                                placeholder="Select start date"
                                defaultDate="{{ old('start_date', date('Y-m-d')) }}"
                            />
                            @error('start_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Initial Estimated Cost
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                                <input type="number" name="cost" value="{{ old('cost') }}" step="0.01" min="0" placeholder="0.00"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            </div>
                            @error('cost') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Description of Issue / Service Required <span class="text-error-500">*</span>
                            </label>
                            <textarea name="description" rows="3" required placeholder="Describe issue symptom (e.g. Broken keyboard, OS boot failure, battery bloat)..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('description') }}</textarea>
                            @error('description') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Internal Notes (Optional)
                            </label>
                            <textarea name="notes" rows="2" placeholder="Additional logistics notes or courier dispatch reference..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Hardware Info --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="wrench" class="w-4 h-4 text-amber-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Action</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                            Service
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Send to Maintenance
                        </button>

                        <a href="{{ route('assets.show', $asset) }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Hardware Summary Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Hardware Info</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Asset Tag:</span>
                            <span class="font-mono font-bold text-brand-600 dark:text-brand-400">{{ $asset->asset_tag }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Model:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Serial Number:</span>
                            <span class="font-mono text-gray-700 dark:text-gray-300">{{ $asset->serial_number ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
