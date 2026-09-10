<div class="group relative rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 dark:hover:border-brand-500/40">
    <div class="flex items-center justify-between">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">{{ $title }}</p>
        @isset($icon)
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-50 text-gray-400 transition-colors group-hover:bg-brand-50 group-hover:text-brand-500 dark:bg-gray-800/60 dark:text-gray-500 dark:group-hover:bg-brand-500/10 dark:group-hover:text-brand-400 shrink-0">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
            </div>
        @endisset
    </div>

    <div class="mt-2 flex items-baseline justify-between gap-2">
        <h4 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $value }}</h4>
        @isset($trend)
            <span class="inline-flex items-center gap-0.5 text-xs font-semibold text-{{ $trendColor }}-600 dark:text-{{ $trendColor }}-400 shrink-0">
                @if($trend > 0)
                    <i data-lucide="trending-up" class="w-3 h-3"></i>+{{ $trend }}%
                @elseif($trend < 0)
                    <i data-lucide="trending-down" class="w-3 h-3"></i>{{ $trend }}%
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

