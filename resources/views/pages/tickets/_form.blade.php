@php
    $closedStatusIds = $ticketStatuses->filter(fn($s) => $s->is_closed || in_array($s->name, ['Completed', 'Closed', 'Resolved']))->pluck('id')->toArray();
    $completedStatusId = $ticketStatuses->firstWhere('name', 'Completed')?->id ?? ($ticketStatuses->firstWhere('is_closed', true)?->id ?? 4);

    $allAssetItems = $assets->map(function($asset) {
        $holderUser = $asset->currentCheckout?->checkedOutToUser ?? $asset->assignedToUser;
        $holder = $holderUser?->name;
        $holderId = $holderUser?->id;
        $dept = $asset->department?->name;
        $statusName = $asset->status?->name;
        $brandName = optional($asset->product?->brand)->name;
        $modelName = optional($asset->product)->model;
        $productTitle = optional($asset->product)->title ?? optional($asset->product)->name;
        $productInfo = array_filter([$productTitle, trim($brandName . ' ' . $modelName)]);
        $productStr = !empty($productInfo) ? implode(' / ', array_unique($productInfo)) : '';
        $deviceName = $asset->device_name ? $asset->device_name . ($productStr ? ' (' . $productStr . ')' : '') : ($productStr ?: 'Unknown Product');
        return [
            'id' => $asset->id,
            'user_id' => $holderId,
            'text' => '[' . $asset->asset_tag . '] ' . $deviceName . ' — ' . ($holder ? 'User: ' . $holder : 'Unassigned') . ($dept ? ' ('.$dept.')' : '') . ($statusName ? ' [' . $statusName . ']' : '')
        ];
    })->values()->toArray();

    $selTypeId = old('ticket_type_id', $ticket?->ticket_type_id ?? ($ticketTypes->where('name', 'Incident')->first()?->id ?? ''));
    $selRequesterId = old('requester_user_id', $ticket?->requester_user_id);
    $selAssignedId = old('assigned_to_id', $ticket?->assigned_to_id);
    $selCatId = old('ticket_category_id', $ticket?->ticket_category_id);
    $selPriorityId = old('priority_id', $ticket?->priority_id ?? ($priorities->where('name', 'Medium')->first()?->id ?? ''));
    $selStatusId = old('status_id', $ticket?->status_id ?? ($ticketStatuses->where('name', 'New')->first()?->id ?? ''));
    $selLabelId = old('label_id', $ticket?->label_id);
    $selSourceId = old('source_id', $ticket?->source_id ?? ($sources->first()?->id ?? ''));
