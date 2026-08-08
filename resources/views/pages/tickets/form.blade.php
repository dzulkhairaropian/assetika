@extends('layouts.app')

@section('content')
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
    <x-common.page-breadcrumb pageTitle="{{ $ticket ? 'Edit Ticket #' . $ticket->request_id : 'Create New Ticket' }}" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <form action="{{ $ticket ? route('tickets.update', $ticket) : route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($ticket)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Requester -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Requester User *</label>
                    <select name="requester_user_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('requester_user_id', $ticket?->requester_user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
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
                            <option value="{{ $priority->id }}" {{ old('priority_id', $ticket?->priority_id) == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Status</option>
                        @foreach($ticketStatuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id', $ticket?->status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
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

            <!-- Problem Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Problem Description *</label>
                <textarea name="problem_description" rows="4" required class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('problem_description', $ticket?->problem_description) }}</textarea>
            </div>

            <!-- Attachment -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attachment / Screenshot (Max 10MB)</label>
                <input type="file" name="attachment" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300"/>
            </div>

            <!-- Internal & Resolution Notes for Edit -->
            @if($ticket)
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
            @endif

            <div class="flex justify-end gap-3">
                <a href="{{ route('tickets.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection
