@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb
        pageTitle="{{ $title }} Details"
        :items="[
            ['label' => 'Ticketing', 'url' => route('ticket-dashboard')],
            ['label' => $title.'s', 'url' => route($viewName.'.index')],
        ]"
    />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        View and manage {{ strtolower($title) }} configuration and settings.
    </p>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $item->name }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $item->code }} {{ $item->description ? '• ' . Str::limit($item->description, 100) : '' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route($viewName.'.edit', $item) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                    <i data-feather="edit-3" class="h-3.5 w-3.5"></i>
                    Edit
                </a>
                <form method="POST" action="{{ route($viewName.'.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this {{ $title }}?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-600 transition">
                        <i data-feather="trash-2" class="h-3.5 w-3.5"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="p-4 sm:p-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 text-xs">
                @foreach($item->toArray() as $key => $value)
                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                        <p class="text-[10px] uppercase font-medium text-gray-400">{{ str_replace('_', ' ', $key) }}</p>
                        <p class="mt-0.5 text-xs font-semibold text-gray-900 dark:text-white break-words">
                            @if(is_bool($value)) {{ $value ? 'Yes' : 'No' }} @elseif(is_null($value) || $value === '') - @else {{ $value }} @endif
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
