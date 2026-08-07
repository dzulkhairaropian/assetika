@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Create Maintenance Type" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <form action="{{ route('maintenance-types.store') }}" method="POST">@csrf
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div><label class="mb-1.5 block text-sm font-medium">Name <span class="text-error-500">*</span></label><input type="text" name="name" value="{{ old('name') }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900"></div>
                <div><label class="mb-1.5 block text-sm font-medium">Description</label><input type="text" name="description" value="{{ old('description') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900"></div>
                <div class="lg:col-span-2"><label class="flex items-center gap-2 text-sm font-medium"><input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }} class="rounded"> Default type (recommended)</label></div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('maintenance-types.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Cancel</a>
                <button type="submit" name="create_another" value="1" class="inline-flex justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 hover:bg-brand-50 transition">Create & Create New</button>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection
