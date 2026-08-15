@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Import Data" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Bulk import assets, users, and master data from spreadsheets.
        </p>
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4">Upload CSV File</h3>
        <form action="{{ route('import.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-end">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Import Type</label>
                    <select name="type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900">
                        <option value="assets">IT Assets</option>
                        <option value="consumables">Consumables</option>
                        <option value="users">Users</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">CSV File <span class="text-error-500">*</span></label>
                    <input type="file" name="file" accept=".csv,.txt" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900">
                    @error('file') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>
                <div><button type="submit" class="h-11 w-full rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Preview</button></div>
            </div>
        </form>

        <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-400 mb-3">Download Template</h4>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('import.template', 'assets') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"><i data-feather="download" class="w-4 h-4"></i> Asset Template</a>
                <a href="{{ route('import.template', 'consumables') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"><i data-feather="download" class="w-4 h-4"></i> Consumable Template</a>
                <a href="{{ route('import.template', 'users') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"><i data-feather="download" class="w-4 h-4"></i> User Template</a>
            </div>
        </div>
    </div>
</div>
@endsection
