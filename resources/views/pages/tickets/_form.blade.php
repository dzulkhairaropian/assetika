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
            // Put requester's assets first with tag
            const markedUserAssets = userAssets.map(a => ({
                id: a.id,
                text: '★ ' + a.text
            }));
            const otherAssets = this.allAssets.filter(a => a.user_id != userId);
            options = [...options, ...markedUserAssets, ...otherAssets];

            // Auto-select if requested and user has assets
            let selectId = this.currentAssetId;
            if (autoSelectFirst || !selectId) {
                selectId = userAssets[0].id;
                this.currentAssetId = selectId;
            }

            this.$dispatch('set-select-options', { name: 'asset_id', options: options, selectedId: selectId });
        } else {
            options = [...options, ...this.allAssets];
            this.$dispatch('set-select-options', { name: 'asset_id', options: options, selectedId: this.currentAssetId });
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
@select-changed.window="if ($event.detail.name === 'requester_user_id') onRequesterChange($event.detail.id); if ($event.detail.name === 'asset_id') currentAssetId = $event.detail.id;"
>
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
                    <x-form.searchable-select
                        name="ticket_type_id"
                        label="Ticket Type"
                        :options="$ticketTypes->map(fn($t) => ['id' => $t->id, 'text' => $t->name])->toArray()"
                        :selected="old('ticket_type_id', $ticket?->ticket_type_id ?? ($ticketTypes->where('name', 'Incident')->first()?->id ?? ''))"
                        placeholder="Select Type"
                    />
                </div>

                <!-- Requester -->
                @php
                    $userOptions = $users->map(fn($u) => ['id' => $u->id, 'text' => $u->name . ' (' . $u->email . ')'])->toArray();
                @endphp
                <x-form.searchable-select
                    name="requester_user_id"
                    label="Requester User"
                    :options="$userOptions"
                    :selected="old('requester_user_id', $ticket?->requester_user_id)"
                    placeholder="Select User"
                    required="true"
                />

                <!-- Assigned To -->
                @php
                    $staffList = $staffUsers ?? $users->filter(fn($u) => $u->hasAnyRole(['it_admin', 'super_admin']));
                    if ($staffList->isEmpty()) $staffList = $users;
                    $staffOptions = $staffList->map(fn($u) => [
                        'id' => $u->id, 
                        'text' => $u->name . ($u->department ? ' (' . $u->department->name . ')' : '')
                    ])->toArray();
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
                    <x-form.searchable-select
                        name="ticket_category_id"
                        label="ITSM Category"
                        :options="$ticketCategories->map(fn($cat) => ['id' => $cat->id, 'text' => $cat->name])->toArray()"
                        :selected="old('ticket_category_id', $ticket?->ticket_category_id)"
                        placeholder="Select Category"
                    />
                </div>

                <!-- Priority -->
                <div>
                    <x-form.searchable-select
                        name="priority_id"
                        label="Priority"
                        :options="$priorities->map(fn($p) => ['id' => $p->id, 'text' => $p->name])->toArray()"
                        :selected="old('priority_id', $ticket?->priority_id ?? ($priorities->where('name', 'Medium')->first()?->id ?? ''))"
                        placeholder="Select Priority"
                    />
                </div>

                <!-- Status -->
                <div>
                    <x-form.searchable-select
                        name="status_id"
                        label="Status"
                        :options="$ticketStatuses->map(fn($s) => ['id' => $s->id, 'text' => $s->name])->toArray()"
                        :selected="old('status_id', $ticket?->status_id ?? ($ticketStatuses->where('name', 'New')->first()?->id ?? ''))"
                        placeholder="Select Status"
                    />
                </div>

                <!-- Label -->
                <div>
                    <x-form.searchable-select
                        name="label_id"
                        label="Label"
                        :options="$labels->map(fn($l) => ['id' => $l->id, 'text' => $l->name])->toArray()"
                        :selected="old('label_id', $ticket?->label_id)"
                        placeholder="Select Label"
                    />
                </div>

                <!-- Source -->
                <div>
                    <x-form.searchable-select
                        name="source_id"
                        label="Source"
                        :options="$sources->map(fn($src) => ['id' => $src->id, 'text' => $src->name])->toArray()"
                        :selected="old('source_id', $ticket?->source_id ?? ($sources->where('name', 'Portal')->first()?->id ?? ''))"
                        placeholder="Select Source"
                    />
                </div>

                <!-- SLA Status -->
                <div>
                    <x-form.searchable-select
                        name="sla_status_id"
                        label="SLA Status"
                        :options="$slaStatuses->map(fn($sla) => ['id' => $sla->id, 'text' => $sla->name])->toArray()"
                        :selected="old('sla_status_id', $ticket?->sla_status_id ?? ($slaStatuses->where('name', 'Fulfilled')->first()?->id ?? ''))"
                        placeholder="Select SLA Status"
                    />
                </div>

                <!-- Linked IT Asset -->
                @php
                    $assetOptions = array_merge(
                        [['id' => '', 'text' => 'No Asset Linked']],
                        $assets->map(function($asset) {
                            $holder = $asset->currentCheckout?->checkedOutToUser?->name ?? $asset->assignedToUser?->name;
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
            @if(!$ticket)
                <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-100 dark:bg-brand-950 dark:text-brand-300 dark:border-brand-800 transition">
                    Create Another
                </button>
            @endif
            <a href="{{ route('tickets.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                Cancel
            </a>
        </div>
    </div>
</div>
