@props([
    'variant' => 'light',
    'size' => 'md',
    'color' => 'primary',
    'startIcon' => null,
    'endIcon' => null,
    'featherIcon' => null,
])

@php
    $baseStyles = 'inline-flex items-center w-fit px-2.5 py-0.5 justify-center gap-1 rounded-full font-medium capitalize';

    $sizeStyles = [
        'sm' => 'text-[10px] px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-0.5',
    ];

    $variants = [
        'light' => [
            'primary'   => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
            'secondary' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400',
            'success'   => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
            'danger'    => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
            'error'     => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
            'warning'   => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
            'info'      => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-400',
            'light'     => 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-white/80',
            'dark'      => 'bg-gray-500 text-white dark:bg-white/5 dark:text-white',
        ],
        'solid' => [
            'primary'   => 'bg-brand-500 text-white',
            'secondary' => 'bg-gray-500 text-white dark:bg-white/15 dark:text-white',
            'success'   => 'bg-green-500 text-white',
            'danger'    => 'bg-red-500 text-white',
            'error'     => 'bg-red-500 text-white',
            'warning'   => 'bg-orange-500 text-white',
            'info'      => 'bg-cyan-500 text-white',
            'light'     => 'bg-gray-400 dark:bg-white/5 text-white dark:text-white/80',
            'dark'      => 'bg-gray-800 text-white dark:bg-white/15 dark:text-white',
        ],
    ];

    $sizeClass = $sizeStyles[$size] ?? $sizeStyles['md'];

    $hexRegex = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
    $isHexColor = preg_match($hexRegex, $color);

    if ($isHexColor) {
        if (strlen($color) === 7) {
            $bgColor = $color . '20'; // 12% opacity hex string
        } else {
            $bgColor = $color;
        }

        if ($variant === 'light') {
            $colorStyles = "background-color: {$bgColor}; color: {$color};";
        } else {
            $r = hexdec(substr($color, 1, 2));
            $g = hexdec(substr($color, 3, 2));
            $b = hexdec(substr($color, 5, 2));
            $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
            $textColor = ( $brightness > 150 ) ? 'color: #1f2937;' : 'color: #ffffff;';
            $colorStyles = "background-color: {$color}; {$textColor}";
        }
        $isCustomStyle = true;
    } else {
        $colorStyles = $variants[$variant][$color] ?? $variants['light']['primary'];
        $isCustomStyle = false;
    }
@endphp

<span class="{{ $baseStyles }} {{ $sizeClass }} {{ $isCustomStyle ? '' : $colorStyles }} {{ $attributes->get('class') }}" style="{{ $isCustomStyle ? $colorStyles : '' }}" {{ $attributes->except('class') }}>
    @if($featherIcon)
        <span class="flex shrink-0">
            @switch($featherIcon)
                @case('user-check')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                    @break
                @case('alert-circle')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    @break
                @case('check-circle')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    @break
                @case('clock')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    @break
                @case('tool')
                @case('wrench')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    @break
                @case('rotate-ccw')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                    @break
                @case('shopping-bag')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    @break
                @case('flag')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                    @break
                @case('disc')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>
                    @break
                @case('thumbs-up')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                    @break
                @case('info')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    @break
                @case('alert-triangle')
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    @break
                @default
                    <i data-feather="{{ $featherIcon }}" class="w-3 h-3 shrink-0"></i>
            @endswitch
        </span>
    @endif

    @if($startIcon)
        <span class="flex shrink-0">
            {!! $startIcon !!}
        </span>
    @endif

    <span class="truncate max-w-[100px] sm:max-w-[150px]">
        {{ $slot }}
    </span>

    @if($endIcon)
        <span class="flex shrink-0">
            {!! $endIcon !!}
        </span>
    @endif
</span>
