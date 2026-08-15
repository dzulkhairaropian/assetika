@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Import Data" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Bulk import assets, users, and master data from spreadsheets.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        {{-- Main Form Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <i data-feather="upload-cloud" class="w-5 h-5 text-brand-500"></i>
                    Upload Spreadsheet File
                </h3>

                <form action="{{ route('import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Import Type <span class="text-error-500">*</span>
                                </label>
                                <select name="type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="assets">IT Assets</option>
                                    <option value="consumables">Consumables</option>
                                    <option value="users">Users</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    CSV File <span class="text-error-500">*</span>
                                </label>
                                <input type="file" name="file" accept=".csv,.txt" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @error('file') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                                <i data-feather="eye" class="w-4 h-4"></i> Preview & Validate Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Download Templates Card --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                    <i data-feather="download" class="w-5 h-5 text-brand-500"></i>
                    Sample Templates
                </h3>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Download sample CSV templates pre-formatted with column headers before uploading.
                </p>
                <div class="space-y-2.5">
                    <a href="{{ route('import.template', 'assets') }}"
                       class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <span class="flex items-center gap-2">
                            <i data-feather="hard-drive" class="w-4 h-4 text-brand-500"></i> IT Assets Template
                        </span>
                        <i data-feather="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'consumables') }}"
                       class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <span class="flex items-center gap-2">
                            <i data-feather="package" class="w-4 h-4 text-amber-500"></i> Consumables Template
                        </span>
                        <i data-feather="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'users') }}"
                       class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <span class="flex items-center gap-2">
                            <i data-feather="users" class="w-4 h-4 text-purple-500"></i> Users Template
                        </span>
                        <i data-feather="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
