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
    $baseStyles = 'inline-flex items-center w-fit justify-center gap-1 rounded-full font-medium capitalize';

    $sizeStyles = [
        'xs' => 'text-[9px] px-1.5 py-0.25',
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
        @if(in_array($iconName, ['circle-check', 'check']))
            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
            </svg>
        @elseif(in_array($iconName, ['user-check']))
            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>
            </svg>
        @elseif(in_array($iconName, ['wrench', 'tool']))
            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        @elseif(in_array($iconName, ['circle-x', 'x-circle']))
            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>
            </svg>
        @elseif(in_array($iconName, ['archive']))
            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>
            </svg>
        @elseif(in_array($iconName, ['alert-triangle', 'triangle-alert']))
            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>
            </svg>
        @elseif(in_array($iconName, ['alert-circle', 'circle-alert']))
            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
        @else
            <span class="flex shrink-0">
                <i data-lucide="{{ $iconName }}" class="w-3 h-3 shrink-0"></i>
            </span>
        @endif
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
