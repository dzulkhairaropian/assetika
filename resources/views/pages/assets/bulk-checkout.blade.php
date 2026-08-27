@extends('layouts.app')
@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Bulk Checkout Assets" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />
    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Selected Assets ({{ count($assets) }})</h3>
        <div class="flex flex-wrap gap-1.5 mb-5">
            @foreach($assets as $a)
                <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 font-medium">{{ $a->asset_tag }} - {{ $a->name }}</span>
            @endforeach
        </div>
        <form action="{{ route('assets.bulk-checkout.process') }}" method="POST">
            @csrf
            <input type="hidden" name="ids" value="{{ $assets->pluck('id')->join(',') }}">
            @foreach($assets as $a) <input type="hidden" name="ids[]" value="{{ $a->id }}"> @endforeach
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Checkout to User <span class="text-red-500">*</span></label>
                    <select name="checked_out_to_user_id" required class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <input type="text" name="notes" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" placeholder="Bulk checkout notes">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2.5 mt-5 border-t border-gray-100 pt-3 dark:border-gray-800">
                <a href="{{ route('assets.index') }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">Checkout All</button>
            </div>
        </form>
    </div>
</div>
@endsection
