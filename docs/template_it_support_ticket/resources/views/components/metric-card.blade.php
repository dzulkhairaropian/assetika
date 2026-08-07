@props([
    'label' => '',
    'value' => '0',
    'icon' => '',
    'trend' => null,
    'trendDirection' => 'up',
    'trendColor' => 'success',
    'color' => 'gray',
])

@php
    $iconColors = [
        'gray' => 'text-gray-800 dark:text-white/90',
        'brand' => 'text-brand-500',
        'success' => 'text-success-500',
        'warning' => 'text-warning-500',
        'error' => 'text-error-500',
    ];
    $bgColors = [
        'gray' => 'bg-gray-100 dark:bg-gray-800',
        'brand' => 'bg-brand-50 dark:bg-brand-500/10',
        'success' => 'bg-success-50 dark:bg-success-500/10',
        'warning' => 'bg-warning-50 dark:bg-warning-500/10',
        'error' => 'bg-error-50 dark:bg-error-500/10',
    ];
    
    $featherIcons = [
        'ticket' => 'tag',
        'open' => 'folder',
        'warning' => 'alert-triangle',
        'clock' => 'clock',
        'users' => 'users',
        'user' => 'user',
    ];
    
    $featherIcon = $featherIcons[$icon] ?? 'help-circle';
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $bgColors[$color] }}">
        <i data-feather="{{ $featherIcon }}" class="{{ $iconColors[$color] }} size-6"></i>
    </div>

    <div class="mt-5 flex items-end justify-between">
        <div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</span>
            <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $value }}</h4>
        </div>

        @if($trend !== null)
            <span class="flex items-center gap-1 rounded-full py-0.5 pl-2 pr-2.5 text-sm font-medium
                {{ $trendColor === 'success' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : '' }}
                {{ $trendColor === 'error' ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' : '' }}
                {{ $trendColor === 'warning' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' : '' }}">
                @if($trendDirection === 'up')
                    <i data-feather="arrow-up" class="size-3"></i>
                @else
                    <i data-feather="arrow-down" class="size-3"></i>
                @endif
                {{ $trend }}
            </span>
        @endif
    </div>
</div>