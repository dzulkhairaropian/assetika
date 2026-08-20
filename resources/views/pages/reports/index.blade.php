@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Reports & Analytics" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        View asset metrics, depreciation trends, activity audit logs, and custom reports.
    </p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-6">
        <a href="{{ route('reports.activity') }}"
           class="group rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-theme-xs transition-all hover:border-brand-300 hover:shadow-theme-md dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/50">
            <div class="flex items-center gap-3.5 mb-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 group-hover:scale-105 transition-transform">
                    <i data-feather="activity" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 group-hover:text-brand-500 transition-colors">Activity Audit</h3>
                    <span class="text-xs text-gray-400">Checkout & Check-in Logs</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Detailed audit trail of all asset checkouts, check-ins, and user movement history.
            </p>
        </a>

        <a href="{{ route('reports.assets') }}"
           class="group rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-theme-xs transition-all hover:border-success-300 hover:shadow-theme-md dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-success-500/50">
            <div class="flex items-center gap-3.5 mb-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 group-hover:scale-105 transition-transform">
                    <i data-feather="hard-drive" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 group-hover:text-success-600 transition-colors">Asset Inventory</h3>
                    <span class="text-xs text-gray-400">Complete Hardware List</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Filtered breakdown of all registered IT hardware assets with multi-criteria export.
            </p>
        </a>

        <a href="{{ route('reports.depreciation') }}"
           class="group rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-theme-xs transition-all hover:border-brand-300 hover:shadow-theme-md dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/50">
            <div class="flex items-center gap-3.5 mb-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 group-hover:scale-105 transition-transform">
                    <i data-feather="trending-down" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 group-hover:text-brand-500 transition-colors">Depreciation</h3>
                    <span class="text-xs text-gray-400">Financial Book Value</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Track straight-line asset depreciation, original cost vs current book value.
            </p>
        </a>

        <a href="{{ route('reports.custom') }}"
           class="group rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-theme-xs transition-all hover:border-warning-300 hover:shadow-theme-md dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-warning-500/50">
            <div class="flex items-center gap-3.5 mb-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400 group-hover:scale-105 transition-transform">
                    <i data-feather="sliders" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 group-hover:text-warning-600 transition-colors">Custom Report</h3>
                    <span class="text-xs text-gray-400">Custom Builder</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Build tailored reports with custom column selections, filters, and export formats.
            </p>
        </a>
    </div>
</div>
@endsection
