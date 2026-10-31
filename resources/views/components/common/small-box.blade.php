@props([
    'title',
    'value',
    'icon' => 'hard-drive',
    'color' => 'brand',
    'trend' => null,
    'trendColor' => null,
    'trendLabel' => null,
    'description' => null,
    'url' => '#',
    'urlText' => 'View details'
])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 transition-colors duration-150 hover:border-brand-500/30 shadow-xs dark:border-gray-800 dark:bg-white/3 flex flex-col justify-between">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 truncate">{{ $title }}</h3>
            @isset($description)
                <p class="text-theme-sm mt-0.5 text-gray-500 dark:text-gray-400 text-xs truncate">{{ $description }}</p>
            @endisset
        </div>

        @if($url && $url !== '#')
            <a href="{{ $url }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-300 transition-colors shrink-0" title="View details">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
            </a>
        @else
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-50 text-gray-400 dark:bg-white/5 dark:text-gray-500 shrink-0">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
            </div>
        @endif
    </div>

    <div class="flex items-end justify-between gap-3 pt-2">
        <div>
            <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white/90 sm:text-3xl tabular-nums">{{ $value }}</h3>
            @if($trend !== null && $trendLabel)
                <p class="text-theme-xs mt-1 text-gray-500 dark:text-gray-400 text-xs flex items-center gap-1">
                    <span class="inline-flex items-center font-semibold text-{{ $trendColor ?? 'emerald' }}-600 dark:text-{{ $trendColor ?? 'emerald' }}-400">
                        {{ $trend }}
                    </span>
                    <span>{{ $trendLabel }}</span>
                </p>
            @endif
        </div>

        @if($url && $url !== '#')
            <a href="{{ $url }}" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400 flex items-center gap-1 shrink-0 pb-1">
                <span>View</span>
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        @endif
    </div>
</div>
