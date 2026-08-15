@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Reports" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            View asset metrics, depreciation, and activity audit logs.
        </p>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('reports.activity') }}" class="rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3"><i data-feather="activity" class="w-6 h-6 text-brand-500"></i><span class="font-semibold text-gray-800 dark:text-white/90">Activity Report</span></div>
            <p class="mt-2 text-sm text-gray-500">Checkout/checkin history with filters by date and user.</p>
        </a>
        <a href="{{ route('reports.assets') }}" class="rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3"><i data-feather="hard-drive" class="w-6 h-6 text-green-500"></i><span class="font-semibold text-gray-800 dark:text-white/90">Asset Report</span></div>
            <p class="mt-2 text-sm text-gray-500">All assets with multi-filter and export.</p>
        </a>
        <a href="{{ route('reports.depreciation') }}" class="rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3"><i data-feather="trending-down" class="w-6 h-6 text-purple-500"></i><span class="font-semibold text-gray-800 dark:text-white/90">Depreciation Report</span></div>
            <p class="mt-2 text-sm text-gray-500">Book value vs purchase cost per asset.</p>
        </a>
        <a href="{{ route('reports.custom') }}" class="rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3"><i data-feather="file-text" class="w-6 h-6 text-orange-500"></i><span class="font-semibold text-gray-800 dark:text-white/90">Custom Report</span></div>
            <p class="mt-2 text-sm text-gray-500">Build custom report with column and filter selection.</p>
        </a>
    </div>
</div>
@endsection
