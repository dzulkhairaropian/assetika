<x-app-layout :title="$title">
    <div class="space-y-6">
        <!-- Breadcrumb Start -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                {{ $title }}s
            </h2>
            <a href="{{ route($viewName.'.create') }}" class="inline-flex items-center justify-center gap-2.5 rounded-lg bg-brand-500 px-6 py-3 text-center text-theme-sm font-medium text-white hover:bg-brand-600">
                + Create {{ $title }}
            </a>
        </div>
        <!-- Breadcrumb End -->

        <!-- Table Card Start -->
        <div class="rounded-2xl border border-gray-200 bg-white px-5 pb-3 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pb-4 sm:pt-6">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    {{ $title }} List
                </h3>
                <form method="GET" class="flex w-full max-w-sm gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or code..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-theme-sm font-medium text-white hover:bg-brand-600">
                        Search
                    </button>
                </form>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 dark:border-gray-800">
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">ID</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Name</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Code</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Color</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Sort</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Active</span></th>
                            <th class="py-3 text-right"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($items as $item)
                            <tr>
                                <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">#{{ $item->id }}</td>
                                <td class="py-3.5 pr-4"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $item->name }}</p></td>
                                <td class="py-3.5 pr-4"><code class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ $item->code }}</code></td>
                                <td class="py-3.5 pr-4">
                                    @if($item->color)
                                        <div class="flex items-center gap-2">
                                            <span class="inline-block h-5 w-5 rounded-full border border-gray-200 dark:border-gray-700" style="background-color: {{ $item->color }}"></span>
                                            <span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $item->color }}</span>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $item->sort_order }}</td>
                                <td class="py-3.5 pr-4">
                                    @if($item->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route($viewName.'.show', $item) }}" class="rounded p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300" title="View">
                                            <i data-feather="eye" class="size-5"></i>
                                        </a>
                                        <a href="{{ route($viewName.'.edit', $item) }}" class="rounded p-1.5 text-brand-500 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-500/10 dark:hover:text-brand-400" title="Edit">
                                            <i data-feather="edit-2" class="size-5"></i>
                                        </a>
                                        <form method="POST" action="{{ route($viewName.'.destroy', $item) }}" onsubmit="return confirm('Delete this {{ $title }}?')" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded p-1.5 text-error-500 hover:bg-error-50 hover:text-error-600 dark:hover:bg-error-500/10 dark:hover:text-error-400" title="Delete">
                                                <i data-feather="trash-2" class="size-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <i data-feather="inbox" class="size-8 text-gray-300 dark:text-gray-600"></i>
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No {{ $title }}s found.</p>
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
        <!-- Table Card End -->
    </div>

    @if(session('success'))
        <div class="fixed bottom-6 right-6 z-9999 flex items-center gap-3 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm font-medium text-success-700 shadow-lg dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
            <i data-feather="check-circle" class="size-5"></i>
            {{ session('success') }}
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.feather !== 'undefined') {
                window.feather.replace();
            }
        });
    </script>
</x-app-layout>
