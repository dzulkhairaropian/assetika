@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Edit Group" :items="[['label' => 'Groups', 'url' => route('groups.index')], ['label' => 'Edit']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Update corporate holding structure, affiliated companies, or logo for <strong class="text-gray-900 dark:text-white">{{ $group->name }}</strong>.
                </p>
            </div>
            <a href="{{ route('groups.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Groups
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('groups.update', $group) }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf
            @method('PUT')

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card: Holding Details --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Holding Group Profile</h3>
                                <p class="text-[11px] text-gray-400">Corporate group identity, code, and affiliated companies</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Group Code
                            </label>
                            <input type="text" name="code" value="{{ old('code', $group->code) }}" placeholder="e.g. G-HOLDING"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-mono uppercase text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('code') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Group Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $group->name) }}" placeholder="e.g. Duta Bangsa Group" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Description & Charter
                            </label>
                            <textarea name="description" rows="3" placeholder="Holding structure or business group profile..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('description', $group->description) }}</textarea>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Affiliated Operating Companies
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                @forelse($companies as $company)
                                    <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white dark:hover:bg-gray-900 cursor-pointer text-xs transition">
                                        <input type="checkbox" name="company_ids[]" value="{{ $company->id }}"
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900"
                                            {{ in_array($company->id, old('company_ids', $group->companies->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $company->name }}</span>
                                        <span class="text-[10px] text-gray-400 font-mono">({{ $company->code }})</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-400 p-2">No companies available.</p>
                                @endforelse
                            </div>
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
                            Update Group
                        </button>

                        <a href="{{ route('groups.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="image" class="w-4 h-4 text-brand-500"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Group Logo</h3>
                    </div>

                    @if($group->logo)
                        <div class="mb-3 flex items-center justify-center p-3 rounded-xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800">
                            <img src="{{ Storage::url($group->logo) }}" alt="{{ $group->name }}" class="h-10 w-auto object-contain">
                        </div>
                    @endif

                    <div>
                        <input type="file" name="logo" accept="image/*"
                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950/30 dark:file:text-brand-400" />
                        @error('logo') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
