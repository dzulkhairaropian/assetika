@props([
    'variant' => 'light',
    'size' => 'md',
    'color' => 'primary',
    'startIcon' => null,
    'endIcon' => null,
    'featherIcon' => null,
    'lucideIcon' => null,
])

@php
    $iconName = $lucideIcon ?? $featherIcon;
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
    @if($iconName)
        @php
            $lucideName = match($iconName) {
                'circle-check', 'check' => 'circle-check',
                'alert-triangle' => 'triangle-alert',
                'alert-circle' => 'circle-alert',
                'x-circle' => 'circle-x',
                'tool' => 'wrench',
                'upload-cloud' => 'cloud-upload',
                default => $iconName
            };
        @endphp
        <span class="flex shrink-0">
            <i data-lucide="{{ $lucideName }}" class="w-3 h-3 shrink-0"></i>
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
