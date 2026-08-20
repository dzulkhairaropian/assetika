@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Custom Report Builder" :items="[['label' => 'Reports', 'url' => route('reports.index')]]" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Build tailored reports with custom column selections, filters, and export formats.
    </p>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col items-center justify-center space-y-3">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                <i data-feather="sliders" class="w-8 h-8"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Custom Report Builder</h3>
            <p class="max-w-md text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                The advanced custom report generator is currently being prepared. In the meantime, please use the <a href="{{ route('reports.assets') }}" class="font-medium text-brand-500 hover:underline">Asset Inventory Report</a> or <a href="{{ route('reports.activity') }}" class="font-medium text-brand-500 hover:underline">Activity Report</a>.
            </p>
        </div>
    </div>
</div>
@endsection
