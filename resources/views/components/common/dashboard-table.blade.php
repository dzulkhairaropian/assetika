@props(['title', 'description' => null, 'headers' => [], 'footerLink' => null, 'footerText' => 'View All', 'searchable' => true])

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
     x-data="{ search: '' }">
    <div class="px-4 pt-4 mb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
            @isset($description)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $description }}</p>
            @endisset
        </div>
        @if($searchable)
            <div class="relative shrink-0">
                <input type="text"
                       x-model="search"
                       placeholder="Filter..."
                       class="w-full sm:w-36 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 pl-7 text-xs text-gray-700 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:placeholder-gray-500 dark:focus:border-brand-500 transition">
                <i data-lucide="search" class="w-3 h-3 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        @endif
    </div>
    <div class="max-w-full overflow-x-auto max-h-72 overflow-y-auto">
        <table class="min-w-full text-xs">
            <thead>
                <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                    @foreach($headers as $header)
                        <th class="px-4 py-2 text-start font-medium text-gray-500 dark:text-gray-400">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @isset($footerLink)
        <div class="border-t border-gray-100 py-2.5 dark:border-gray-800 text-center px-4">
            <a href="{{ $footerLink }}" class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 inline-flex items-center gap-1">
                {{ $footerText ?? 'View All' }} <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>
    @endisset
</div>
