@props(['title', 'description' => null, 'headers' => [], 'footerLink' => null, 'footerText' => 'View All', 'searchable' => true])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
     x-data="{ search: '' }">
    <div class="px-5 pt-5 sm:px-6 mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>
            @isset($description)
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $description }}</p>
            @endisset
        </div>
        @if($searchable)
            <div class="relative shrink-0">
                <input type="text"
                       x-model="search"
                       placeholder="Filter..."
                       class="w-full sm:w-44 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-1.5 pl-8 text-xs text-gray-700 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-300 dark:placeholder-gray-500 dark:focus:border-brand-500 transition">
                <i data-feather="search" class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        @endif
    </div>
    <div class="max-w-full overflow-x-auto max-h-80 overflow-y-auto">
        <table class="min-w-full">
            <thead>
                <tr class="border-t border-gray-100 dark:border-gray-800">
                    @foreach($headers as $header)
                        <th class="px-5 py-3 sm:px-6 text-start">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">{{ $header }}</p>
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
        <div class="border-t border-gray-100 py-3 dark:border-gray-800 text-center px-5 sm:px-6">
            <a href="{{ $footerLink }}" class="text-xs font-semibold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-1">
                {{ $footerText ?? 'View All' }} <i data-feather="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    @endisset
</div>