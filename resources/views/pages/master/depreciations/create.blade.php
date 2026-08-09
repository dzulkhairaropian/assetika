@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Create Depreciation" />
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <form action="{{ route('depreciations.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        @csrf

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="trending-down" class="w-5 h-5 text-brand-500"></i> Depreciation Profile
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Name <span class="text-error-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., 3 Year Straight Line" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Duration (Months) <span class="text-error-500">*</span></label>
                            <input type="number" name="months" value="{{ old('months', 36) }}" min="1" max="600" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('months') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white/90">
                    <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                    Actions
                </h3>
                <div class="space-y-3">
                    <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                        Create Depreciation
                    </button>
                    <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                        Create & Create New
                    </button>
                    <a href="{{ route('depreciations.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

