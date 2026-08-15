@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Import Preview" />
    @if($errors)
        @foreach($errors as $e) <x-ui.alert variant="error" message="{{ $e }}" /> @endforeach
    @endif
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex items-center justify-between mb-4"><h3 class="text-base font-medium">Preview ({{ min(count($preview), $maxPreview) }} rows)</h3><span class="text-sm text-gray-500">Columns: {{ implode(', ', $header) }}</span></div>
        <div class="max-w-full overflow-x-auto"><table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900"><tr>@foreach($header as $h)<th class="px-4 py-2 text-xs text-left text-gray-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                @forelse($preview as $row)
                <tr>@foreach($header as $h)<td class="px-4 py-2 text-sm">{{ $row[$h] ?? '' }}</td>@endforeach</tr>
                @empty
                <tr><td colspan="{{ count($header) }}" class="px-6 py-12 text-center text-sm text-gray-500">No rows to preview.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <form action="{{ route('import.process') }}" method="POST" class="mt-6 flex justify-end gap-3">
            @csrf
            <a href="{{ route('import.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Cancel</a>
            <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Confirm Import</button>
        </form>
    </div>
</div>
@endsection
