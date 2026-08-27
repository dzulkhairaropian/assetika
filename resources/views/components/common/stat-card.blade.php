<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">{{ $title }}</p>
        @isset($icon)
            <i data-feather="{{ $icon }}" class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0"></i>
        @endisset
    </div>

    <div class="mt-2 flex items-baseline justify-between gap-2">
        <h4 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $value }}</h4>
        @isset($trend)
            <span class="inline-flex items-center gap-0.5 text-xs font-medium text-{{ $trendColor }}-600 dark:text-{{ $trendColor }}-400 shrink-0">
                @if($trend > 0)
                    <i data-feather="trending-up" class="w-3 h-3"></i>+{{ $trend }}%
                @elseif($trend < 0)
                    <i data-feather="trending-down" class="w-3 h-3"></i>{{ $trend }}%
                @else
                    {{ $trend }}%
                @endif
            </span>
        @endisset
    </div>

    @isset($description)
        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ $description }}</p>
    @endisset

    @isset($slot)
        <div class="mt-2">
            {{ $slot }}
        </div>
    @endisset
</div>

