@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Ticket Detail" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-bold font-mono text-gray-800 dark:text-white/90">
                [{{ $ticket->request_id }}]
            </h2>
            <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                {{ $ticket->status?->name ?? 'Open' }}
            </span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                Back
            </a>
            <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white hover:bg-brand-600">
                Edit Issue
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl border border-success-200 bg-success-50 text-success-700 dark:border-success-900/30 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Main Content Area: Problem, Comments, Activity Timeline -->
        <div class="space-y-6 lg:col-span-8">
            <!-- Issue Description -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-4">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Problem Description</h3>
                <div class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 leading-relaxed bg-gray-50 p-4 rounded-xl dark:bg-gray-900/50">
                    {{ $ticket->problem_description }}
                </div>

                @if($ticket->attachments->count() > 0)
                    <div class="pt-2">
                        <h4 class="text-xs font-semibold uppercase text-gray-500 mb-2">Ticket Attachments</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($ticket->attachments as $attachment)
                                <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    <i data-feather="file" class="w-3.5 h-3.5 text-brand-500"></i>
                                    <span>{{ $attachment->filename }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Resolution & Internal Notes -->
            @if($ticket->resolution_notes || $ticket->internal_notes)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-4">
                    @if($ticket->resolution_notes)
                        <div>
                            <h4 class="text-xs font-semibold uppercase text-success-600 dark:text-success-400 mb-1">Resolution Summary</h4>
                            <div class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 bg-success-50/50 p-4 rounded-xl dark:bg-success-900/10">
                                {{ $ticket->resolution_notes }}
                            </div>
                        </div>
                    @endif
                    @if($ticket->internal_notes)
                        <div>
                            <h4 class="text-xs font-semibold uppercase text-warning-600 dark:text-warning-400 mb-1">Internal Notes</h4>
                            <div class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 bg-warning-50/50 p-4 rounded-xl dark:bg-warning-900/10">
                                {{ $ticket->internal_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Comments & Discussion -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Activity & Discussion</h3>

                <!-- Add Comment Form -->
                <form action="{{ route('tickets.comment', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <textarea name="comment" rows="3" required placeholder="Add a response or internal update..." class="w-full rounded-xl border border-gray-300 bg-transparent p-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                Internal note (visible to staff only)
                            </label>
                            <input type="file" name="attachment" class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300"/>
                        </div>
                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white hover:bg-brand-600">
                            Add Comment
                        </button>
                    </div>
                </form>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Comment Feed -->
                <div class="space-y-4">
                    @forelse($ticket->comments as $comment)
                        <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800 {{ $comment->is_internal ? 'bg-warning-50/30 dark:bg-warning-900/10' : 'bg-gray-50/50 dark:bg-gray-900/30' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-xs text-gray-800 dark:text-white">
                                    {{ $comment->user?->name ?? 'System' }}
                                    @if($comment->is_internal)
                                        <span class="ml-2 rounded bg-warning-100 px-1.5 py-0.5 text-[10px] font-semibold text-warning-700">Internal</span>
                                    @endif
                                </span>
                                <span class="text-[11px] text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->body ?? $comment->comment }}</p>

                            @if($comment->attachments->count() > 0)
                                <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-800/60 flex flex-wrap gap-2">
                                    @foreach($comment->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-brand-600 hover:underline">
                                            <i data-feather="paperclip" class="w-3 h-3"></i>
                                            {{ $attachment->filename }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-xs text-gray-400 py-4">No comments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Details / ITSM Metadata -->
        <div class="space-y-6 lg:col-span-4">
            <!-- Ticket Info -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-4 text-xs">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Ticket Meta</h3>
                <div class="space-y-2.5">
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Priority</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->priority?->name ?? 'Normal' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Category</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $ticket->ticketCategory?->name ?? '-' }}
                            @if($ticket->ticketSubcategory)
                                <span class="text-gray-400 font-normal">({{ $ticket->ticketSubcategory->name }})</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Assigned To</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">First Response</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $ticket->response_time_minutes ? $ticket->response_time_minutes . ' mins' : 'Pending' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Resolution Time</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $ticket->resolution_time_hours ? $ticket->resolution_time_hours . ' hrs' : 'In Progress' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Created At</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->created_at?->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Requester Information -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-3 text-xs">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Requester Details</h3>
                <div class="space-y-2">
                    <p class="font-medium text-sm text-gray-800 dark:text-white">{{ $ticket->requester_name ?? '-' }}</p>
                    <p class="text-gray-500">{{ $ticket->requester_email ?? '-' }}</p>
                    <p class="text-gray-500">{{ $ticket->requesterDepartment?->name ?? '-' }} / {{ $ticket->requesterCompany?->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Assetika ITAM Integration -->
            @if($ticket->asset)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Linked IT Asset</h3>
                        <a href="{{ route('assets.show', $ticket->asset_id) }}" class="text-brand-500 font-medium hover:underline">View Asset</a>
                    </div>
                    <div class="p-3 bg-brand-50/50 rounded-xl dark:bg-brand-900/10 space-y-1.5">
                        <p class="font-bold text-brand-700 dark:text-brand-300 font-mono">{{ $ticket->asset->asset_tag }}</p>
                        <p class="text-gray-700 dark:text-gray-300 font-medium">{{ $ticket->asset->device_name ?? $ticket->asset->name }}</p>
                        <p class="text-gray-500">S/N: {{ $ticket->asset->serial_number ?? '-' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
