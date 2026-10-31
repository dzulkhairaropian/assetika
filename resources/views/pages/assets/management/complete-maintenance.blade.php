@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Complete Asset Maintenance" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)], ['label' => 'Complete Maintenance']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Record service completion and final costs for asset <strong class="font-mono text-gray-900 dark:text-white">{{ $asset->asset_tag }}</strong>.
                </p>
            </div>
            <a href="{{ route('assets.show', $asset) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Asset
            </a>
        </div>

        <form action="{{ route('assets.maintenance.complete.process', [$asset, $maintenance]) }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card 1: Maintenance History Overview (Read-Only) --}}
                <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-gray-950/50 md:p-6">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-brand-500"></i>
                        Dispatched Ticket Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Vendor / Provider:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $maintenance->service_provider ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Start Date:</span>
                            <span class="font-mono text-gray-800 dark:text-gray-200">{{ $maintenance->start_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Estimated Cost:</span>
                            <span class="font-mono text-gray-800 dark:text-gray-200">Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</span>
                        </div>
                        @if($maintenance->description)
                            <div class="md:col-span-3 pt-2 border-t border-gray-200/50 dark:border-gray-800">
                                <span class="text-gray-400 block text-[11px]">Reported Issue:</span>
                                <p class="text-gray-800 dark:text-gray-200 mt-0.5 leading-relaxed">{{ $maintenance->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card 2: Completion Entry --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Completion Details</h3>
                                <p class="text-[11px] text-gray-400">Actual completion date, final invoices, and restored status</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <x-form.date-picker
                                id="completion_date"
                                name="completion_date"
                                label="Completion Date"
                                placeholder="Select completion date"
                                defaultDate="{{ old('completion_date', date('Y-m-d')) }}"
                            />
                            @error('completion_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Final Actual Cost <span class="text-error-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                                <input type="number" name="cost" value="{{ old('cost', $maintenance->cost) }}" step="0.01" min="0" required placeholder="0.00"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs font-mono text-gray-800 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            </div>
                            @error('cost') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Status After Maintenance <span class="text-error-500">*</span>
                            </label>
                            <select name="status_id" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                @foreach($statuses as $st)
                                    <option value="{{ $st->id }}" {{ old('status_id', $st->name == 'Ready to Deploy' ? $st->id : '') == $st->id ? 'selected' : '' }}>
                                        {{ $st->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Completion Notes / Repair Resolution
                            </label>
                            <textarea name="notes" rows="3" placeholder="Summary of work completed, replaced components (e.g. replaced keyboard), or test outcomes..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('notes') }}</textarea>
                            @error('notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Action Card --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Action</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                            Complete
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Complete Maintenance
                        </button>

                        <a href="{{ route('assets.show', $asset) }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
