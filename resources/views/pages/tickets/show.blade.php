@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ openProjectModal: false, cannedNote: '' }">
    <x-common.page-breadcrumb pageTitle="Incident Ticket Detail" :items="[['label' => 'Service Desk', 'url' => route('tickets.index')]]" />

    {{-- Ticket Top Header Bar --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-base font-bold text-gray-900 dark:text-white">#{{ $ticket->request_id }}</span>
                <span class="text-gray-400 font-normal">&mdash;</span>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">
                    {{ \Illuminate\Support\Str::limit($ticket->problem_description, 70) }}
                </h2>
                <x-ui.badge :color="$ticket->status?->color ?? 'primary'" variant="light" size="sm" :featherIcon="$ticket->status?->icon ?: 'disc'">
                    {{ $ticket->status?->name ?? 'New' }}
                </x-ui.badge>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ $ticket->ticketCategory?->name ?? 'Corporate IT' }} &middot; 
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ $ticket->assignedTo?->name ? 'Assigned to ' . $ticket->assignedTo->name : 'Service Desk Tier 1 (Unassigned)' }}
                </span>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if(!$ticket->assigned_to_id || $ticket->assigned_to_id !== auth()->id())
                <form action="{{ route('tickets.claim', $ticket) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition" title="Assign to me to contact the requester">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        Assign to me
                    </button>
                </form>
            @endif

            @if(isset($projects) && $projects->count() > 0)
                <button type="button" @click="openProjectModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-purple-200 bg-purple-50 px-3 py-1.5 text-xs font-semibold text-purple-700 hover:bg-purple-100 dark:border-purple-800 dark:bg-purple-950/40 dark:text-purple-300 transition">
                    <i data-lucide="layout" class="w-3.5 h-3.5"></i>
                    Add to Project Task
                </button>
            @endif

            <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                Edit
            </a>
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        <!-- Main Content Column (Left - 8 cols) -->
        <div class="space-y-4 lg:col-span-8">

            <!-- Description (requester report) -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="message-square" class="w-4 h-4 text-brand-500"></i>
                        Description (requester report)
                    </h3>
                    <span class="text-[11px] text-gray-400 font-mono">{{ $ticket->created_at?->format('M d, Y, g:i A') }}</span>
                </div>
                <div class="whitespace-pre-wrap text-xs text-gray-800 dark:text-gray-200 leading-relaxed bg-gray-50 p-3.5 rounded-lg dark:bg-gray-950 border border-gray-100 dark:border-gray-800 font-sans">
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

            <!-- Resolution Notes Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="check-square" class="w-4 h-4 text-emerald-500"></i>
                        Resolution Notes (visible to requester on close)
                    </h3>
                    <span class="text-[11px] text-gray-400">Summarize the cause, corrective action, and verification</span>
                </div>

                <form action="{{ route('tickets.update', $ticket) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <!-- Preserve required values -->
                    <input type="hidden" name="requester_user_id" value="{{ $ticket->requester_user_id }}">
                    <input type="hidden" name="problem_description" value="{{ $ticket->problem_description }}">
                    
                    <textarea name="resolution_notes" rows="3" x-model="cannedNote"
                        placeholder="Summarize the cause, corrective action, and how service was verified with the requester."
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white transition">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-gray-400">Quick template:</span>
                            <button type="button" @click="cannedNote = 'Issue verified and resolved. Component tested and confirmed working with requester.'" class="rounded border border-gray-200 bg-gray-50 px-2 py-1 text-[11px] font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 transition">
                                Canned closing
                            </button>
                            <button type="button" @click="cannedNote = 'Rebooted system and updated drivers. Hardware tested operational.'" class="rounded border border-gray-200 bg-gray-50 px-2 py-1 text-[11px] font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 transition">
                                Reboot & Drivers
                            </button>
                        </div>

                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            Save resolution notes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Workflow & Resolution Quick Actions -->
            <div class="rounded-xl border border-brand-100 bg-brand-50/40 p-4 dark:border-brand-900/30 dark:bg-brand-950/20 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300 flex items-center gap-2">
                    <i data-lucide="git-pull-request" class="w-4 h-4 text-brand-500"></i>
                    Workflow & Quick Resolution
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1">Resolution Code</label>
                        <form action="{{ route('tickets.update', $ticket) }}" method="POST" class="flex gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="requester_user_id" value="{{ $ticket->requester_user_id }}">
                            <input type="hidden" name="problem_description" value="{{ $ticket->problem_description }}">
                            <input type="hidden" name="status_id" value="{{ $ticketStatuses->firstWhere('name', 'Completed')?->id ?? ($ticketStatuses->firstWhere('is_closed', true)?->id ?? 4) }}">
                            
                            <select name="resolution_code" class="h-8 w-full rounded-lg border border-gray-200 bg-white px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Select Resolution Code...</option>
                                <option value="Solved - Permanently">Solved - Permanently</option>
                                <option value="Solved - Workaround">Solved - Workaround</option>
                                <option value="Closed - User Error">Closed - User Error</option>
                                <option value="Closed - Duplicate">Closed - Duplicate</option>
                            </select>

                            <button type="submit" class="shrink-0 rounded-lg bg-brand-500 px-3 py-1 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                Resolve Ticket
                            </button>
                        </form>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1">Quick Action</label>
                        <form action="{{ route('tickets.comment', $ticket) }}" method="POST">
                            @csrf
                            <input type="hidden" name="comment" value="Performed standard restart procedure on device to restore functionality.">
                            <input type="hidden" name="is_internal" value="1">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 w-full h-8 rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 transition">
                                <i data-lucide="rotate-cw" class="w-3.5 h-3.5 text-brand-500"></i>
                                Restart device (no diagnosis)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-3 dark:bg-gray-900 border border-brand-100 dark:border-brand-900/30 flex items-start gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                    <i data-lucide="book-open" class="w-4 h-4 text-brand-500 shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">Documentation Auto-Publishing:</span>
                        When you resolve this ticket, work notes, resolution notes, and resolution code are published as a formatted document under Documentation.
                    </div>
                </div>
            </div>

            <!-- Activity & Discussion Feed -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="activity" class="w-4 h-4 text-brand-500"></i>
                    Activity & Work Notes
                </h3>

                <!-- Add Comment Form -->
                <form action="{{ route('tickets.comment', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-2.5">
                    @csrf
                    <div>
                        <textarea name="comment" rows="3" required placeholder="Add work notes or response to requester..." class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                                <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                Internal note (visible to staff only)
                            </label>
                            <input type="file" name="attachment" class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300"/>
                        </div>
                        <button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            Post Note
                        </button>
                    </div>
                </form>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Comment Feed -->
                <div class="space-y-3">
                    @forelse($ticket->comments as $comment)
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
                        <p class="text-center text-xs text-gray-400 py-3">No work notes yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Details / ITSM Metadata (Right Column - 4 cols) -->
        <div class="space-y-4 lg:col-span-4">
            
            <!-- Incident details Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3 text-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2 dark:border-gray-800 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-brand-500"></i>
                    Incident details
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Number</span>
                        <span class="font-mono font-bold text-gray-900 dark:text-white">#{{ $ticket->request_id }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Priority</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->priority?->name ?? 'P4 Low' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Status</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->status?->name ?? 'new' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Service</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">Corporate IT</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Category</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $ticket->ticketCategory?->name ?? 'General' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Assignment</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->assignedTo?->name ?? 'Service Desk Tier 1' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">SLA response</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->created_at?->addHours(8)->format('M d, Y, g:i A') }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-500">SLA resolve</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->created_at?->addDays(3)->format('M d, Y, g:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Requester Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3 text-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2 dark:border-gray-800 flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-brand-500"></i>
                    Requester
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between py-0.5">
                        <span class="text-gray-500">Name</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $ticket->requester_name ?? $ticket->requesterUser?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-gray-500">Role</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $ticket->requesterUser?->position?->name ?? 'Staff / End User' }}</span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-gray-500">Dept</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $ticket->requesterDepartment?->name ?? $ticket->requesterUser?->department?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-gray-500">Email</span>
                        <span class="font-mono text-[11px] text-gray-700 dark:text-gray-300">{{ $ticket->requester_email ?? $ticket->requesterUser?->email ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-gray-500">Office</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $ticket->requesterCompany?->name ?? 'Corporate HQ' }}</span>
                    </div>
                </div>
            </div>

            <!-- Device / Asset Card -->
            @if($ticket->asset)
                @php
                    $assetHolder = $ticket->asset->currentCheckout?->checkedOutToUser?->name ?? $ticket->asset->assignedToUser?->name;
                    $assetDept = $ticket->asset->department?->name;
                @endphp
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                            <i data-lucide="laptop" class="w-3.5 h-3.5 text-brand-500"></i>
                            Device
                        </h3>
                        <a href="{{ route('assets.show', $ticket->asset_id) }}" class="text-brand-600 font-semibold hover:underline text-[11px]">View Asset</a>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Hostname</span>
                            <span class="font-mono font-bold text-brand-600 dark:text-brand-400">{{ $ticket->asset->device_name ?: $ticket->asset->asset_tag }}</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Asset</span>
                            <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->asset->asset_tag }}</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Model</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ optional($ticket->asset->product)->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">IP</span>
                            <span class="font-mono text-gray-700 dark:text-gray-300">10.20.14.224</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">Connectivity</span>
                            <span class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> connected
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
