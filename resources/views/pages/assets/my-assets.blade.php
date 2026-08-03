@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="My Assigned Assets" />

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Asset Tag / Name</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Product Details</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Serial Number</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Status</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Assigned At</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Location</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($assets as $asset)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400 font-semibold">{{ $asset->asset_tag }}</span>
                                        <span class="text-xs text-gray-500">{{ $asset->device_name ?? 'Unnamed Device' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ optional($asset->product->brand)->name ?? 'No Brand' }} - {{ optional($asset->product)->model ?? 'Unknown Model' }}
                                        </span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ optional(optional($asset->product)->category)->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400 font-mono">{{ $asset->serial_number ?? '-' }}</span>
                                        @if($asset->specification)
                                            <span class="text-xs text-gray-450 dark:text-gray-500 truncate max-w-xs" title="{{ $asset->specification }}">
                                                {{ $asset->specification }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusName = optional($asset->status)->name;
                                        $color = match($statusName) {
                                            'Ready to Deploy', 'Available', 'Ready' => 'success',
                                            'Deployed', 'In Use', 'Borrowed' => 'primary',
                                            'Broken' => 'error',
                                            'In Repair', 'Service' => 'warning',
                                            'Archived', 'Retired' => 'gray',
                                            default => 'gray'
                                        };
                                        $icon = match($statusName) {
                                             'Ready to Deploy', 'Available', 'Ready' => 'circle-check',
                                            'Deployed', 'In Use', 'Borrowed' => 'user',
                                            'Broken' => 'x-circle',
                                            'In Repair', 'Service' => 'wrench',
                                            'Archived', 'Retired' => 'archive',
                                            default => 'help-circle'
                                        };
                                        $iconHtml = new \Illuminate\Support\HtmlString("<i data-lucide='{$icon}' class='w-3 h-3'></i>");
                                    @endphp
                                    <x-ui.badge :color="$color" variant="light" size="sm" :startIcon="$iconHtml">{{ $statusName ?? 'Assigned' }}</x-ui.badge>
                                </td>
                                <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                    @if($asset->currentCheckout)
                                        {{ $asset->currentCheckout->checked_out_at ? $asset->currentCheckout->checked_out_at->format('M d, Y') : '-' }}
                                    @elseif($asset->release_date)
                                        {{ $asset->release_date->format('M d, Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                    {{ optional($asset->location)->name ?? optional(optional($asset->currentCheckout)->location)->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                                                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                                                <line x1="6" y1="6" x2="6.01" y2="6"></line>
                                                <line x1="6" y1="18" x2="6.01" y2="18"></line>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">You don't have any assigned assets</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Assets assigned directly to you will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
