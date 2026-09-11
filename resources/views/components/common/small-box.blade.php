@props([
    'title',
    'value',
    'icon' => 'hard-drive',
    'color' => 'brand', // brand, success, warning, danger, info
    'trend' => null,
    'trendColor' => null,
    'description' => null,
    'url' => '#',
    'urlText' => 'More info'
])

@php
    $colorClasses = [
        'brand' => [
            'bg' => 'bg-brand-500/10 border-brand-200/80 dark:bg-brand-500/10 dark:border-brand-900/40',
            'text' => 'text-brand-700 dark:text-brand-300',
            'icon' => 'text-brand-500/30 group-hover:text-brand-500/50 dark:text-brand-400/20 dark:group-hover:text-brand-400/40',
            'footer' => 'bg-brand-500/10 text-brand-700 hover:bg-brand-500/20 dark:bg-brand-500/20 dark:text-brand-300 dark:hover:bg-brand-500/30',
        ],
        'success' => [
            'bg' => 'bg-emerald-500/10 border-emerald-200/80 dark:bg-emerald-500/10 dark:border-emerald-900/40',
            'text' => 'text-emerald-700 dark:text-emerald-300',
            'icon' => 'text-emerald-500/30 group-hover:text-emerald-500/50 dark:text-emerald-400/20 dark:group-hover:text-emerald-400/40',
            'footer' => 'bg-emerald-500/10 text-emerald-700 hover:bg-emerald-500/20 dark:bg-emerald-500/20 dark:text-emerald-300 dark:hover:bg-emerald-500/30',
        ],
        'warning' => [
            'bg' => 'bg-amber-500/10 border-amber-200/80 dark:bg-amber-500/10 dark:border-amber-900/40',
            'text' => 'text-amber-700 dark:text-amber-300',
            'icon' => 'text-amber-500/30 group-hover:text-amber-500/50 dark:text-amber-400/20 dark:group-hover:text-amber-400/40',
            'footer' => 'bg-amber-500/10 text-amber-700 hover:bg-amber-500/20 dark:bg-amber-500/20 dark:text-amber-300 dark:hover:bg-amber-500/30',
        ],
        'danger' => [
            'bg' => 'bg-red-500/10 border-red-200/80 dark:bg-red-500/10 dark:border-red-900/40',
            'text' => 'text-red-700 dark:text-red-300',
            'icon' => 'text-red-500/30 group-hover:text-red-500/50 dark:text-red-400/20 dark:group-hover:text-red-400/40',
            'footer' => 'bg-red-500/10 text-red-700 hover:bg-red-500/20 dark:bg-red-500/20 dark:text-red-300 dark:hover:bg-red-500/30',
        ],
        'info' => [
            'bg' => 'bg-cyan-500/10 border-cyan-200/80 dark:bg-cyan-500/10 dark:border-cyan-900/40',
            'text' => 'text-cyan-700 dark:text-cyan-300',
            'icon' => 'text-cyan-500/30 group-hover:text-cyan-500/50 dark:text-cyan-400/20 dark:group-hover:text-cyan-400/40',
            'footer' => 'bg-cyan-500/10 text-cyan-700 hover:bg-cyan-500/20 dark:bg-cyan-500/20 dark:text-cyan-300 dark:hover:bg-cyan-500/30',
        ],
    ];

    $scheme = $colorClasses[$color] ?? $colorClasses['brand'];
@endphp

<div class="group relative overflow-hidden rounded-xl border {{ $scheme['bg'] }} bg-white shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900">
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

            <h3 class="text-3xl font-extrabold tracking-tight {{ $scheme['text'] }}">{{ $value }}</h3>

            @isset($description)
                <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 truncate">{{ $description }}</p>
            @endisset
        </div>

        <!-- AdminLTE Watermark Icon -->
        <div class="absolute -bottom-2 -right-2 z-0 pointer-events-none transition-all duration-300 group-hover:scale-110">
            <i data-lucide="{{ $icon }}" class="w-20 h-20 {{ $scheme['icon'] }} transition-colors"></i>
        </div>
    </div>

    <!-- Small Box Footer Link -->
    @if($url && $url !== '#')
        <a href="{{ $url }}" class="flex items-center justify-center gap-1.5 py-1.5 px-3 text-xs font-semibold {{ $scheme['footer'] }} transition-colors">
            <span>{{ $urlText }}</span>
            <i data-lucide="arrow-right-circle" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"></i>
        </a>
    @endif
</div>
