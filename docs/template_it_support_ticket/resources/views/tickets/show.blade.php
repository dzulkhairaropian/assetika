<x-app-layout :title="'Ticket #' . $ticket->request_id">
    <div class="space-y-6">
        <!-- Breadcrumb Start -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                    Ticket #{{ $ticket->request_id }}
                </h2>
                <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    Detail View
                </span>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('tickets.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    ← Back
                </a>
                <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Edit Ticket
                </a>
            </div>
        </div>
        <!-- Breadcrumb End -->

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left Side: Problem Description & Resolution Notes (8 Cols) -->
            <div class="space-y-6 lg:col-span-8">
                <!-- Description Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Problem Description</h3>
                    <p class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $ticket->problem_description }}
                    </p>
                </div>

                <!-- Internal Notes -->
                @if($ticket->internal_notes)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Internal Notes</h3>
                        <p class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ $ticket->internal_notes }}
                        </p>
                    </div>
                @endif

                <!-- Resolution Card -->
                @if($ticket->resolution_notes || $ticket->resolution_time_hours)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Resolution</h3>
                        <div class="space-y-4">
                            @if($ticket->resolution_time_hours)
                                <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800 max-w-xs">
                                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Resolution Time</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $ticket->resolution_time_hours }} Hours</p>
                                </div>
                            @endif
                            @if($ticket->resolution_notes)
                                <p class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                    {{ $ticket->resolution_notes }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Side: Metadata, Requester details, and Asset details (4 Cols) -->
            <div class="space-y-6 lg:col-span-4">
                <!-- Status & Assignee Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Ticket Settings</h3>
                    <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Status</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->status?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>SLA Status</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->slaStatus?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Priority</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->priority?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Category</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->category?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Ticket Type</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->ticketType?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Source</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->source?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Assigned IT Staff</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Requester Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Requester Details</h3>
                    <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Name</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->requester_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Email</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->requester_email ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Company</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->requesterCompany?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Department</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->requesterDepartment?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Asset Card -->
                @if($ticket->asset)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Asset Details</h3>
                            <a href="{{ route('admin.sync.show', ['assets', $ticket->asset_id]) }}" class="text-xs text-brand-500 hover:underline">View Detail</a>
                        </div>
                        <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                                <span>Asset Tag</span>
                                <code class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ $ticket->asset->asset_tag }}</code>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                                <span>Device Name</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->asset->device_name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Serial Number</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->asset->serial_number ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
