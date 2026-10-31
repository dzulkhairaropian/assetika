@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Asset Check-in" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)], ['label' => 'Check-in']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Receive returned asset <strong class="font-mono text-gray-900 dark:text-white">{{ $asset->asset_tag }}</strong> from <strong class="text-gray-900 dark:text-white">{{ $asset->currentCheckout->checkedOutToUser->name ?? 'User' }}</strong>.
                </p>
            </div>
            <a href="{{ route('assets.show', $asset) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Asset
            </a>
        </div>

        @if(session('error'))
            <x-ui.alert variant="danger" message="{{ session('error') }}" />
        @endif

        @php
            $readyStatus = $statuses->where('name', 'Ready to Deploy')->first();
            $defaultStatusId = $readyStatus ? $readyStatus->id : ($statuses->first()?->id ?? '');
            $statusOptions = $statuses->map(fn($st) => ['id' => $st->id, 'text' => $st->name])->toArray();
            $supervisorOptions = array_merge(
                [['id' => '', 'text' => 'Select Supervisor (Optional)']],
                $supervisors->map(fn($s) => ['id' => $s->id, 'text' => $s->name . ($s->department ? ' - ' . $s->department->name : '')])->toArray()
            );
        @endphp

        <form action="{{ route('assets.checkin.process', $asset) }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card: Return Details --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Check-in Verification</h3>
                                <p class="text-[11px] text-gray-400">Physical condition, updated asset status, and inspection details</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <x-form.date-picker
                                id="checked_in_at"
                                name="checked_in_at"
                                label="Check-in Date"
                                placeholder="Select check-in date"
                                defaultDate="{{ old('checked_in_at', date('Y-m-d')) }}"
                            />
                            @error('checked_in_at') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select
                                name="status_id"
                                label="Status After Check-in"
                                :options="$statusOptions"
                                :selected="old('status_id', $defaultStatusId)"
                                placeholder="Select Status"
                                required="true"
                            />
                            @error('status_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <x-form.searchable-select
                                name="supervisor_user_id"
                                label="Supervisor / Knowing Party (Optional)"
                                :options="$supervisorOptions"
                                :selected="old('supervisor_user_id', $asset->currentCheckout?->supervisor_user_id)"
                                placeholder="Select Supervisor / Manager"
                            />
                            @error('supervisor_user_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Physical Condition <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="condition_report" value="{{ old('condition_report', 'Good') }}" required placeholder="e.g. Good, Minor Scratches, Screen flickering"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('condition_report') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Stripped Condition --}}
                        <div class="lg:col-span-2 rounded-xl border border-amber-200/70 bg-amber-50/50 p-3.5 dark:border-amber-900/30 dark:bg-amber-950/20">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_components_stripped" value="1" {{ old('is_components_stripped', $asset->is_components_stripped) ? 'checked' : '' }}
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                <div>
                                    <span class="text-xs font-semibold text-gray-900 dark:text-white">Components Stripped / Skeleton Unit (RAM & SSD removed)</span>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Check this box if the asset is returned in an empty state (RAM, SSD, or parts harvested).</p>
                                </div>
                            </label>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Return Notes / Peripherals Included
                            </label>
                            <textarea name="checkin_notes" rows="3" placeholder="Condition remarks, returned accessories (charger, bag, mouse), or inspection notes..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('checkin_notes') }}</textarea>
                            @error('checkin_notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Holder Card --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 text-emerald-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Action</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                            Check-in
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Confirm Return to Inventory
                        </button>

                        <a href="{{ route('assets.show', $asset) }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Returning Holder Summary --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Current Assignment</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Holder:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $asset->currentCheckout->checkedOutToUser->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Department:</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ $asset->department?->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Out Date:</span>
                            <span class="font-mono text-gray-700 dark:text-gray-300">{{ $asset->currentCheckout->checked_out_at?->format('d M Y') ?? '-' }}</span>
                        </div>
                        @if($asset->currentCheckout->expected_return_date)
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Due Target:</span>
                                <span class="font-mono {{ $asset->currentCheckout->isOverdue() ? 'text-rose-600 font-bold' : 'text-blue-600' }}">
                                    {{ $asset->currentCheckout->expected_return_date->format('d M Y') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
