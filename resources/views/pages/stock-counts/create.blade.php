@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Create Stock Count" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <form action="{{ route('stock-counts.store') }}" method="POST">@csrf
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div><label class="mb-1.5 block text-sm font-medium">Session Name <span class="text-error-500">*</span></label><input type="text" name="name" value="{{ old('name', 'Stock Count ' . date('F Y')) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900"></div>
                <div><label class="mb-1.5 block text-sm font-medium">Target Location</label><select name="location_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900"><option value="">All Locations</option>@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></div>
                <div><label class="mb-1.5 block text-sm font-medium">Start Date <span class="text-error-500">*</span></label><input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900"></div>
                <div class="lg:col-span-2"><label class="mb-1.5 block text-sm font-medium">Notes</label><textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900">{{ old('notes') }}</textarea></div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('stock-counts.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Start Session</button>
            </div>
        </form>
    </div>
</div>
@endsection
