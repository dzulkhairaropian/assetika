@props([
    'title',
    'value',
    'icon' => 'hard-drive',
    'color' => 'brand', // keep for badge accent if needed
    'trend' => null,
    'trendColor' => null,
    'description' => null,
    'url' => '#',
    'urlText' => 'More info'
])

<div class="group relative overflow-hidden rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900">
    <div class="p-4 sm:p-5">
        <div class="relative z-10 space-y-1">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">{{ $title }}</p>
                @isset($trend)
                    <span class="inline-flex items-center gap-0.5 text-xs font-bold text-{{ $trendColor ?? 'emerald' }}-600 dark:text-{{ $trendColor ?? 'emerald' }}-400 shrink-0">
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

            <h3 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ $value }}</h3>

            @isset($description)
                <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 truncate">{{ $description }}</p>
            @endisset
        </div>

        <!-- TailAdmin Clean Watermark Icon -->
        <div class="absolute -bottom-2 -right-2 z-0 pointer-events-none transition-all duration-300 group-hover:scale-110">
            <i data-lucide="{{ $icon }}" class="w-20 h-20 text-gray-200/50 group-hover:text-brand-500/15 dark:text-gray-800/40 dark:group-hover:text-brand-400/20 transition-colors"></i>
        </div>
    </div>

    <!-- TailAdmin Clean Footer Link -->
    @if($url && $url !== '#')
        <a href="{{ $url }}" class="flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold border-t border-gray-100 bg-gray-50/50 text-gray-600 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800/80 dark:bg-gray-950/60 dark:text-gray-400 dark:hover:bg-brand-500/10 dark:hover:text-brand-400 transition-colors">
            <span>{{ $urlText }}</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1"></i>
        </a>
    @endif
</div>
