@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Complete Asset Maintenance" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)]]" />

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-500/10">
                    <i data-feather="check-circle" class="h-5 w-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Complete Maintenance</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Recording completion of service for asset <strong>{{ $asset->asset_tag }}</strong> ({{ $asset->device_name }}).</p>
                </div>
            </div>

            <form action="{{ route('assets.maintenance.complete.process', [$asset, $maintenance]) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Section: Service History Summary (Read-Only) -->
                <div class="rounded-lg bg-gray-50 p-3.5 dark:bg-gray-950 border border-gray-100 dark:border-gray-800">
                    <h4 class="text-xs font-semibold text-gray-900 dark:text-white mb-2 uppercase tracking-wider">Maintenance Start Info</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Service Provider:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $maintenance->service_provider ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Start Date:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $maintenance->start_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Initial Estimated Cost:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</span>
                        </div>
                        @if($maintenance->description)
                            <div class="md:col-span-3">
                                <span class="text-gray-400 block text-[11px]">Description of Issue:</span>
                                <p class="text-gray-800 dark:text-gray-200 mt-0.5">{{ $maintenance->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Section: Completion Details -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <i data-feather="info" class="w-4 h-4 text-brand-500"></i>
                        Completion Details
                    </h3>
                    <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
                        <!-- Completion Date -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Completion Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="completion_date" value="{{ old('completion_date', date('Y-m-d')) }}" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            @error('completion_date') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Actual/Final Cost -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Final Actual Cost (Rp)
                            </label>
                            <input type="number" name="cost" value="{{ old('cost', $maintenance->cost) }}" step="0.01" min="0" placeholder="e.g. 150000"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            @error('cost') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Completion Notes -->
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Completion Notes / Service Report
                            </label>
                            <textarea name="notes" rows="3" placeholder="Describe the resolution or work done during service..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('notes', $maintenance->notes) }}</textarea>
                            @error('notes') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <a href="{{ route('assets.show', $asset) }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        Complete Maintenance
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
