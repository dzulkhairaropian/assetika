
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
        'sm' => 'text-[10px] px-2 py-0',
        'md' => 'text-xs px-2.5 py-0.5',
    ];

    $variants = [
        'light' => [
            'primary'   => 'bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400',
            'secondary' => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
            'success'   => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'danger'    => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            'error'     => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            'warning'   => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400',
            'info'      => 'bg-blue-light-50 text-blue-light-500 dark:bg-blue-light-500/15 dark:text-blue-light-500',
            'light'     => 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-white/80',
            'dark'      => 'bg-gray-500 text-white dark:bg-white/5 dark:text-white',
        ],
        'solid' => [
            'primary'   => 'bg-brand-500 text-white',
            'secondary' => 'bg-gray-500 text-white dark:bg-white/15 dark:text-white',
            'success'   => 'bg-success-500 text-white',
            'danger'    => 'bg-error-500 text-white',
            'error'     => 'bg-error-500 text-white',
            'warning'   => 'bg-warning-500 text-white',
            'info'      => 'bg-blue-light-500 text-white',
            'light'     => 'bg-gray-400 dark:bg-white/5 text-white dark:text-white/80',
            'dark'      => 'bg-gray-800 text-white dark:bg-white/15 dark:text-white',
        ],
    ];

    $sizeClass = $sizeStyles[$size] ?? $sizeStyles['md'];

    $hexRegex = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
    $isHexColor = preg_match($hexRegex, $color);

    if ($isHexColor) {
        if ($variant === 'light') {
            $colorStyles = "background-color: color-mix(in srgb, {$color} 12%, transparent); color: {$color};";
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
            <i data-feather="{{ $featherIcon }}" class="w-3 h-3"></i>
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
