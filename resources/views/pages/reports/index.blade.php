@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Reports & Analytics" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        View asset metrics, depreciation trends, activity audit logs, and custom reports.
    </p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('reports.activity') }}"
           class="group rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                    <i data-feather="activity" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-brand-600 transition">Activity Audit</h3>
                    <span class="text-[11px] text-gray-400">Checkout & Check-in Logs</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Detailed audit trail of all asset checkouts, check-ins, and user movement history.
            </p>
        </a>

        <a href="{{ route('reports.assets') }}"
           class="group rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-green-300 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">
                    <i data-feather="hard-drive" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-green-600 transition">Asset Inventory</h3>
                    <span class="text-[11px] text-gray-400">Complete Hardware List</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Filtered breakdown of all registered IT hardware assets with multi-criteria export.
            </p>
        </a>

        <a href="{{ route('reports.depreciation') }}"
           class="group rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                    <i data-feather="trending-down" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-brand-600 transition">Depreciation</h3>
                    <span class="text-[11px] text-gray-400">Financial Book Value</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Track straight-line asset depreciation, original cost vs current book value.
            </p>
        </a>

        <a href="{{ route('reports.custom') }}"
           class="group rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-orange-300 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
                    <i data-feather="sliders" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-orange-600 transition">Custom Report</h3>
                    <span class="text-[11px] text-gray-400">Custom Builder</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Build tailored reports with custom column selections, filters, and export formats.
            </p>
        </a>
    </div>
</div>
@endsection
