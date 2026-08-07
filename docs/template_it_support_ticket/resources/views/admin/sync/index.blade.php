<x-app-layout :title="$meta['plural']">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $meta['plural'] }}</h2>
                <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">Synced from Assetika</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 pb-3 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pb-4 sm:pt-6">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $meta['label'] }} List</h3>
                <form method="GET" class="flex w-full max-w-sm gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search {{ $meta['label'] }}..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">Search</button>
                </form>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 dark:border-gray-800">
                            @if($table === 'assets')
                                <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Asset Tag / Name</span></th>
                                <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Product / Serial</span></th>
                                <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</span></th>
                                <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Holder</span></th>
                            @else
                                <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">ID</span></th>
                                @foreach($meta['cols'] as $col)
                                    <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $col)) }}</span></th>
                                @endforeach
                                @if($hasUserCount)
                                    <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Total Users</span></th>
                                @endif
                            @endif
                            <th class="py-3 text-right"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($items as $item)
                            <tr>
                                @if($table === 'assets')
                                    {{-- Asset Tag / Name --}}
                                    <td class="py-3.5 pr-4">
                                        <div class="flex flex-col">
                                            @if($item->asset_tag)
                                                <code class="text-xs font-medium text-brand-600 dark:text-brand-400">{{ $item->asset_tag }}</code>
                                            @endif
                                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $item->device_name ?? '-' }}</p>
                                        </div>
                                    </td>
                                    {{-- Product / Serial --}}
                                    <td class="py-3.5 pr-4">
                                        <div class="flex flex-col">
                                            <p class="text-theme-sm text-gray-800 dark:text-white/90">{{ $item->product_name ?? '-' }}</p>
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">S/N: {{ $item->serial_number ?? '-' }}</p>
                                        </div>
                                    </td>
                                    {{-- Status --}}
                                    <td class="py-3.5 pr-4">
                                        @if($item->status_name)
                                            <span class="rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">{{ $item->status_name }}</span>
                                        @else
                                            <span class="rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">Draft</span>
                                        @endif
                                    </td>
                                    {{-- Holder --}}
                                    <td class="py-3.5 pr-4">
                                        @if($item->holder_name)
                                            <p class="text-theme-sm text-gray-800 dark:text-white/90">{{ $item->holder_name }}</p>
                                        @else
                                            <p class="text-theme-xs text-gray-400 italic dark:text-gray-500">Not Assigned</p>
                                        @endif
                                    </td>
                                @else
                                    <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">#{{ $item->id }}</td>
                                    @foreach($meta['cols'] as $col)
                                        <td class="py-3.5 pr-4">
                                            @if($col === 'code')
                                                <code class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ $item->$col ?? '-' }}</code>
                                            @else
                                                <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $item->$col ?? '-' }}</p>
                                            @endif
                                        </td>
                                    @endforeach
                                    @if($hasUserCount)
                                        <td class="py-3.5 pr-4">
                                            <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                                {{ $item->user_count ?? 0 }} Users
                                            </span>
                                        </td>
                                    @endif
                                @endif
                                <td class="py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.sync.show', [$table, $item->id]) }}" class="rounded p-1.5 text-brand-500 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-500/10 dark:hover:text-brand-400" title="View Detail">
                                            <i data-feather="eye" class="size-5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $table === 'assets' ? 5 : count($meta['cols']) + ($hasUserCount ? 1 : 0) + 2 }}" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <i data-feather="inbox" class="size-8 text-gray-300 dark:text-gray-600"></i>
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No {{ $meta['plural'] }} synced yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="mt-4 border-t border-gray-100 py-3 dark:border-gray-800">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.feather !== 'undefined') {
                window.feather.replace();
            }
        });
    </script>
</x-app-layout>
