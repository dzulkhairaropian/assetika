@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Complete Asset Maintenance" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)]]" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="mb-8 flex items-center gap-4 border-b border-gray-100 pb-6 dark:border-gray-800">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-success-50 text-success-500 dark:bg-success-500/10">
                    <i data-feather="check-circle" class="h-6 w-6"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">Complete Maintenance</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Recording completion of service for asset <strong>{{ $asset->asset_tag }}</strong> ({{ $asset->device_name }}).</p>
                </div>
            </div>

            <form action="{{ route('assets.maintenance.complete.process', [$asset, $maintenance]) }}" method="POST" class="space-y-8">
                @csrf

                <!-- Section: Service History Summary (Read-Only) -->
                <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/[0.02] border border-gray-100 dark:border-gray-850">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-white/90 mb-3 uppercase tracking-wider">Maintenance Start Info</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400 block text-xs">Service Provider:</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $maintenance->service_provider ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Start Date:</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $maintenance->start_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Initial Estimated Cost:</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">Rp {{ number_format($maintenance->cost, 2, ',', '.') }}</span>
                        </div>
                        @if($maintenance->description)
                            <div class="md:col-span-3">
                                <span class="text-gray-400 block text-xs">Description of Issue:</span>
                                <p class="text-gray-700 dark:text-gray-300 mt-0.5">{{ $maintenance->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Section: Completion Details -->
                <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                        Completion Details
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <!-- Completion Date -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Completion Date <span class="text-error-500">*</span>
                            </label>
                            <input type="date" name="completion_date" value="{{ old('completion_date', date('Y-m-d')) }}" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('completion_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Actual/Final Cost -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Final Actual Cost (Rp)
                            </label>
                            <input type="number" name="cost" value="{{ old('cost', $maintenance->cost) }}" step="0.01" min="0" placeholder="e.g. 150000"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('cost') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Completion Notes -->
                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Completion Notes / Service Report
                            </label>
                            <textarea name="notes" rows="3" placeholder="Describe the resolution or work done during service..."
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('notes', $maintenance->notes) }}</textarea>
                            @error('notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('assets.show', $asset) }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                        Complete Maintenance
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
