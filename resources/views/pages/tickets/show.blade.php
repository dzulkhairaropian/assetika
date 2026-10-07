@extends('layouts.app')

@section('content')
@php
    $isStaff = auth()->user()?->hasAnyRole(['it_admin', 'super_admin']);
@endphp
<div class="space-y-4" x-data="{ openProjectModal: false }">
    <x-common.page-breadcrumb pageTitle="Ticket Detail" :items="[['label' => 'Tickets', 'url' => route('tickets.index')]]" />

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2.5">
            <h2 class="text-lg font-bold font-mono text-gray-900 dark:text-white">
                #{{ $ticket->request_id }}
            </h2>
            <x-ui.badge :color="$ticket->status?->color ?? 'primary'" variant="light" size="sm" :featherIcon="$ticket->status?->icon ?: 'disc'">
                {{ $ticket->status?->name ?? 'Open' }}
            </x-ui.badge>
        </div>
        <div class="flex gap-2">
            @if(isset($projects) && $projects->count() > 0)
                <button type="button" @click="openProjectModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-purple-200 bg-purple-50 px-3.5 py-1.5 text-xs font-semibold text-purple-700 hover:bg-purple-100 dark:border-purple-800 dark:bg-purple-950/40 dark:text-purple-300 transition">
                    <i data-lucide="layout" class="w-3.5 h-3.5"></i>
                    Add to Project Task
                </button>
            @endif
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                Back
            </a>
            <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                Edit Issue
            </a>
        </div>
    </div>

    @if (session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if (session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        <!-- Main Content Area: Problem, Comments, Activity Timeline -->
        <div class="space-y-4 lg:col-span-8">
            <!-- Issue Description -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Problem Description</h3>
                <div class="whitespace-pre-wrap text-xs text-gray-700 dark:text-gray-300 leading-relaxed bg-gray-50 p-3.5 rounded-lg dark:bg-gray-950">
                    {{ $ticket->problem_description }}
                </div>

                @if($ticket->attachments->count() > 0)
                    <div class="pt-1">
                        <h4 class="text-[11px] font-semibold uppercase text-gray-400 mb-1.5">Ticket Attachments</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($ticket->attachments as $attachment)
                                <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                                    <i data-lucide="file" class="w-3.5 h-3.5 text-brand-500"></i>
                                    <span>{{ $attachment->filename }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Resolution & Internal Notes / Lessons Learned (LL) -->
            @if($ticket->resolution_notes || ($isStaff && $ticket->internal_notes))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/20 p-4 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10 space-y-3">
                    @if($ticket->resolution_notes)
                        <div>
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <i data-lucide="book-open-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                    Resolution & Lessons Learned (LL)
                                </h4>
                            </div>
                            <div class="whitespace-pre-wrap text-xs text-gray-800 dark:text-gray-200 bg-white p-3.5 rounded-lg border border-emerald-100 dark:bg-gray-900 dark:border-emerald-900/30 shadow-2xs">
                                {{ $ticket->resolution_notes }}
                            </div>
                        </div>
                    @endif
                    @if($isStaff && $ticket->internal_notes)
                        <div class="{{ $ticket->resolution_notes ? 'pt-2 border-t border-emerald-100 dark:border-emerald-900/20' : '' }}">
                            <div class="flex items-center gap-1.5 mb-1 text-amber-600 dark:text-amber-400">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                <h4 class="text-[11px] font-semibold uppercase tracking-wider">Internal Notes</h4>
                            </div>
                            <div class="whitespace-pre-wrap text-xs text-gray-700 dark:text-gray-300 bg-amber-50/50 p-3 rounded-lg dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/20">
                                {{ $ticket->internal_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Comments & Discussion -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Activity & Discussion</h3>

                <!-- Add Comment Form -->
                <form action="{{ route('tickets.comment', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-2.5">
                    @csrf
                    <div>
                        <textarea name="comment" rows="3" required placeholder="Add a response or internal update..." class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            @if($isStaff)
                                <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                                    <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                    Internal note (visible to staff only)
                                </label>
                            @endif
                            <input type="file" name="attachment" class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300"/>
                        </div>
                        <button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            Add Comment
                        </button>
                    </div>
                </form>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Comment Feed -->
                <div class="space-y-3">
                    @forelse($ticket->comments as $comment)
                        @if($comment->is_internal && ! $isStaff)
                            @continue
                        @endif
                        <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800 {{ $comment->is_internal ? 'bg-orange-50/30 dark:bg-orange-950/20' : 'bg-gray-50/50 dark:bg-gray-950' }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-gray-900 dark:text-white">
                                    {{ $comment->user?->name ?? 'System' }}
                                    @if($comment->is_internal)
                                        <span class="ml-1.5 rounded bg-orange-100 px-1.5 py-0.5 text-[10px] font-semibold text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">Internal</span>
                                    @endif
                                </span>
                                <span class="text-[11px] text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->body ?? $comment->comment }}</p>

                            @if($comment->attachments->count() > 0)
                                <div class="mt-2 pt-1.5 border-t border-gray-100 dark:border-gray-800 flex flex-wrap gap-2">
                                    @foreach($comment->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-medium text-brand-600 hover:underline">
                                            <i data-lucide="paperclip" class="w-3 h-3"></i>
                                            {{ $attachment->filename }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-xs text-gray-400 py-3">No comments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Details / ITSM Metadata -->
        <div class="space-y-4 lg:col-span-4">
            <!-- Ticket Info -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3 text-xs">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Ticket Meta</h3>
                <div class="space-y-2">
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Priority</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->priority?->name ?? 'Normal' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Category</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $ticket->ticketCategory?->name ?? '-' }}
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
                        <span class="text-gray-500">Estimated Hours</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->estimated_hours ?? '0' }} hrs</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Actual Hours</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->actual_hours ?? '0' }} hrs</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-500">Created At</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->created_at?->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Requester Information -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-2 text-xs">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Requester Details</h3>
                <div class="space-y-1">
                    <p class="font-semibold text-xs text-gray-900 dark:text-white">{{ $ticket->requester_name ?? '-' }}</p>
                    <p class="text-gray-500">{{ $ticket->requester_email ?? '-' }}</p>
                    <p class="text-gray-500">{{ $ticket->requesterDepartment?->name ?? '-' }} / {{ $ticket->requesterCompany?->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Assetika ITAM Integration -->
            @if($ticket->asset)
                @php
                    $assetHolder = $ticket->asset->currentCheckout?->checkedOutToUser?->name ?? $ticket->asset->assignedToUser?->name;
                    $assetDept = $ticket->asset->department?->name;
                @endphp
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Linked IT Asset</h3>
                        <a href="{{ route('assets.show', $ticket->asset_id) }}" class="text-brand-600 font-semibold hover:underline">View Asset</a>
                    </div>
                    <div class="p-2.5 bg-brand-50/50 rounded-lg dark:bg-brand-950/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-brand-700 dark:text-brand-300 font-mono">{{ $ticket->asset->asset_tag }}</span>
                            @if($ticket->asset->status)
                                <x-ui.badge :color="$ticket->asset->status->color" variant="light" size="sm" :featherIcon="$ticket->asset->status->icon">
                                    {{ $ticket->asset->status->name }}
                                </x-ui.badge>
                            @endif
                        </div>
                        <p class="text-gray-900 dark:text-white font-semibold text-xs">{{ $ticket->asset->device_name ?? $ticket->asset->name }}</p>
                        <p class="text-gray-500 text-[11px]">S/N: {{ $ticket->asset->serial_number ?? '-' }}</p>
                        @if($assetHolder)
                            <p class="text-gray-700 dark:text-gray-300 pt-1 border-t border-brand-100 dark:border-brand-900/30 text-[11px]">
                                <span class="text-gray-400">User / Holder:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $assetHolder }}</span> {{ $assetDept ? '('.$assetDept.')' : '' }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Link to Project -->
    @if(isset($projects) && $projects->count() > 0)
        <div x-show="openProjectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak x-data="{ selectedProject: '{{ $projects->first()?->id }}' }">
            <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Convert Ticket to Project Task</h3>
                    <button type="button" @click="openProjectModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'/projects/' + selectedProject + '/tasks'" method="POST" class="space-y-3.5 text-left">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                    @if($ticket->asset_id)
                        <input type="hidden" name="asset_id" value="{{ $ticket->asset_id }}">
                    @endif

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Target Project <span class="text-error-500">*</span></label>
                        <select x-model="selectedProject" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                            @foreach($projects as $prj)
                                <option value="{{ $prj->id }}">{{ $prj->code }} - {{ $prj->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Task Title <span class="text-error-500">*</span></label>
                        <input type="text" name="title" value="[Ticket #{{ $ticket->request_id }}] {{ \Illuminate\Support\Str::limit($ticket->problem_description, 80) }}" required class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200"/>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Assign Tech/Staff</label>
                            <select name="assigned_to_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                <option value="">Unassigned</option>
                                <option value="{{ $ticket->assigned_to_id }}" selected>{{ $ticket->assignedTo?->name ?: 'Same as Ticket Assignee' }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Priority</label>
                            <select name="priority" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                <option value="low">Low</option>
                                <option value="medium" {{ strtolower($ticket->priority?->name) === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ strtolower($ticket->priority?->name) === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ in_array(strtolower($ticket->priority?->name), ['urgent', 'critical']) ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Task Notes / Details</label>
                        <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">{{ $ticket->problem_description }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openProjectModal = false" class="rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">Add to Project Task</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
