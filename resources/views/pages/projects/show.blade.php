@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ openTaskModal: false }">
    <x-common.page-breadcrumb pageTitle="Project Detail" :items="[['label' => 'Projects', 'url' => route('projects.index')], ['label' => $project->code]]" />

    @if (session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    <!-- Project Overview Header -->
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5 space-y-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-[11px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10 px-2 py-0.5 rounded">{{ $project->code }}</span>
                    @php
                        $stRel = $project->statusRel;
                        $stName = $stRel ? $stRel->name : ucfirst(str_replace('_', ' ', $project->status));
                        $stColor = $stRel ? $stRel->color : 'info';
                        $stIcon = $stRel ? $stRel->icon : 'clock';
                    @endphp
                    <x-ui.badge :color="$stColor" variant="light" size="sm" :featherIcon="$stIcon">
                        {{ $stName }}
                    </x-ui.badge>
                    <x-ui.badge :color="$project->priority === 'urgent' ? 'danger' : ($project->priority === 'high' ? 'warning' : 'secondary')" variant="light" size="sm">
                        {{ ucfirst($project->priority) }} Priority
                    </x-ui.badge>
                </div>
                <h1 class="text-base font-semibold text-gray-900 dark:text-white">{{ $project->name }}</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-3xl leading-relaxed">{{ $project->description ?: 'No description provided.' }}</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="openTaskModal = true" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Add Task
                </button>
            </div>
        </div>

        <!-- Progress Summary Card Grid -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 pt-1">
            <!-- Box 1: Overall Progress -->
            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-gray-950/40 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <i data-lucide="bar-chart-2" class="w-3.5 h-3.5 text-brand-500"></i>
                        Overall Progress
                    </span>
                    <span class="font-mono font-bold text-xs text-brand-600 dark:text-brand-400" id="progressVal">{{ $project->progress_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                    <div id="progressBar" class="bg-brand-500 h-2 rounded-full transition-all duration-500" style="width: {{ $project->progress_percentage }}%"></div>
                </div>
            </div>

            <!-- Box 2: Manager & Tasks -->
            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-gray-950/40 flex items-center justify-between">
                <div class="space-y-0.5 text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Project Lead</span>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $project->owner?->name ?: 'Unassigned' }}</p>
                </div>
                <div class="text-right text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Tasks</span>
                    <p class="font-bold text-gray-800 dark:text-gray-200">{{ $project->tasks->count() }} Total</p>
                </div>
            </div>

            <!-- Box 3: Timeline / Target -->
            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-gray-950/40 flex items-center justify-between text-xs">
                <div class="space-y-0.5">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Start Date</span>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $project->start_date?->format('d M Y') ?: '-' }}</p>
                </div>
                <div class="text-right space-y-0.5">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Target Date</span>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $project->due_date?->format('d M Y') ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- KANBAN BOARD WITH DRAG & DROP -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white flex items-center gap-1.5">
                <i data-lucide="columns" class="w-4 h-4 text-brand-500"></i>
                Kanban Task Board
            </h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">Drag & drop tasks between columns to update status</span>
        </div>

        @php
            $columns = $taskStatuses->count() > 0 ? $taskStatuses : collect([
                (object)['id' => null, 'name' => 'To Do', 'color' => 'light', 'icon' => 'circle', 'slug' => 'todo'],
                (object)['id' => null, 'name' => 'In Progress', 'color' => 'primary', 'icon' => 'loader', 'slug' => 'in_progress'],
                (object)['id' => null, 'name' => 'Review', 'color' => 'warning', 'icon' => 'eye', 'slug' => 'review'],
                (object)['id' => null, 'name' => 'Done', 'color' => 'success', 'icon' => 'circle-check', 'slug' => 'done'],
            ]);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
            @foreach($columns as $col)
                @php
                    $colTasks = $project->tasks->filter(function($t) use ($col) {
                        if (isset($col->id) && $col->id) {
                            return $t->status_id == $col->id;
                        }
                        $slug = $col->slug ?? strtolower(str_replace(' ', '_', $col->name));
                        return $t->status === $slug || strtolower($t->statusRel?->name) === strtolower($col->name);
                    });
                @endphp
                <div class="kanban-column rounded-xl border border-gray-200 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-gray-950/40 space-y-3 min-h-[420px] transition-colors"
                     data-status-id="{{ $col->id }}"
                     ondragover="allowDrop(event)"
                     ondragleave="removeDragHover(event)"
                     ondrop="dropTask(event)">
                    
                    <!-- Column Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-800 pointer-events-none">
                        <div class="flex items-center gap-2">
                            <x-ui.badge :color="$col->color ?? 'info'" variant="light" size="sm" :featherIcon="$col->icon ?? 'circle'">
                                {{ $col->name }}
                            </x-ui.badge>
                            <span class="column-count text-[11px] font-bold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 px-2 py-0.5 rounded-full shadow-xs border border-gray-200 dark:border-gray-700">
                                {{ $colTasks->count() }}
                            </span>
                        </div>
                    </div>

                    <!-- Task Cards Container -->
                    <div class="task-list space-y-2">
                        @forelse($colTasks as $task)
                            <div class="task-card rounded-lg border border-gray-200 bg-white p-3 shadow-xs dark:border-gray-800 dark:bg-gray-900 hover:border-brand-300 dark:hover:border-brand-500/50 transition space-y-2 cursor-grab active:cursor-grabbing"
                                 draggable="true"
                                 data-task-id="{{ $task->id }}"
                                 ondragstart="dragTask(event)">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-xs font-semibold text-gray-800 dark:text-white leading-snug">{{ $task->title }}</h4>
                                    <form method="POST" action="{{ route('projects.destroy-task', $task) }}" onsubmit="return confirm('Delete task?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-error-500 transition">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>

                                @if($task->description)
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-2 leading-tight">{{ $task->description }}</p>
                                @endif

                                <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px] font-medium">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ $task->assignedTo?->name ?: 'Unassigned' }}
                                    </span>
                                    <span class="inline-block px-1.5 py-0.5 rounded font-semibold uppercase
                                        {{ $task->priority === 'urgent' ? 'bg-red-50 text-red-600 dark:bg-red-500/10' : '' }}
                                        {{ $task->priority === 'high' ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10' : '' }}
                                        {{ $task->priority === 'medium' ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/10' : '' }}
                                        {{ $task->priority === 'low' ? 'bg-gray-100 text-gray-600 dark:bg-gray-700' : '' }}
                                    ">
                                        {{ $task->priority }}
                                    </span>
                                </div>

                                @if($task->asset || $task->ticket)
                                    <div class="flex flex-wrap gap-1 text-[10px] pt-1">
                                        @if($task->asset)
                                            <span class="inline-flex items-center gap-1 rounded bg-purple-50 text-purple-700 px-1.5 py-0.5 dark:bg-purple-500/10 dark:text-purple-300">
                                                <i data-lucide="monitor" class="w-2.5 h-2.5"></i> {{ $task->asset->asset_tag }}
                                            </span>
                                        @endif
                                        @if($task->ticket)
                                            <span class="inline-flex items-center gap-1 rounded bg-emerald-50 text-emerald-700 px-1.5 py-0.5 dark:bg-emerald-500/10 dark:text-emerald-300">
                                                <i data-lucide="ticket" class="w-2.5 h-2.5"></i> #{{ $task->ticket->request_id }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="empty-state py-8 text-center text-[11px] text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-800 rounded-lg">
                                Empty {{ $col->name }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal Add Task -->
    <div x-show="openTaskModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Add Task to Project</h3>
                <button type="button" @click="openTaskModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('projects.store-task', $project) }}" method="POST" class="space-y-3.5 text-left">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Task Title <span class="text-error-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Install Operating System" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200"/>
                </div>

                <div>
                    <x-form.searchable-select name="assigned_to_id" label="Assigned To" :options="$userOpts" :selected="old('assigned_to_id')" placeholder="Unassigned" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-form.searchable-select name="status_id" label="Initial Status" :options="$statusOpts" :selected="old('status_id')" placeholder="Select Status" />
                    </div>
                    <div>
                        <x-form.searchable-select name="priority" label="Priority" :options="[['id' => 'low', 'text' => 'Low'], ['id' => 'medium', 'text' => 'Medium'], ['id' => 'high', 'text' => 'High'], ['id' => 'urgent', 'text' => 'Urgent']]" :selected="old('priority', 'medium')" placeholder="Select Priority" required="true" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-form.searchable-select name="asset_id" label="Link Asset (Optional)" :options="$assetOpts" :selected="old('asset_id')" placeholder="None" />
                    </div>
                    <div>
                        <x-form.searchable-select name="ticket_id" label="Link Ticket (Optional)" :options="$ticketOpts" :selected="old('ticket_id')" placeholder="None" />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Description</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="openTaskModal = false" class="rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function dragTask(ev) {
        ev.dataTransfer.setData("taskId", ev.target.getAttribute("data-task-id"));
        ev.target.classList.add('opacity-40');
    }

    function allowDrop(ev) {
        ev.preventDefault();
        const col = ev.currentTarget;
        col.classList.add('ring-2', 'ring-brand-500', 'bg-brand-50/20');
    }

    function removeDragHover(ev) {
        const col = ev.currentTarget;
        col.classList.remove('ring-2', 'ring-brand-500', 'bg-brand-50/20');
    }

    function dropTask(ev) {
        ev.preventDefault();
        const col = ev.currentTarget;
        col.classList.remove('ring-2', 'ring-brand-500', 'bg-brand-50/20');
        
        const taskId = ev.dataTransfer.getData("taskId");
        const statusId = col.getAttribute("data-status-id");
        const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
        
        if (!taskCard) return;
        taskCard.classList.remove('opacity-40');

        const taskList = col.querySelector('.task-list');
        const emptyState = col.querySelector('.empty-state');
        if (emptyState) emptyState.remove();

        taskList.appendChild(taskCard);

        // Update counts visually
        document.querySelectorAll('.kanban-column').forEach(c => {
            const countSpan = c.querySelector('.column-count');
            const count = c.querySelectorAll('.task-card').length;
            if (countSpan) countSpan.textContent = count;
        });

        // Send AJAX request to server
        fetch(`/project-tasks/${taskId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status_id: statusId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const pBar = document.getElementById('progressBar');
                const pVal = document.getElementById('progressVal');
                if (pBar) pBar.style.width = data.progress + '%';
                if (pVal) pVal.textContent = data.progress + '%';
            }
        })
        .catch(err => console.error('Failed to update task status:', err));
    }

    document.addEventListener('dragend', (ev) => {
        if (ev.target.classList.contains('task-card')) {
            ev.target.classList.remove('opacity-40');
        }
    });
</script>
@endsection
