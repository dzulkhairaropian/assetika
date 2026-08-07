@php
    $isEdit = isset($ticket);
    $selectedAssetId = old('asset_id', $ticket?->asset_id ?? '');
@endphp

<x-app-layout :title="$isEdit ? 'Edit Ticket #' . $ticket->request_id : 'Create New Ticket'">
    <div class="space-y-6" x-data="{
        requesterId: '{{ old('requester_user_id', $ticket?->requester_user_id ?? '') }}',
        assetId: '{{ $selectedAssetId }}',
        assets: {{ json_encode($assets->map(fn($a) => [
            'id' => $a->id,
            'assigned_to_user_id' => $a->assigned_to_user_id,
            'asset_tag' => $a->asset_tag ?? 'N/A',
            'device_name' => $a->device_name ?? 'No Name',
            'serial_number' => $a->serial_number ?? 'N/A',
            'product_name' => $a->product?->name ?? 'N/A',
            'holder' => $a->assignedToUser?->name ?? 'Not Assigned',
            'location' => $a->location?->name ?? 'N/A',
            'department' => $a->department?->name ?? 'N/A',
        ])) }},
        get filteredAssets() {
            if (!this.requesterId) return [];
            return this.assets.filter(a => a.assigned_to_user_id == this.requesterId);
        },
        get activeAsset() {
            return this.assets.find(a => a.id == this.assetId);
        }
    }">
        <!-- Breadcrumb Start -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                {{ $isEdit ? "Edit Ticket #{$ticket->request_id}" : 'Create New Ticket' }}
            </h2>
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                ← Back to List
            </a>
        </div>
        <!-- Breadcrumb End -->

        <form method="POST" action="{{ $isEdit ? route('tickets.update', $ticket) : route('tickets.store') }}" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <!-- Left Panel: Form Input Sections (8 Cols) -->
            <div class="space-y-6 lg:col-span-8">
                {{-- Requester & Asset --}}
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Requester & Asset</h3>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="requester_user_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Requester User *</label>
                                <select name="requester_user_id" id="requester_user_id" x-model="requesterId" @change="assetId = ''" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('requester_user_id', $ticket?->requester_user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                @error('requester_user_id') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="asset_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Problematic Asset</label>
                                <select name="asset_id" id="asset_id" x-model="assetId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">— No Asset —</option>
                                    <template x-for="asset in filteredAssets" :key="asset.id">
                                        <option :value="asset.id" x-text="asset.asset_tag + ' — ' + asset.device_name + ' (S/N: ' + asset.serial_number + ')'"></option>
                                    </template>
                                </select>
                                @error('asset_id') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Issue Details --}}
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Issue Details</h3>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="category_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Category</label>
                                <select name="category_id" id="category_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $ticket?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="label_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Label</label>
                                <select name="label_id" id="label_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">Select Label</option>
                                    @foreach($labels as $l)
                                        <option value="{{ $l->id }}" {{ old('label_id', $ticket?->label_id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="problem_description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Problem Description *</label>
                            <textarea name="problem_description" id="problem_description" rows="5" required placeholder="Describe the issue in detail..." class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('problem_description', $ticket?->problem_description) }}</textarea>
                            @error('problem_description') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="internal_notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Internal Notes (visible to IT only)</label>
                            <textarea name="internal_notes" id="internal_notes" rows="3" placeholder="Notes for IT staff..." class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('internal_notes', $ticket?->internal_notes) }}</textarea>
                        </div>
                    </div>
                </div>

                @if($isEdit)
                {{-- Resolution --}}
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Resolution</h3>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                        <div>
                            <label for="resolution_time_hours" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Resolution Time (hours)</label>
                            <input type="number" step="0.1" name="resolution_time_hours" id="resolution_time_hours" value="{{ old('resolution_time_hours', $ticket->resolution_time_hours) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        <div>
                            <label for="resolution_notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Resolution Notes</label>
                            <textarea name="resolution_notes" id="resolution_notes" rows="4" placeholder="How was the issue resolved?" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Panel: Metadata & Asset Preview Panel (4 Cols) -->
            <div class="space-y-6 lg:col-span-4">
                {{-- Asset Preview Panel --}}
                <div x-show="activeAsset" x-transition class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Asset Preview</h3>
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Asset Tag</span>
                            <code class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400" x-text="activeAsset ? activeAsset.asset_tag : ''"></code>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Device Name</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="activeAsset ? activeAsset.device_name : ''"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Serial Number</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="activeAsset ? activeAsset.serial_number : ''"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Product</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="activeAsset ? activeAsset.product_name : ''"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Location</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="activeAsset ? activeAsset.location : ''"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2 dark:border-gray-800">
                            <span>Department</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="activeAsset ? activeAsset.department : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Holder</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="activeAsset ? activeAsset.holder : ''"></span>
                        </div>
                    </div>
                </div>

                {{-- Classification & Assignee Settings --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Ticket Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="ticket_type_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ticket Type</label>
                            <select name="ticket_type_id" id="ticket_type_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select Type</option>
                                @foreach($ticketTypes as $t)
                                    <option value="{{ $t->id }}" {{ old('ticket_type_id', $ticket?->ticket_type_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="priority_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Priority</label>
                            <select name="priority_id" id="priority_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select Priority</option>
                                @foreach($priorities as $p)
                                    <option value="{{ $p->id }}" {{ old('priority_id', $ticket?->priority_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="source_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Source</label>
                            <select name="source_id" id="source_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select Source</option>
                                @foreach($sources as $s)
                                    <option value="{{ $s->id }}" {{ old('source_id', $ticket?->source_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                            <select name="status_id" id="status_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select Status</option>
                                @foreach($ticketStatuses as $s)
                                    <option value="{{ $s->id }}" {{ old('status_id', $ticket?->status_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="sla_status_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">SLA Status</label>
                            <select name="sla_status_id" id="sla_status_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select SLA</option>
                                @foreach($slaStatuses as $s)
                                    <option value="{{ $s->id }}" {{ old('sla_status_id', $ticket?->sla_status_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="assigned_to_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Assigned To (IT Staff)</label>
                            <select name="assigned_to_id" id="assigned_to_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Unassigned</option>
                                @foreach($users->where('role', 'it') as $staff)
                                    <option value="{{ $staff->id }}" {{ old('assigned_to_id', $ticket?->assigned_to_id) == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('tickets.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Cancel</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        {{ $isEdit ? 'Update Ticket' : 'Create Ticket' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