@endphp
<div class="space-y-4" x-data="{
    selectedCategory: '{{ old('ticket_category_id', $ticket?->ticket_category_id) }}',
    statusId: '{{ old('status_id', $ticket?->status_id ?? ($ticketStatuses->where('name', 'New')->first()?->id ?? '')) }}',
    closedIds: {{ json_encode($closedStatusIds) }},
    completedStatusId: '{{ $completedStatusId }}',
    completedDate: '{{ old('completed_date', $ticket?->completed_date?->format('Y-m-d')) }}',
    completedTime: '{{ old('completed_time', $ticket?->completed_time?->format('H:i')) }}',
    allAssets: {{ json_encode($allAssetItems) }},
    currentRequesterId: '{{ old('requester_user_id', $ticket?->requester_user_id) }}',
    currentAssetId: '{{ old('asset_id', $ticket?->asset_id) }}',
    priorityId: '{{ $selPriorityId }}',

    init() {
        if (this.currentRequesterId) {
            this.filterAssetsByRequester(this.currentRequesterId, false);
        }
    },
    onRequesterChange(userId) {
        this.currentRequesterId = userId;
        this.filterAssetsByRequester(userId, true);
    },
    filterAssetsByRequester(userId, autoSelectFirst = true) {
        if (!userId) {
            const defaultOptions = [{ id: '', text: 'No Asset Linked' }, ...this.allAssets];
            this.$dispatch('set-select-options', { name: 'asset_id', options: defaultOptions, selectedId: this.currentAssetId });
            return;
        }

        const userAssets = this.allAssets.filter(a => a.user_id == userId);
        let options = [{ id: '', text: 'No Asset Linked' }];

        if (userAssets.length > 0) {
            options = [...options, ...userAssets.map(a => ({ id: a.id, text: a.text }))];

            let selectId = this.currentAssetId;
            if (autoSelectFirst || !userAssets.some(a => a.id == selectId)) {
                selectId = userAssets[0].id;
                this.currentAssetId = selectId;
            }

            this.$dispatch('set-select-options', { name: 'asset_id', options: options, selectedId: selectId });
        } else {
            this.currentAssetId = '';
            this.$dispatch('set-select-options', { name: 'asset_id', options: options, selectedId: '' });
        }
    },
    setToday() {
        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        const hh = String(now.getHours()).padStart(2, '0');
        const min = String(now.getMinutes()).padStart(2, '0');
        this.completedDate = `${yyyy}-${mm}-${dd}`;
        if (!this.completedTime) this.completedTime = `${hh}:${min}`;
        this.onDateChange();
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
}"
@select-changed.window="if ($event.detail.name === 'requester_user_id') onRequesterChange($event.detail.id); if ($event.detail.name === 'asset_id') currentAssetId = $event.detail.id; if ($event.detail.name === 'priority_id') priorityId = $event.detail.id;"
>
    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-5">
        <div>
            <div class="border-b border-gray-100 pb-3 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="life-buoy" class="w-5 h-5 text-brand-500"></i>
                        {{ $ticket ? 'Edit Incident Ticket' : 'Create Incident' }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Employee self-service, phone, chat, or email intake
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                        Calculated priority: P4 Low
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 mt-4">
                <!-- Requester -->
                <div>
                    <x-form.searchable-select name="requester_user_id" label="Requester *" :options="$userOpts" :selected="$selRequesterId" placeholder="Select Requester (e.g. Aisha Banks - Customer Success)" required="true" />
                </div>

                <!-- Affected configuration item (Linked Asset) -->
                <div>
                    <x-form.searchable-select name="asset_id" label="Affected configuration item" :options="[['id' => '', 'text' => 'No Configuration Item Linked'], ...$allAssetItems]" :selected="old('asset_id', $ticket?->asset_id)" placeholder="Select device / asset item..." />
                </div>

                <!-- Category -->
                <div>
                    <x-form.searchable-select name="ticket_category_id" label="Category *" :options="$catOpts" :selected="$selCatId" placeholder="Select Category (e.g. Corporate IT, Hardware)" />
                </div>

                <!-- Assignment group / Staff -->
                <div>
                    <x-form.searchable-select name="assigned_to_id" label="Assignment group / Staff *" :options="$staffOpts" :selected="$selAssignedId" placeholder="Service Desk Tier 1 (Unassigned)" />
                </div>

                <!-- Ticket Type -->
                <div>
                    <x-form.searchable-select name="ticket_type_id" label="Issue / Ticket Type *" :options="$typeOpts" :selected="$selTypeId" placeholder="General incident / request" />
                </div>

                <!-- Priority -->
                <div>
                    <x-form.searchable-select name="priority_id" label="Priority / Impact *" :options="$priorityOpts" :selected="$selPriorityId" placeholder="Select Priority (P4 Low, P3 Moderate...)" />
                </div>

                <!-- Status -->
                <div>
                    <x-form.searchable-select name="status_id" label="Status" :options="$statusOpts" :selected="$selStatusId" placeholder="Select Status" />
                </div>

                <!-- Source / Channel Intake -->
                <div>
                    <x-form.searchable-select name="source_id" label="Source Contact / Channel" :options="$sourceOpts" :selected="$selSourceId" placeholder="Phone, Email, Chat, Self-Service" />
                </div>

                <!-- SLA Status -->
                <div>
                    <x-form.searchable-select
                        name="sla_status_id"
                        label="SLA Status Target"
                        :options="$slaStatuses->map(fn($sla) => ['id' => $sla->id, 'text' => $sla->name])->toArray()"
                        :selected="old('sla_status_id', $ticket?->sla_status_id ?? ($slaStatuses->where('name', 'Fulfilled')->first()?->id ?? ''))"
                        placeholder="Select SLA Target"
                    />
                </div>

                <!-- Ticket Date & Time (Backdate) -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Ticket Intake Date & Time <span class="text-[11px] text-gray-400 font-normal">(Backdate supported)</span>
                    </label>
                    <input type="datetime-local" name="created_at"
                        value="{{ old('created_at', isset($ticket) ? ($ticket->created_at ? $ticket->created_at->format('Y-m-d\TH:i') : '') : date('Y-m-d\TH:i')) }}"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    @error('created_at') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Problem Description -->
        <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i>
                Incident Details & Report
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Short description <span class="text-red-500">*</span>
                    </label>
                    <textarea name="problem_description" rows="2" required placeholder="Write one searchable sentence with the symptom and affected service or device, e.g. Warehouse scanner cannot sync inventory on NL-TAB-0221." class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white transition">{{ old('problem_description', $ticket?->problem_description) }}</textarea>
                    <p class="mt-1 text-[11px] text-gray-400">Write one searchable sentence with the symptom and affected service or device.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Description (requester report)
                    </label>
                    <textarea name="internal_notes" rows="4" placeholder='Write this in the requester's voice, e.g. "Hi, this is Jordan from Sales. I cannot open any websites, it started after this morning's meeting, and I need access before my client call." Include timing, exact errors, what they tried, and business impact.' class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white transition">{{ old('internal_notes', $ticket?->internal_notes) }}</textarea>
                    <p class="mt-1 text-[11px] text-gray-400">Include timing, exact errors, what they tried, and business impact in the requester's voice.</p>
                </div>

                <!-- Attachment -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Attachment / Screenshot (Max 10MB)</label>
                    <input type="file" name="attachment" class="w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300"/>
                </div>
            </div>
        </div>

        <!-- Resolution Notes for Edit -->
        @if($ticket)
            <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <i data-lucide="check-square" class="w-4 h-4 text-brand-500"></i>
                    Notes & Resolution
                </h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Completed Date
                            <button type="button" @click="setToday()" class="ml-1 text-[11px] font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 focus:outline-none">
                                [Today]
                            </button>
                        </label>
                        <input type="date" name="completed_date" x-model="completedDate" @input="onDateChange()" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Completed Time</label>
                        <input type="time" name="completed_time" x-model="completedTime" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Resolution Notes</label>
                        <textarea name="resolution_notes" rows="3" class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
            <i data-lucide="save" class="w-4 h-4 text-brand-500"></i>
            Actions
        </h3>
        <div class="space-y-2.5">
            <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                {{ $ticket ? 'Update Incident' : 'Create incident' }}
            </button>
            @if(!$ticket)
                <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-100 dark:bg-brand-950 dark:text-brand-300 dark:border-brand-800 transition">
                    Create & Create Another
                </button>
            @endif
            <a href="{{ route('tickets.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                Cancel
            </a>
        </div>
        <p class="mt-3 text-[11px] text-gray-400 dark:text-gray-500 leading-relaxed border-t border-gray-100 dark:border-gray-800 pt-2.5">
            Source contact is linked when this form is opened from mail, chat, or a call.
        </p>
    </div>
</div>
