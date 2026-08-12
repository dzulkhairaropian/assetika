<div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 transition hover:shadow-theme-sm relative overflow-hidden group">
    <!-- Optional glow effect -->
    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-{{ $trendColor ?? 'brand' }}-500/5 blur-2xl group-hover:bg-{{ $trendColor ?? 'brand' }}-500/10 transition"></div>

    <div class="flex items-start justify-between relative z-10">
        <div class="w-full">
            <div class="flex items-center justify-between w-full mb-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $title }}</p>
                @isset($trend)
                    <span class="inline-flex items-center gap-1 rounded-md bg-{{ $trendColor }}-50 px-2 py-0.5 text-xs font-medium text-{{ $trendColor }}-700 dark:bg-{{ $trendColor }}-500/10 dark:text-{{ $trendColor }}-400">
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
            <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ $value }}</h4>
            @isset($description)
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800 pt-3">{{ $description }}</p>
            @endisset
        </div>
    </div>
    @isset($slot)
        <div class="relative z-10 mt-3">
            {{ $slot }}
        </div>
    @endisset
</div>