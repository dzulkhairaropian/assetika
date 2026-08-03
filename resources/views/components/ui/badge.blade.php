
@props([
    'variant' => 'light',
    'size' => 'md',
    'color' => 'primary',
    'startIcon' => null,
    'endIcon' => null,
])

@php
    $baseStyles = 'inline-flex items-center px-2.5 py-0.5 justify-center gap-1 rounded-full font-medium capitalize';

    $sizeStyles = [
        'sm' => 'text-[10px] px-2 py-0',
        'md' => 'text-xs px-2.5 py-0.5',
    ];

    $variants = [
        'light' => [
            'primary' => 'bg-blue-50 text-blue-500 dark:bg-blue-500/15 dark:text-blue-400',
            'success' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500',
            'error' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500',
            'warning' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-orange-400',
            'info' => 'bg-sky-50 text-sky-500 dark:bg-sky-500/15 dark:text-sky-500',
            'light' => 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-white/80',
            'dark' => 'bg-gray-500 text-white dark:bg-white/5 dark:text-white',
        ],
        'solid' => [
            'primary' => 'bg-blue-500 text-white dark:text-white',
            'success' => 'bg-green-500 text-white dark:text-white',
            'error' => 'bg-red-500 text-white dark:text-white',
            'warning' => 'bg-yellow-500 text-white dark:text-white',
            'info' => 'bg-sky-500 text-white dark:text-white',
            'light' => 'bg-gray-400 dark:bg-white/5 text-white dark:text-white/80',
            'dark' => 'bg-gray-700 text-white dark:text-white',
        ],
    ];

    $sizeClass = $sizeStyles[$size] ?? $sizeStyles['md'];

    $hexRegex = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
    $isHexColor = preg_match($hexRegex, $color);

    if ($isHexColor) {
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5, 2));
        // Calculate brightness to determine text color
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        $textColor = ( $brightness > 150 ) ? 'color: #1f2937;' : 'color: #ffffff;'; // text-gray-900 or text-white
        $colorStyles = "background-color: {$color}; {$textColor}";
        $isCustomStyle = true;
    } else {
        $colorStyles = $variants[$variant][$color] ?? $variants['light']['primary'];
        $isCustomStyle = false;
    }
@endphp

<span class="{{ $baseStyles }} {{ $sizeClass }} {{ $isCustomStyle ? '' : $colorStyles }} {{ $attributes->get('class') }}" style="{{ $isCustomStyle ? $colorStyles : '' }}" {{ $attributes->except('class') }}>
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
