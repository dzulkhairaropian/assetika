@extends('layouts.app')

@section('content')
    @php
        $selectedGroupId = old('group_id', $company->group_id);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Edit Company" :items="[['label' => 'Companies', 'url' => route('companies.index')], ['label' => 'Edit']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Update corporate details, holding assignment, and logo for <strong class="text-gray-900 dark:text-white">{{ $company->name }}</strong>.
                </p>
            </div>
            <a href="{{ route('companies.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Companies
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('companies.update', $company) }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf
            @method('PUT')

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card: Corporate Identity --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="briefcase" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Corporate Identity</h3>
                                <p class="text-[11px] text-gray-400">Company legal entity, tag prefix, and corporate contact</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Company Code <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="code" value="{{ old('code', $company->code) }}" placeholder="e.g. DB / PTX" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono uppercase text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('code') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Company Legal Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $company->name) }}" placeholder="e.g. PT Duta Bangsa Perkasa" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <x-form.searchable-select name="group_id" label="Holding / Corporate Group (Optional)" :options="$groupOpts" :selected="$selectedGroupId" placeholder="Select Group (Optional)" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Official Email
                            </label>
                            <input type="email" name="email" value="{{ old('email', $company->email) }}" placeholder="contact@company.com"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Phone Number
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" placeholder="+62 21 1234567"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Registered Office Address
                            </label>
                            <textarea name="address" rows="3" placeholder="Full registered corporate address..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('address', $company->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Logo --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                            Editing
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Update Company
                        </button>

                        <a href="{{ route('companies.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Logo Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="image" class="w-4 h-4 text-brand-500"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Brand Logo</h3>
                    </div>

                    @if($company->logo)
                        <div class="mb-3 flex items-center justify-center p-3 rounded-xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800">
                            <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="h-12 w-auto object-contain" />
                        </div>
                    @endif

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                            Upload New Logo (PNG / JPG)
                        </label>
                        <input type="file" name="logo" accept="image/*"
                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950/30 dark:file:text-brand-400" />
                        @error('logo') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
