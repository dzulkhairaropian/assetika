@php
    $closedStatusIds = $ticketStatuses->filter(fn($s) => $s->is_closed || in_array($s->name, ['Completed', 'Closed', 'Resolved']))->pluck('id')->toArray();
    $completedStatusId = $ticketStatuses->firstWhere('name', 'Completed')?->id ?? ($ticketStatuses->firstWhere('is_closed', true)?->id ?? 4);
@endphp
<div class="space-y-4" x-data="{
    selectedCategory: '{{ old('ticket_category_id', $ticket?->ticket_category_id) }}',
    selectedSubcategory: '{{ old('ticket_subcategory_id', $ticket?->ticket_subcategory_id) }}',
    categories: {{ json_encode($ticketCategories) }},
    statusId: '{{ old('status_id', $ticket?->status_id ?? ($ticketStatuses->where('name', 'New')->first()?->id ?? '')) }}',
    closedIds: {{ json_encode($closedStatusIds) }},
    completedStatusId: '{{ $completedStatusId }}',
    completedDate: '{{ old('completed_date', $ticket?->completed_date?->format('Y-m-d')) }}',
    completedTime: '{{ old('completed_time', $ticket?->completed_time?->format('H:i')) }}',
    get subcategories() {
        if (!this.selectedCategory) return [];
        const cat = this.categories.find(c => c.id == this.selectedCategory);
        return cat ? cat.subcategories : [];
    },
    onStatusChange() {
        const val = parseInt(this.statusId);
        if (this.closedIds.includes(val)) {
            const now = new Date();
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const dd = String(now.getDate()).padStart(2, '0');
            const hh = String(now.getHours()).padStart(2, '0');
            const min = String(now.getMinutes()).padStart(2, '0');
            
            if (!this.completedDate) this.completedDate = `${yyyy}-${mm}-${dd}`;
            if (!this.completedTime) this.completedTime = `${hh}:${min}`;
        }
    },
    onDateChange() {
        if (this.completedDate && this.completedStatusId) {
            const currentVal = parseInt(this.statusId);
            if (!this.closedIds.includes(currentVal)) {
                this.statusId = this.completedStatusId;
            }
            if (!this.completedTime) {
                const now = new Date();
                const hh = String(now.getHours()).padStart(2, '0');
                const min = String(now.getMinutes()).padStart(2, '0');
                this.completedTime = `${hh}:${min}`;
            }
        }
    }
}">
    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                <i data-feather="life-buoy" class="w-4 h-4 text-brand-500"></i>
                Ticket Information
            </h3>

            <div class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                <!-- Ticket Creation Date (Backdate) -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Ticket Date & Time <span class="text-[11px] text-gray-400 font-normal">(Backdate supported)</span>
                    </label>
                    <input type="datetime-local" name="created_at"
                        value="{{ old('created_at', isset($ticket) ? ($ticket->created_at ? $ticket->created_at->format('Y-m-d\TH:i') : '') : date('Y-m-d\TH:i')) }}"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    @error('created_at') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Ticket Type -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Ticket Type</label>
                    <select name="ticket_type_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">Select Type</option>
                        @foreach($ticketTypes as $type)
                            <option value="{{ $type->id }}" {{ old('ticket_type_id', $ticket?->ticket_type_id ?? ($ticketTypes->where('name', 'Incident')->first()?->id ?? '')) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Requester -->
                @php
                    $userOptions = $users->map(fn($u) => ['id' => $u->id, 'text' => $u->name . ' (' . $u->email . ')'])->toArray();
                @endphp
                <x-form.searchable-select
                    name="requester_user_id"
                    label="Requester User"
                    :options="$userOptions"
                    :selected="old('requester_user_id', $ticket?->requester_user_id ?? auth()->id())"
                    placeholder="Select User"
                    required="true"
                />

                <!-- Assigned To -->
                @php
                    $staffOptions = array_merge([['id' => '', 'text' => 'Unassigned']], $users->map(fn($u) => ['id' => $u->id, 'text' => $u->name])->toArray());
                @endphp
                <x-form.searchable-select
                    name="assigned_to_id"
                    label="Assigned Staff"
                    :options="$staffOptions"
                    :selected="old('assigned_to_id', $ticket?->assigned_to_id)"
                    placeholder="Unassigned"
                />

                <!-- ITSM Ticket Category -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">ITSM Category</label>
                    <select name="ticket_category_id" x-model="selectedCategory" @change="selectedSubcategory = ''" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">Select Category</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name" :selected="cat.id == selectedCategory"></option>
                        </template>
                    </select>
                </div>

                <!-- ITSM Ticket Subcategory -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">ITSM Subcategory</label>
                    <select name="ticket_subcategory_id" x-model="selectedSubcategory" :disabled="!selectedCategory" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 disabled:opacity-50 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">Select Subcategory</option>
                        <template x-for="sub in subcategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name" :selected="sub.id == selectedSubcategory"></option>
                        </template>
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                    <select name="priority_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">Select Priority</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ old('priority_id', $ticket?->priority_id ?? ($priorities->where('name', 'Medium')->first()?->id ?? '')) == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status_id" x-model="statusId" @change="onStatusChange()" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">Select Status</option>
                        @foreach($ticketStatuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Label -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                    <select name="label_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">Select Label</option>
                        @foreach($labels as $label)
                            <option value="{{ $label->id }}" {{ old('label_id', $ticket?->label_id) == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Source -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Source</label>
                    <select name="source_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">Select Source</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}" {{ old('source_id', $ticket?->source_id ?? ($sources->where('name', 'Portal')->first()?->id ?? '')) == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- SLA Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SLA Status</label>
                    <select name="sla_status_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">Select SLA Status</option>
                        @foreach($slaStatuses as $slaStatus)
                            <option value="{{ $slaStatus->id }}" {{ old('sla_status_id', $ticket?->sla_status_id ?? ($slaStatuses->where('name', 'Fulfilled')->first()?->id ?? '')) == $slaStatus->id ? 'selected' : '' }}>{{ $slaStatus->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Linked IT Asset -->
                @php
                    $assetOptions = array_merge(
                        [['id' => '', 'text' => 'No Asset Linked']],
                        $assets->map(function($asset) {
                            $holder = $asset->currentCheckout?->checkedOutToUser?->name ?? $asset->assignedToUser?->name;
                            $dept = $asset->department?->name;
                            $statusName = $asset->status?->name;
                            $brandModel = optional($asset->product?->brand)->name . ' ' . optional($asset->product)->model;
                            $deviceName = $asset->device_name ?: trim($brandModel);
                            return [
                                'id' => $asset->id,
                                'text' => '[' . $asset->asset_tag . '] ' . $deviceName . ' — ' . ($holder ? 'User: ' . $holder : 'Unassigned') . ($dept ? ' ('.$dept.')' : '') . ($statusName ? ' [' . $statusName . ']' : '')
                            ];
                        })->toArray()
                    );
                @endphp
                <x-form.searchable-select
                    name="asset_id"
                    label="Linked Asset (ITAM)"
                    :options="$assetOptions"
                    :selected="old('asset_id', $ticket?->asset_id)"
                    placeholder="No Asset Linked"
                    class="sm:col-span-2"
                />
            </div>
        </div>

        <!-- Problem Description -->
        <div class="pt-1">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                <i data-feather="file-text" class="w-4 h-4 text-brand-500"></i>
                Details & Description
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Problem Description <span class="text-red-500">*</span></label>
                    <textarea name="problem_description" rows="4" required class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('problem_description', $ticket?->problem_description) }}</textarea>
                </div>

                <!-- Attachment -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Attachment / Screenshot (Max 10MB)</label>
                    <input type="file" name="attachment" class="w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300"/>
                </div>
            </div>
        </div>

        <!-- Internal & Resolution Notes for Edit -->
        @if($ticket)
            <div class="pt-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                    <i data-feather="check-square" class="w-4 h-4 text-brand-500"></i>
                    Notes & Resolution
                </h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Completed Date</label>
                        <input type="date" name="completed_date" x-model="completedDate" @input="onDateChange()" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Completed Time</label>
                        <input type="time" name="completed_time" x-model="completedTime" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Internal Notes</label>
                        <textarea name="internal_notes" rows="3" class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('internal_notes', $ticket->internal_notes) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Resolution Notes</label>
                        <textarea name="resolution_notes" rows="3" class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                    </div>
                </div>
            </div>
        @endif

        <!-- Hours -->
        <div class="pt-1">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                <i data-feather="clock" class="w-4 h-4 text-brand-500"></i>
                Hours
            </h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <!-- Estimated Hours -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Hours</label>
                    <input type="number" name="estimated_hours" value="{{ old('estimated_hours', $ticket?->estimated_hours ?? 0) }}" step="0.5" min="0" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    @error('estimated_hours')
                        <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actual Hours -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Actual Hours</label>
                    <input type="number" name="actual_hours" value="{{ old('actual_hours', $ticket?->actual_hours ?? 0) }}" step="0.5" min="0" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    @error('actual_hours')
                        <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
            <i data-feather="save" class="w-4 h-4 text-brand-500"></i>
            Actions
        </h3>
        <div class="space-y-2.5">
            <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                {{ $ticket ? 'Update Ticket' : 'Save Ticket' }}
            </button>
            <a href="{{ route('tickets.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                Cancel
            </a>
        </div>
    </div>
</div>
