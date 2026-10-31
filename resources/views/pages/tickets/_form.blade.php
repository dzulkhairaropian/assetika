@php
    $closedStatusIds = $ticketStatuses->filter(fn($s) => $s->is_closed || in_array($s->name, ['Completed', 'Closed', 'Resolved']))->pluck('id')->toArray();
    $completedStatusId = $ticketStatuses->firstWhere('name', 'Resolved')?->id ?? ($ticketStatuses->firstWhere('is_closed', true)?->id ?? 4);

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

    $isStaff = (bool) auth()->user()?->hasAnyRole(['it_admin', 'super_admin']);
    $selTypeId = old('ticket_type_id', $ticket?->ticket_type_id ?? ($ticketTypes->where('name', 'Incident')->first()?->id ?? ''));
    $selRequesterId = old('requester_user_id', $ticket?->requester_user_id);
    $selAssignedId = old('assigned_to_id', $ticket?->assigned_to_id ?? ($isStaff ? auth()->id() : ''));
    $selCatId = old('ticket_category_id', $ticket?->ticket_category_id);
    $selPriorityId = old('priority_id', $ticket?->priority_id ?? ($priorities->where('name', 'Medium')->first()?->id ?? ''));
    $selStatusId = old('status_id', $ticket?->status_id ?? ($ticketStatuses->where('name', 'New')->first()?->id ?? ''));
    $selLabelId = old('label_id', $ticket?->label_id);
    $selSourceId = old('source_id', $ticket?->source_id ?? ($sources->first()?->id ?? ''));
    $initialRequesterId = old('requester_user_id', $ticket?->requester_user_id ?? ($isStaff ? '' : auth()->id()));
@endphp

