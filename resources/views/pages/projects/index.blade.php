@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{
    openCreateModal: false,
    selectedProjects: [],
    selectAll: false,
    toggleAll() {
        this.selectAll = !this.selectAll;
        if (this.selectAll) {
            this.selectedProjects = Array.from(document.querySelectorAll('.project-checkbox')).map(el => parseInt(el.value));
        } else {
            this.selectedProjects = [];
        }
    },
    toggleProject(id) {
        const index = this.selectedProjects.indexOf(id);
        if (index > -1) {
            this.selectedProjects.splice(index, 1);
        } else {
            this.selectedProjects.push(id);
        }
        this.selectAll = this.selectedProjects.length === document.querySelectorAll('.project-checkbox').length;
    }
}">
    <x-common.page-breadcrumb pageTitle="Projects" />
    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Manage IT projects, operations, infrastructure rollouts, and task progress.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    <!-- Bulk Action Form -->
    <form id="bulkDeleteProjectsForm" action="{{ route('projects.bulk-delete') }}" method="POST" class="hidden">
        @csrf
        <template x-for="id in selectedProjects" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
    </form>

    <!-- Bulk Action Bar -->
    <div x-show="selectedProjects.length > 0"
        x-transition
        class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/50 p-3 dark:border-brand-900/30 dark:bg-brand-500/10"
        style="display: none;">
        <div class="flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
            <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                <span x-text="selectedProjects.length"></span> projects selected.
            </span>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <button type="button" @click="if (confirm('Are you sure you want to delete selected projects?')) { document.getElementById('bulkDeleteProjectsForm').submit(); }"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                Delete Selected
            </button>
        </div>
    </div>

    <!-- Unified Table Card -->
    <div class="overflow-hidden xl:overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <!-- Card Header Toolbar -->
        <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
            @php
                $currentTab = request('tab');
                $tabFilters = array_filter(request()->except(['page', 'tab']));
                $tabs = [
                    ['key' => null,     'label' => 'All Projects', 'count' => $summary['total'] ?? 0, 'icon' => 'layers',   'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                    ['key' => 'today',  'label' => 'Added Today',  'count' => $summary['today'] ?? 0, 'icon' => 'calendar', 'badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'],
                ];
            @endphp
            <nav class="flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-track]:bg-white dark:[&::-webkit-scrollbar-track]:bg-transparent">
                @foreach($tabs as $tab)
                    @php
                        $isActive = $currentTab === $tab['key'];
                        $tabUrl = route('projects.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                    @endphp
                    <a href="{{ $tabUrl }}"
                       class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-colors duration-200 ease-in-out {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-white/5 dark:text-white font-semibold' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                        <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                        {{ $tab['label'] }}
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('projects.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    @if(request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                    @endif
                    <div class="relative flex items-center">
                        <span class="absolute left-2.5 text-gray-400">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
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
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Create Project
                </button>
            </div>
        </div>

        <!-- Standard Table View (Matching Asset Index & AdminLTE Projects Specs) -->
        <div class="max-w-full overflow-x-auto xl:overflow-visible">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400 w-10">
                            <label class="flex cursor-pointer items-center select-none">
                                <div class="relative">
                                    <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                    <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                        class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                        <span :class="selectAll ? '' : 'opacity-0'">
                                            <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Project</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Progress</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Team</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Budget</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Due Date</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Priority</th>
                        <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($projects as $project)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5">
                                <label class="flex cursor-pointer items-center select-none">
                                    <div class="relative">
                                        <input type="checkbox" value="{{ $project->id }}" class="project-checkbox sr-only"
                                            :checked="selectedProjects.includes({{ $project->id }})" @click="toggleProject({{ $project->id }})">
                                        <div :class="selectedProjects.includes({{ $project->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                            class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                            <span :class="selectedProjects.includes({{ $project->id }}) ? '' : 'opacity-0'">
                                                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <a href="{{ route('projects.show', $project) }}" class="font-semibold text-gray-900 hover:text-brand-600 dark:text-white transition">
                                        {{ $project->name }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="font-mono text-[10px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10 px-1.5 py-0.2 rounded">{{ $project->code }}</span>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">• {{ $project->owner?->name ?? 'Unassigned' }}</span>
                                    </div>
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
                            <td class="px-4 py-2.5 w-44">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-brand-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $project->progress_percentage }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 w-9 text-end">{{ $project->progress_percentage }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $teamMembers = $project->tasks->pluck('assignedTo')->filter()->unique('id')->take(4);
                                @endphp
                                <div class="flex items-center -space-x-1.5">
                                    @forelse($teamMembers as $member)
                                        @php
                                            $initials = strtoupper(substr($member->name, 0, 2));
                                        @endphp
                                        <span title="{{ $member->name }}" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 text-[10px] font-bold text-brand-600 ring-2 ring-white dark:bg-brand-500/20 dark:text-brand-300 dark:ring-gray-900">
                                            {{ $initials }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-gray-400 italic">No team</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $project->budget ? 'Rp ' . number_format($project->budget, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                {{ $project->due_date ? $project->due_date->format('d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $prColor = match(strtolower($project->priority)) {
                                        'low' => 'info',
                                        'medium' => 'warning',
                                        'high' => 'danger',
                                        'urgent' => 'danger',
                                        default => 'secondary'
                                    };
                                    $prIcon = match(strtolower($project->priority)) {
                                        'urgent' => 'alert-triangle',
                                        default => 'flag'
                                    };
                                @endphp
                                <x-ui.badge :color="$prColor" variant="light" size="sm" :featherIcon="$prIcon">
                                    {{ ucfirst($project->priority ?: 'medium') }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5 text-end">
                                <div x-data="{
                                    open: false,
                                    dropdownStyle: '',
                                    toggle(e) {
                                        this.open = !this.open;
                                        if (this.open) {
                                            const rect = e.currentTarget.getBoundingClientRect();
                                            const menuHeight = 100;
                                            const spaceBelow = window.innerHeight - rect.bottom;
                                            const top = spaceBelow < menuHeight ? Math.max(10, rect.top - menuHeight) : (rect.bottom + 4);
                                            const left = Math.max(10, rect.right - 144);
                                            this.dropdownStyle = `position: fixed; top: ${top}px; left: ${left}px; z-index: 9999; display: block;`;
                                        }
                                    }
                                }" @click.away="open = false" @scroll.window="open = false" class="inline-block text-left">
                                    <button type="button" @click="toggle($event)" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                    </button>

                                    <div x-show="open"
                                        x-transition
                                        :style="open ? dropdownStyle : 'display: none;'"
                                        class="w-36 rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                        style="display: none;">
                                        
                                        <a href="{{ route('projects.show', $project) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                            <i data-lucide="columns" class="w-3.5 h-3.5 text-gray-400"></i>
                                            View Board
                                        </a>

                                        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                Delete Project
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-1.5">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-lucide="inbox" class="w-5 h-5"></i>
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

        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Per page:</span>
                <select onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();"
                    class="h-8 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                </select>
            </div>
            <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                {{ $projects->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Create Project -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Create New Project</h3>
                <button type="button" @click="openCreateModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i data-lucide="x" class="w-4 h-4"></i>
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
                    <x-form.searchable-select name="owner_id" label="Project Manager / Owner" :options="$userOpts" :selected="old('owner_id')" placeholder="Select Owner" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-form.searchable-select name="status_id" label="Status" :options="$statusOpts" :selected="old('status_id')" placeholder="Select Status" />
                    </div>
                    <div>
                        <x-form.searchable-select name="priority" label="Priority" :options="[['id' => 'low', 'text' => 'Low'], ['id' => 'medium', 'text' => 'Medium'], ['id' => 'high', 'text' => 'High'], ['id' => 'urgent', 'text' => 'Urgent']]" :selected="old('priority', 'medium')" placeholder="Select Priority" required="true" />
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
