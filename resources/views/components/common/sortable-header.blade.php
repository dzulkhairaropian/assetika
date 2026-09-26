@props([
    'column',
    'label',
    'route' => null,
    'align' => 'start',
])

@php
    $currentSort = request('sort_by');
    $currentDir = strtolower(request('sort_dir', 'asc'));
    $isActive = $currentSort === $column;
    $nextDir = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';

    $query = array_merge(request()->query(), [
        'sort_by' => $column,
        'sort_dir' => $nextDir,
    ]);

    $url = $route ? route($route, $query) : url()->current().'?'.http_build_query($query);
    $alignClass = $align === 'end' ? 'justify-end' : ($align === 'center' ? 'justify-center' : 'justify-start');
@endphp

<a href="{{ $url }}" class="inline-flex items-center gap-1.5 cursor-pointer group select-none {{ $alignClass }}" title="Sort by {{ $label }}">
    <span class="font-medium text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200 transition-colors">
        {{ $label }}
    </span>
    <span class="flex flex-col gap-[2px]">
        <svg class="w-2 h-1.5 {{ $isActive && $currentDir === 'asc' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-300 dark:text-gray-600 group-hover:text-gray-400' }}" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 0L7.4641 4.5H0.535898L4 0Z" fill="currentColor"/>
        </svg>
        <svg class="w-2 h-1.5 {{ $isActive && $currentDir === 'desc' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-300 dark:text-gray-600 group-hover:text-gray-400' }}" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 5L0.535898 0.5H7.4641L4 5Z" fill="currentColor"/>
        </svg>
    </span>
</a>
