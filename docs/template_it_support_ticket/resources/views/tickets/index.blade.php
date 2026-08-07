<x-app-layout title="Tickets">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">Tickets</h2>
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center gap-2.5 rounded-lg bg-brand-500 px-6 py-3 text-center text-theme-sm font-medium text-white hover:bg-brand-600">+ Create Ticket</a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 pb-3 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pb-4 sm:pt-6">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ticket List</h3>
                <form method="GET" class="flex w-full max-w-md gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search tickets..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    <select name="status_id" class="h-11 w-36 appearance-none rounded-lg border border-gray-300 bg-transparent px-4 pr-10 text-theme-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <select name="priority_id" class="h-11 w-36 appearance-none rounded-lg border border-gray-300 bg-transparent px-4 pr-10 text-theme-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Priority</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">Search</button>
                </form>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 dark:border-gray-800">
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">ID</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requester</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Description</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</span></th>
                            <th class="py-3 pr-4 text-left"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Priority</span></th>
                            <th class="py-3 text-right"><span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">#{{ $ticket->request_id }}</td>
                                <td class="py-3.5 pr-4"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $ticket->requester_name ?? $ticket->requesterUser?->name }}</p></td>
                                <td class="py-3.5 pr-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($ticket->problem_description, 50) }}</td>
                                <td class="py-3.5 pr-4"><span class="rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">{{ $ticket->status?->name }}</span></td>
                                <td class="py-3.5 pr-4"><span class="rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">{{ $ticket->priority?->name }}</span></td>
                                <td class="py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="rounded px-2.5 py-1.5 text-theme-xs font-medium text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-500/10">View</a>
                                        <a href="{{ route('tickets.edit', $ticket) }}" class="rounded px-2.5 py-1.5 text-theme-xs font-medium text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <i data-feather="inbox" class="size-8 text-gray-300 dark:text-gray-600"></i>
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No tickets found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="mt-4 border-t border-gray-100 py-3 dark:border-gray-800">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
