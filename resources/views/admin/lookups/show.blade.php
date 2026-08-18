@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb
        pageTitle="{{ $title }} Details"
        :items="[
            ['label' => 'Ticketing', 'url' => route('ticket-dashboard')],
            ['label' => $title.'s', 'url' => route($viewName.'.index')],
        ]"
    />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        View and manage {{ strtolower($title) }} configuration and settings.
    </p>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $item->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $item->code }} {{ $item->description ? '• ' . Str::limit($item->description, 100) : '' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route($viewName.'.edit', $item) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i data-feather="edit-3" class="h-4 w-4"></i>
                    Edit
                </a>
                <form method="POST" action="{{ route($viewName.'.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this {{ $title }}?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition hover:bg-error-600">
                        <i data-feather="trash-2" class="h-4 w-4"></i>
                        Delete
                    </button>
                </form>
            </div>
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
        </div>
    </div>
@endsection
