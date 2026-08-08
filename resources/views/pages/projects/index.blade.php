@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false }">
    <x-common.page-breadcrumb pageTitle="Project Management" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">IT Projects & Operations</h2>
            <p class="text-xs text-gray-500">Track IT rollouts, infrastructure upgrades, and operational tasks.</p>
        </div>
        <button @click="openCreateModal = true" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-center text-theme-sm font-medium text-white hover:bg-brand-600">
            <i data-feather="plus" class="w-4 h-4"></i> New Project
        </button>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl border border-success-200 bg-success-50 text-success-700 dark:border-success-900/30 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <form method="GET" class="mb-5 flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search project name or code..." class="h-11 w-full sm:w-80 rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            <select name="status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">All Statuses</option>
                <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 text-theme-sm font-medium text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">Filter</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($projects as $project)
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900/50 hover:shadow-md transition space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400">{{ $project->code }}</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase
                                {{ $project->status === 'active' ? 'bg-success-50 text-success-600 dark:bg-success-500/10' : '' }}
                                {{ $project->status === 'planning' ? 'bg-info-50 text-info-600 dark:bg-info-500/10' : '' }}
                                {{ $project->status === 'completed' ? 'bg-gray-100 text-gray-700 dark:bg-gray-800' : '' }}
                                {{ $project->status === 'on_hold' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/10' : '' }}
                            ">
                                {{ str_replace('_', ' ', $project->status) }}
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-800 dark:text-white leading-snug">
                            <a href="{{ route('projects.show', $project) }}" class="hover:underline">{{ $project->name }}</a>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $project->description ?? 'No description.' }}</p>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-gray-800/80">
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500">Progress</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $project->progress_percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-brand-500 h-2 rounded-full" style="width: {{ $project->progress_percentage }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Owner: <strong class="text-gray-700 dark:text-gray-300">{{ $project->owner?->name ?? '-' }}</strong></span>
                            <span>Tasks: <strong class="text-gray-700 dark:text-gray-300">{{ $project->tasks->count() }}</strong></span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-gray-500 dark:text-gray-400">
                    No Projects found. Click "New Project" to get started.
                </div>
            @endforelse
        </div>

        @if($projects->hasPages())
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create Project -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Create New Project</h3>
            <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Project Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Server Migration 2026" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"/>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Owner / Project Manager</label>
                    <select name="owner_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Select Owner</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="planning">Planning</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                        <select name="priority" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent p-3 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openCreateModal = false" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2 text-xs font-medium text-white hover:bg-brand-600">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
