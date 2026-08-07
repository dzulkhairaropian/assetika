@extends('layouts.app')

@section('content')
    <div class="space-y-6 min-w-0">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $title }} Detail</h2>
            <a href="{{ route($viewName.'.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Back to List</a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $title }} Properties</h3>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($item->toArray() as $key => $value)
                        <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                            <p class="text-xs uppercase font-medium text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $key) }}</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white break-words">
                                @if(is_bool($value)) {{ $value ? 'Yes' : 'No' }} @elseif(is_null($value) || $value === '') - @else {{ $value }} @endif
                            </p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex justify-end gap-4 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <form method="POST" action="{{ route($viewName.'.destroy', $item) }}" onsubmit="return confirm('Delete this {{ $title }}?')" class="inline-block">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-error-300 bg-white px-5 py-2.5 text-sm font-medium text-error-500 shadow-sm hover:bg-error-50 dark:border-error-800 dark:bg-transparent dark:hover:bg-error-500/10">Delete</button>
                    </form>
                    <a href="{{ route($viewName.'.edit', $item) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Edit {{ $title }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
