@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Custom Report Builder" :items="[['label' => 'Reports', 'url' => route('reports.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Build tailored reports with custom column selections, filters, and export formats.
    </p>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-8 sm:p-10 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col items-center justify-center space-y-2">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                <i data-feather="sliders" class="w-6 h-6"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Custom Report Builder</h3>
            <p class="max-w-md text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                The advanced custom report generator is currently being prepared. In the meantime, please use the <a href="{{ route('reports.assets') }}" class="font-semibold text-brand-600 hover:underline">Asset Inventory Report</a> or <a href="{{ route('reports.activity') }}" class="font-semibold text-brand-600 hover:underline">Activity Report</a>.
            </p>
        </div>
    </div>
</div>
@endsection
