@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ openCreateModal: false }">
    <x-common.page-breadcrumb pageTitle="Projects" />
    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Manage IT projects, operations, infrastructure rollouts, and task progress.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    <!-- Unified Table Card -->
    <div class="overflow-hidden xl:overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <!-- Card Header Toolbar -->
        <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('projects.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <div class="relative flex items-center">
                        <span class="absolute left-2.5 text-gray-400">
                            <i data-feather="search" class="w-3.5 h-3.5"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search projects..."
                            class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                    </div>

                    <div class="w-40">
                        <select name="status" onchange="this.form.submit()"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                            <option value="">All Statuses</option>
                            @foreach($projectStatuses as $st)
                                <option value="{{ $st->id }}" {{ request('status') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($search || request('status'))
                        <a href="{{ route('projects.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">Clear</a>
                    @endif
                </form>

                <button type="button" @click="openCreateModal = true" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                    <i data-feather="plus" class="w-3.5 h-3.5"></i> Create Project
                </button>
            </div>
        </div>

        <!-- Standard Table View (Matching Asset Index Specs) -->
        <div class="max-w-full overflow-x-auto xl:overflow-visible">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Code</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Project Name</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Owner</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Priority</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Progress</th>
                        <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($projects as $project)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5">
                                <span class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10 px-2 py-0.5 rounded">{{ $project->code }}</span>
                            </td>
                            <td class="px-4 py-2.5">
                                <a href="{{ route('projects.show', $project) }}" class="font-medium text-gray-900 dark:text-white hover:text-brand-600 transition">
                                    {{ $project->name }}
                                </a>
                                @if($project->description)
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 line-clamp-1 max-w-md">{{ $project->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $project->owner?->name ?: '-' }}
                                    </span>
                                    @if($project->owner && $project->owner->department)
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                            {{ $project->owner->department->name }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $stRel = $project->statusRel;
                                    $stName = $stRel ? $stRel->name : ucfirst(str_replace('_', ' ', $project->status));
                                    $stColor = $stRel ? $stRel->color : 'info';
                                    $stIcon = $stRel ? $stRel->icon : 'clock';
                                @endphp
                                <x-ui.badge :color="$stColor" variant="light" size="sm" :featherIcon="$stIcon">
                                    {{ $stName }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $prColor = match($project->priority) {
                                        'low' => 'info',
                                        'medium' => 'warning',
                                        'high' => 'error',
                                        'urgent' => 'error',
                                        default => 'secondary'
                                    };
                                    $prIcon = match($project->priority) {
                                        'urgent' => 'alert-triangle',
                                        default => 'flag'
                                    };
                                @endphp
                                <x-ui.badge :color="$prColor" variant="light" size="sm" :featherIcon="$prIcon">
                                    {{ ucfirst($project->priority ?: 'medium') }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5 w-48">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[11px] font-medium">
                                        <span class="text-gray-500 dark:text-gray-400">{{ $project->tasks->count() }} Tasks</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $project->progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-brand-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $project->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-end">
                                <div x-data="{ open: false }" class="relative inline-block text-left" @click.away="open = false">
                                    <button type="button" @click="open = !open" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                        <i data-feather="more-vertical" class="w-4 h-4"></i>
                                    </button>

                                    <div x-show="open"
                                        x-transition
                                        class="absolute right-0 top-full mt-1 z-50 w-36 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                        style="display: none;">
                                        
                                        <a href="{{ route('projects.show', $project) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                            <i data-feather="columns" class="w-3.5 h-3.5 text-gray-400"></i>
                                            View Board
                                        </a>

                                        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                <i data-feather="trash-2" class="w-3.5 h-3.5"></i>
                                                Delete Project
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-1.5">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="inbox" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No projects found</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Try adjusting your search or create a new project.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($projects->hasPages())
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create Project -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Create New Project</h3>
                <button type="button" @click="openCreateModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i data-feather="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" class="space-y-3.5 text-left">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Project Name <span class="text-error-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Infrastructure Rollout 2026"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200"/>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Project Manager / Owner</label>
                    <select name="owner_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                        <option value="">Select Owner</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                        <select name="status_id" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                            @foreach($projectStatuses as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Priority</label>
                        <select name="priority" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="openCreateModal = false" class="rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
