@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Bulk Checkout Assets" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-2">Selected Assets ({{ count($assets) }})</h3>
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($assets as $a)
                <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 px-3 py-1 text-xs text-gray-700 dark:border-gray-700 dark:text-gray-400">{{ $a->asset_tag }} - {{ $a->name }}</span>
            @endforeach
        </div>
        <form action="{{ route('assets.bulk-checkout.process') }}" method="POST">
            @csrf
            <input type="hidden" name="ids" value="{{ $assets->pluck('id')->join(',') }}">
            @foreach($assets as $a) <input type="hidden" name="ids[]" value="{{ $a->id }}"> @endforeach
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Checkout to User <span class="text-error-500">*</span></label>
                    <select name="checked_out_to_user_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Notes</label>
                    <input type="text" name="notes" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900" placeholder="Bulk checkout notes">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('assets.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Checkout All</button>
            </div>
        </form>
    </div>
</div>
@endsection
