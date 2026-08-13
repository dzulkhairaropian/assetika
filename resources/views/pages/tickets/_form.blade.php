<div class="space-y-6" x-data="{
    selectedCategory: '{{ old('ticket_category_id', $ticket?->ticket_category_id) }}',
    selectedSubcategory: '{{ old('ticket_subcategory_id', $ticket?->ticket_subcategory_id) }}',
    categories: {{ json_encode($ticketCategories) }},
    get subcategories() {
        if (!this.selectedCategory) return [];
        const cat = this.categories.find(c => c.id == this.selectedCategory);
        return cat ? cat.subcategories : [];
    }
}">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                <i data-feather="life-buoy" class="w-5 h-5 text-brand-500"></i>
                Ticket Information
            </h3>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Ticket Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ticket Type</label>
                    <select name="ticket_type_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Type</option>
                        @foreach($ticketTypes as $type)
                            <option value="{{ $type->id }}" {{ old('ticket_type_id', $ticket?->ticket_type_id ?? ($ticketTypes->where('name', 'Incident')->first()?->id ?? '')) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Requester -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Requester User <span class="text-error-500">*</span></label>
                    <select name="requester_user_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('requester_user_id', $ticket?->requester_user_id ?? auth()->id()) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Assigned To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned Staff</label>
                    <select name="assigned_to_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to_id', $ticket?->assigned_to_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- ITSM Ticket Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ITSM Category</label>
                    <select name="ticket_category_id" x-model="selectedCategory" @change="selectedSubcategory = ''" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Category</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name" :selected="cat.id == selectedCategory"></option>
                        </template>
                    </select>
                </div>

                <!-- ITSM Ticket Subcategory -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ITSM Subcategory</label>
                    <select name="ticket_subcategory_id" x-model="selectedSubcategory" :disabled="!selectedCategory" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Subcategory</option>
                        <template x-for="sub in subcategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name" :selected="sub.id == selectedSubcategory"></option>
                        </template>
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
                    <select name="priority_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Priority</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ old('priority_id', $ticket?->priority_id ?? ($priorities->where('name', 'Medium')->first()?->id ?? '')) == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Status</option>
                        @foreach($ticketStatuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id', $ticket?->status_id ?? ($ticketStatuses->where('name', 'New')->first()?->id ?? '')) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Label -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Label</label>
                    <select name="label_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Label</option>
                        @foreach($labels as $label)
                            <option value="{{ $label->id }}" {{ old('label_id', $ticket?->label_id) == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Source -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Source</label>
                    <select name="source_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Source</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}" {{ old('source_id', $ticket?->source_id ?? ($sources->where('name', 'Portal')->first()?->id ?? '')) == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- SLA Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SLA Status</label>
                    <select name="sla_status_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select SLA Status</option>
                        @foreach($slaStatuses as $slaStatus)
                            <option value="{{ $slaStatus->id }}" {{ old('sla_status_id', $ticket?->sla_status_id ?? ($slaStatuses->where('name', 'Fulfilled')->first()?->id ?? '')) == $slaStatus->id ? 'selected' : '' }}>{{ $slaStatus->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Linked IT Asset -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Linked Asset (ITAM)</label>
                    <select name="asset_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">No Asset Linked</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}" {{ old('asset_id', $ticket?->asset_id) == $asset->id ? 'selected' : '' }}>[{{ $asset->asset_tag }}] {{ $asset->device_name ?? $asset->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Problem Description -->
        <div class="pt-2">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                <i data-feather="file-text" class="w-5 h-5 text-brand-500"></i>
                Details & Description
            </h3>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Problem Description <span class="text-error-500">*</span></label>
                    <textarea name="problem_description" rows="4" required class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('problem_description', $ticket?->problem_description) }}</textarea>
                </div>

                <!-- Attachment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attachment / Screenshot (Max 10MB)</label>
                    <input type="file" name="attachment" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300"/>
                </div>
            </div>
        </div>

        <!-- Internal & Resolution Notes for Edit -->
        @if($ticket)
            <div class="pt-2">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <i data-feather="check-square" class="w-5 h-5 text-brand-500"></i>
                    Notes & Resolution
                </h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Internal Notes</label>
                        <textarea name="internal_notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent p-3 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('internal_notes', $ticket->internal_notes) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Resolution Notes</label>
                        <textarea name="resolution_notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent p-3 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                    </div>
                </div>
            </div>
        @endif

        <!-- Hours -->
        <div class="pt-2">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                <i data-feather="clock" class="w-5 h-5 text-brand-500"></i>
                Hours
            </h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Estimated Hours -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estimated Hours</label>
                    <input type="number" name="estimated_hours" value="{{ old('estimated_hours', $ticket?->estimated_hours ?? 0) }}" step="0.5" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white @error('estimated_hours') border-red-500 @enderror" />
                    @error('estimated_hours')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actual Hours -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Actual Hours</label>
                    <input type="number" name="actual_hours" value="{{ old('actual_hours', $ticket?->actual_hours ?? 0) }}" step="0.5" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white @error('actual_hours') border-red-500 @enderror" />
                    @error('actual_hours')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white/90">
            <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
            Actions
        </h3>
        <div class="space-y-3">
            <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                {{ $ticket ? 'Update Ticket' : 'Save Ticket' }}
            </button>
            <a href="{{ route('tickets.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                Cancel
            </a>
        </div>
    </div>
</div>