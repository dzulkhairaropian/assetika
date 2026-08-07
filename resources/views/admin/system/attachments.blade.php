<x-app-layout title="System Attachments">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">Attachments</h2>
            <form method="GET" class="flex w-full max-w-sm gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search attachments..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">Search</button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 pb-3 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pb-4 sm:pt-6">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead><tr class="border-y border-gray-100 dark:border-gray-800"><th class="py-3 pr-4 text-left text-theme-xs text-gray-500 dark:text-gray-400">File</th><th class="py-3 pr-4 text-left text-theme-xs text-gray-500 dark:text-gray-400">User</th><th class="py-3 pr-4 text-left text-theme-xs text-gray-500 dark:text-gray-400">Mime</th><th class="py-3 pr-4 text-left text-theme-xs text-gray-500 dark:text-gray-400">Size</th><th class="py-3 pr-4 text-left text-theme-xs text-gray-500 dark:text-gray-400">Date</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($attachments as $attachment)
                        <tr>
                            <td class="py-3.5 pr-4 text-theme-sm text-gray-800 dark:text-white/90">{{ $attachment->filename }}</td>
                            <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $attachment->user_name ?? '-' }}</td>
                            <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $attachment->mime_type }}</td>
                            <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ number_format($attachment->size / 1024, 2) }} KB</td>
                            <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $attachment->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-gray-500 dark:text-gray-400">No attachments.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $attachments->links() }}</div>
        </div>
    </div>
</x-app-layout>
