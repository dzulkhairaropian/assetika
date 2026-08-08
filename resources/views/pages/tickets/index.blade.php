@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Support Tickets" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">IT Service Management (ITSM)</h2>
        <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-center text-theme-sm font-medium text-white hover:bg-brand-600">
            <i data-feather="plus" class="w-4 h-4"></i> Create Ticket
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl border border-success-200 bg-success-50 text-success-700 dark:border-success-900/30 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, requester, issue..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>
            <div>
                <select name="status_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <select name="priority_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 text-theme-sm font-medium text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">Filter</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <th class="py-3 px-4">Ticket Key</th>
                        <th class="py-3 px-4">Requester</th>
                        <th class="py-3 px-4">Summary</th>
                        <th class="py-3 px-4">Priority</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">SLA Risk</th>
                        <th class="py-3 px-4">Assignee</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    @forelse($tickets as $ticket)
                        @php
                            $isOverdue = !($ticket->status?->is_closed) && $ticket->created_at->diffInDays(now()) >= 3;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="py-3 px-4 font-mono font-medium text-brand-600 dark:text-brand-400">
                                <a href="{{ route('tickets.show', $ticket) }}">{{ $ticket->request_id }}</a>
                            </td>
                            <td class="py-3 px-4 font-medium text-gray-800 dark:text-white/90">
                                {{ $ticket->requester_name ?? $ticket->requesterUser?->name }}
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                {{ \Illuminate\Support\Str::limit($ticket->problem_description, 50) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $ticket->priority?->name ?? 'Normal' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                    {{ $ticket->status?->name ?? 'Open' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($isOverdue)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-[11px] font-semibold text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                        <i data-feather="alert-circle" class="w-3 h-3"></i> SLA Risk (>3d)
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">On Track</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">
                                {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="p-1.5 text-gray-500 hover:text-brand-500" title="View Ticket">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('tickets.edit', $ticket) }}" class="p-1.5 text-gray-500 hover:text-brand-500" title="Edit Ticket">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                No support tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
