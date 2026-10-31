@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Create Asset Transfer" :items="[['label' => 'Asset Transfers', 'url' => route('asset-transfers.index')], ['label' => 'Create']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Submit a transfer request to relocate an asset to another company, department, or office.
                </p>
            </div>
            <a href="{{ route('asset-transfers.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Transfers
            </a>
        </div>

        @if(session('error'))
            <x-ui.alert variant="danger" message="{{ session('error') }}" />
        @endif

        @php
            $assetOptions = array_merge(
                [['id' => '', 'text' => 'Select Available Asset']],
                $assets->map(fn($a) => [
                    'id' => $a->id,
                    'text' => $a->asset_tag . ' - ' . optional($a->product->brand)->name . ' ' . (optional($a->product)->model ?? $a->device_name) . ' (' . ($a->department?->company?->name ?? 'No Company') . ')'
                ])->toArray()
            );
            $companyOptions = array_merge(
                [['id' => '', 'text' => 'Select Destination Company']],
                $companies->map(fn($c) => ['id' => $c->id, 'text' => $c->name . ' (' . $c->code . ')'])->toArray()
            );
        @endphp

        <form action="{{ route('asset-transfers.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card: Transfer Scope & Destination --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Relocation Routing</h3>
                                <p class="text-[11px] text-gray-400">Select target hardware and choose destination entity</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <x-form.searchable-select
                                name="asset_id"
                                label="Asset to Transfer"
                                :options="$assetOptions"
                                :selected="old('asset_id', $selectedAsset?->id)"
                                placeholder="Select Available Asset"
                                required="true"
                            />
                            @error('asset_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <x-form.searchable-select
                                name="to_company_id"
                                label="Destination Company"
                                :options="$companyOptions"
                                :selected="old('to_company_id')"
                                placeholder="Select Destination Company"
                                required="true"
                            />
                            @error('to_company_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Justification / Reason for Transfer
                            </label>
                            <textarea name="reason" rows="3" placeholder="Reason for inter-company, project, or organizational relocation..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('reason') }}</textarea>
                            @error('reason') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Column: Action & Help --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Submission</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                            Needs Approval
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Submit Transfer Request
                        </button>

                        <a href="{{ route('asset-transfers.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Policy Notice Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-4 text-xs text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 space-y-2 shadow-sm">
                    <div class="flex items-center gap-2 font-semibold text-gray-800 dark:text-gray-200">
                        <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                        Transfer Procedure
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        Submitted transfers remain in <strong>Pending</strong> state until approved by an IT Administrator or department head.
                    </p>
                </div>
            </div>
        </form>
    </div>
@endsection