{{-- Main Form Column --}}
<div class="space-y-6" x-data="{
    selectedCategory: '{{ old('ticket_category_id', $ticket?->ticket_category_id) }}',
    statusId: '{{ old('status_id', $ticket?->status_id ?? ($ticketStatuses->where('name', 'New')->first()?->id ?? '')) }}',
    closedIds: {{ json_encode($closedStatusIds) }},
    completedStatusId: '{{ $completedStatusId }}',
    completedDate: '{{ old('completed_date', $ticket?->completed_date?->format('Y-m-d')) }}',
    completedTime: '{{ old('completed_time', $ticket?->completed_time?->format('H:i')) }}',
    allAssets: {{ json_encode($allAssetItems) }},
    currentRequesterId: '{{ $initialRequesterId }}',
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
@select-changed.window="if ($event.detail.name === 'requester_user_id') onRequesterChange($event.detail.id); if ($event.detail.name === 'asset_id') currentAssetId = $event.detail.id;">

    {{-- Card 1: Ticket Classification & Requester Assignment --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    <i data-lucide="life-buoy" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Ticket Routing & Classification</h3>
                    <p class="text-[11px] text-gray-400">Requester identity, issue categorization, and priority assignment</p>
                </div>
            </div>
        </div>

        @if($isStaff)
            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">
                        Ticket Date & Time <span class="text-[11px] text-gray-400 font-normal">(Backdate supported)</span>
                    </label>
                    <input type="datetime-local" name="created_at"
                        value="{{ old('created_at', isset($ticket) ? ($ticket->created_at ? $ticket->created_at->format('Y-m-d\TH:i') : '') : date('Y-m-d\TH:i')) }}"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                    @error('created_at') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-form.searchable-select name="ticket_type_id" label="Ticket Type" :options="$typeOpts" :selected="$selTypeId" placeholder="Select Type" />
                </div>

                <div>
                    <x-form.searchable-select name="requester_user_id" label="Requester User" :options="$userOpts" :selected="$selRequesterId" placeholder="Select User" required="true" />
                </div>

                <div>
                    <x-form.searchable-select name="assigned_to_id" label="Assigned Technician" :options="$staffOpts" :selected="$selAssignedId" placeholder="Unassigned" />
                </div>

                <div>
                    <x-form.searchable-select name="ticket_category_id" label="ITSM Category" :options="$catOpts" :selected="$selCatId" placeholder="Select Category" />
                </div>

                <div>
                    <x-form.searchable-select name="priority_id" label="Priority" :options="$priorityOpts" :selected="$selPriorityId" placeholder="Select Priority" />
                </div>

                <div>
                    <x-form.searchable-select name="status_id" label="Status" :options="$statusOpts" :selected="$selStatusId" placeholder="Select Status" />
                </div>

                <div>
                    <x-form.searchable-select name="label_id" label="Ticket Label" :options="$labelOpts" :selected="$selLabelId" placeholder="Select Label" />
                </div>

                <div>
                    <x-form.searchable-select name="source_id" label="Intake Source" :options="$sourceOpts" :selected="$selSourceId" placeholder="Select Source" />
                </div>

                <div>
                    <x-form.searchable-select
                        name="sla_status_id"
                        label="SLA Target Rule"
                        :options="[['id' => '', 'text' => 'Auto (Computed from Policy)'], ...$slaStatuses->map(fn($sla) => ['id' => $sla->id, 'text' => $sla->name])->toArray()]"
                        :selected="old('sla_status_id', $ticket?->sla_status_id)"
                        placeholder="Auto (Computed from Policy)"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-form.searchable-select name="asset_id" label="Linked IT Asset (ITAM Device)" :options="[['id' => '', 'text' => 'No Asset Linked'], ...$allAssetItems]" :selected="old('asset_id', $ticket?->asset_id)" placeholder="No Asset Linked" />
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <input type="hidden" name="requester_user_id" value="{{ auth()->id() }}">

                <div class="sm:col-span-2 rounded-xl border border-brand-200/60 bg-brand-50/50 p-3.5 dark:border-brand-900/30 dark:bg-brand-950/20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500 text-white font-bold text-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ auth()->user()->email }} • {{ auth()->user()->department?->name ?? 'General Department' }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                        Helpdesk Requester
                    </span>
                </div>

                <div>
                    <x-form.searchable-select name="ticket_category_id" label="Issue Category" :options="$catOpts" :selected="$selCatId" placeholder="Select Issue Category" />
                </div>

                <div>
                    <x-form.searchable-select name="priority_id" label="Urgency Level" :options="$priorityOpts" :selected="$selPriorityId" placeholder="Select Priority" />
                </div>

                <div class="sm:col-span-2">
                    <x-form.searchable-select name="asset_id" label="Affected Hardware Device (Optional)" :options="[['id' => '', 'text' => 'No Asset Linked / Not Device Specific'], ...$allAssetItems]" :selected="old('asset_id', $ticket?->asset_id)" placeholder="Select Device" />
                </div>
            </div>
        @endif
    </div>

    {{-- Card 2: Subject & Detailed Problem Description --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Incident Details & Evidence</h3>
                    <p class="text-[11px] text-gray-400">Provide clear problem symptoms, error messages, and attachments</p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">
                    Subject / Ticket Title <span class="text-error-500">*</span>
                </label>
                <input type="text" name="subject" value="{{ old('subject', $ticket?->subject) }}" required
                    placeholder="e.g. Laptop Bluescreen after Windows Update, Wi-Fi Printer offline"
                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                @error('subject') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">
                    Problem Description <span class="text-error-500">*</span>
                </label>
                <textarea name="problem_description" rows="4" required
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition"
                    placeholder="Describe the incident, issue symptoms, error messages, and troubleshooting steps...">{{ old('problem_description', $ticket?->problem_description) }}</textarea>
                @error('problem_description') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">
                    Attachment / Screenshot (Max 10MB)
                </label>
                <input type="file" name="attachment"
                    class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950/30 dark:file:text-brand-400" />
            </div>
        </div>
    </div>

    {{-- Card 3: Internal & Resolution Notes (Staff Only) --}}
    @if($isStaff)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Resolution & Workload Logs (Staff Only)</h3>
                        <p class="text-[11px] text-gray-400">Completion timestamp, technical diagnostics, and labor hours</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">
                        Completed Date
                        <button type="button" @click="setToday()" class="ml-1 text-[11px] font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 focus:outline-none">
                            [Today]
                        </button>
                    </label>
                    <input type="date" name="completed_date" x-model="completedDate" @input="onDateChange()"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">Completed Time</label>
                    <input type="time" name="completed_time" x-model="completedTime"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">Estimated Hours</label>
                    <input type="number" name="estimated_hours" value="{{ old('estimated_hours', $ticket?->estimated_hours ?? 0) }}" step="0.5" min="0"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">Actual Hours Spent</label>
                    <input type="number" name="actual_hours" value="{{ old('actual_hours', $ticket?->actual_hours ?? 0) }}" step="0.5" min="0"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">Internal IT Notes</label>
                    <textarea name="internal_notes" rows="3" placeholder="Notes for IT team internal reference only..."
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('internal_notes', $ticket?->internal_notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">Public Resolution Summary</label>
                    <textarea name="resolution_notes" rows="3" placeholder="Troubleshooting outcome visible to requester upon resolution..."
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('resolution_notes', $ticket?->resolution_notes) }}</textarea>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Right Sidebar: Actions & Ticket Status --}}
<div class="space-y-6 xl:sticky xl:top-20">
    {{-- Action Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4 text-brand-500"></i>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Actions</h3>
            </div>
            <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                {{ $ticket ? 'Editing #' . $ticket->request_id : 'New Ticket' }}
            </span>
        </div>

        <div class="space-y-2.5">
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                <i data-lucide="check" class="w-4 h-4"></i>
                {{ $ticket ? 'Update Ticket' : 'Submit Ticket' }}
            </button>

            @if(!$ticket)
                <button type="submit" name="create_another" value="1" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-500/30 bg-brand-50/50 px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-100/60 active:scale-[0.98] dark:bg-brand-950/20 dark:text-brand-400 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Submit & Add Another
                </button>
            @endif

            <a href="{{ route('tickets.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                Cancel
            </a>
        </div>
    </div>
</div>
