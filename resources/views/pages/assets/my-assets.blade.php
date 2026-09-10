@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="My Assigned Assets" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Asset Tag / Name</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Product Details</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Serial Number</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Assigned At</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Location</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="block font-semibold text-gray-900 dark:text-white">{{ $asset->asset_tag }}</span>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $asset->device_name ?? 'Unnamed Device' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ optional($asset->product->brand)->name ?? 'No Brand' }} - {{ optional($asset->product)->model ?? 'Unknown Model' }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ optional(optional($asset->product)->category)->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 font-mono">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $asset->serial_number ?? '-' }}</span>
                                        @if($asset->specification)
                                            <span class="text-[11px] text-gray-400 font-sans truncate max-w-xs" title="{{ $asset->specification }}">
                                                {{ $asset->specification }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $statusName = optional($asset->status)->name;
                                        $color = optional($asset->status)->color ?? 'gray';
                                    @endphp
                                    <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="optional($asset->status)->icon">{{ $statusName ?? 'Assigned' }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    @if($asset->currentCheckout)
                                        {{ $asset->currentCheckout->checked_out_at ? $asset->currentCheckout->checked_out_at->format('M d, Y') : '-' }}
                                    @elseif($asset->release_date)
                                        {{ $asset->release_date->format('M d, Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ optional($asset->location)->name ?? optional(optional($asset->currentCheckout)->location)->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="hard-drive" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">You don't have any assigned assets</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Assets assigned directly to you will appear here.</p>
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
