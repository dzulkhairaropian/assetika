<x-app-layout :title="$meta['label'] . ' Detail'">
    @php
        $prettyLabel = fn (string $key) => ucwords(str_replace('_', ' ', $key));
        $valueText = fn ($value) => is_bool($value) ? ($value ? 'Yes' : 'No') : (is_null($value) || $value === '' ? '-' : $value);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $meta['label'] }} Detail</h2>
                <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">Synced from Assetika</span>
            </div>
            <a href="{{ route('admin.sync.index', $table) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">← Back to {{ $meta['plural'] }}</a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $item->name ?? $item->asset_tag ?? '#' . $item->id }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $meta['label'] }} synced from Assetika</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">ID #{{ $item->id }}</span>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach((array) $item as $key => $value)
                        @if(in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                            @continue
                        @endif
                        <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                            <p class="text-xs uppercase font-medium text-gray-500 dark:text-gray-400">{{ $prettyLabel($key) }}</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white break-words">{{ $valueText($value) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <h4 class="mb-3 text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">Timestamps</h4>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        @foreach(['created_at', 'updated_at', 'deleted_at'] as $key)
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $prettyLabel($key) }}</p>
                                <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $valueText($item->$key ?? null) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
