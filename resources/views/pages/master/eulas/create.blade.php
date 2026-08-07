@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Create EULA" />
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <form action="{{ route('eulas.store') }}" method="POST">@csrf
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <div><label class="mb-1.5 block text-sm font-medium">Title <span class="text-error-500">*</span></label><input type="text" name="title" value="{{ old('title') }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900"></div>
                <div><label class="mb-1.5 block text-sm font-medium">Category</label><select name="category_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900"><option value="">Global (All)</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                <div class="lg:col-span-2"><label class="mb-1.5 block text-sm font-medium">EULA Text <span class="text-error-500">*</span></label><textarea name="body" rows="8" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900">{{ old('body') }}</textarea></div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('eulas.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Cancel</a>
                <button type="submit" name="create_another" value="1" class="inline-flex justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 hover:bg-brand-50 transition">Create & Create New</button>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">Create EULA</button>
            </div>
        </form>
    </div>
</div>
@endsection
