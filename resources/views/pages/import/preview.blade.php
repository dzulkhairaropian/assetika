@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Import Data Preview" :items="[['label' => 'Import Data', 'url' => route('import.index')]]" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Review and validate row data mapping before confirming insertion into system database.
    </p>

    @if($errors)
        @foreach($errors as $e)
            <x-ui.alert variant="error" message="{{ $e }}" />
        @endforeach
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <i data-feather="eye" class="w-5 h-5 text-brand-500"></i>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Data Preview</h3>
                <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ min(count($preview), $maxPreview) }} rows shown
                </span>
            </div>
            <span class="text-xs text-gray-400">Columns: {{ implode(', ', $header) }}</span>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        @foreach($header as $h)
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">{{ $h }}</p>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($preview as $row)
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            @foreach($header as $h)
                                <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    {{ $row[$h] ?? '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($header) }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="file-text" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No rows found to preview</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Please upload a valid CSV file with data rows.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6 flex items-center justify-end gap-3">
            <a href="{{ route('import.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                Cancel
            </a>
            <form action="{{ route('import.process') }}" method="POST" class="inline-flex">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 shadow-theme-xs">
                    <i data-feather="check" class="w-4 h-4"></i> Confirm & Execute Import
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
