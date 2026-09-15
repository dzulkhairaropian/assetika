@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Create Maintenance Type" :items="[['label' => 'Maintenance Types', 'url' => route('maintenance-types.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Add a new maintenance type to categorize asset servicing.
        </p>
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <form action="{{ route('maintenance-types.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        @csrf

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="wrench" class="w-5 h-5 text-brand-500"></i> Maintenance Type Information
                    </h3>
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Name <span class="text-error-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:border-brand-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Description</label>
                            <input type="text" name="description" value="{{ old('description') }}" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:border-brand-800">
                        </div>
                        <div class="lg:col-span-2">
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-400">
                                <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }} class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                Default type (recommended)
                            </label>
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
                        Create Type
                    </button>
                    <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                        Create & Create New
                    </button>
                    <a href="{{ route('maintenance-types.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

