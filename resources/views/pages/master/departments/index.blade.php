@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Departments" />

        <!-- Stat Card Header -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <i data-feather="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Total Departments</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $departments->total() }}</h4>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10">
                        <i data-feather="grid" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Company Filter</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ request('company_id') ? 'Filtered' : 'All Companies' }}</h4>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-500 dark:bg-purple-500/10">
                        <i data-feather="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Structure</span>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">Org Units</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('departments.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search departments..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                </div>

                <select name="company_id" onchange="this.form.submit()" 
                    class="h-10 rounded-lg border border-gray-300 bg-white pl-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>

                @if(request()->anyFilled(['search', 'company_id']))
                    <a href="{{ route('departments.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                @endif
            </form>

            <a href="{{ route('departments.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-feather="plus" class="w-4 h-4"></i>
                Add Department
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            x-data="{ 
                selectedRows: [], 
                selectAll: false, 
                handleSelectAll() { 
                    this.selectAll = !this.selectAll; 
                    if (this.selectAll) { 
                        this.selectedRows = {{ json_encode($departments->pluck('id')->toArray()) }}; 
                    } else { 
                        this.selectedRows = []; 
                    } 
                }, 
                handleRowSelect(id) { 
                    if (this.selectedRows.includes(id)) { 
                        this.selectedRows = this.selectedRows.filter(rowId => rowId !== id); 
                    } else { 
                        this.selectedRows.push(id); 
                    } 
                    this.selectAll = this.selectedRows.length === {{ $departments->count() }};
                } 
            }">
        <!-- Bulk Action Form -->
        <form id="bulkDeleteForm" action="{{ route('departments.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="id in selectedRows" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedRows.length > 0" 
            x-transition
            class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10 m-4"
            style="display: none;">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-brand-700 dark:text-brand-400">
                    <span x-text="selectedRows.length"></span> items selected.
                </span>
            </div>
            <div class="flex items-center gap-4">
                <button @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }" 
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Delete Selected
                </button>
            </div>
        </div>

            <div class="max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                                <div class="flex items-center gap-3">
                                    <div @click="handleSelectAll()"
                                        class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                        :class="selectAll ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                        <svg :class="selectAll ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <span>Code</span>
                                </div>
                            </th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Department Name</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Company</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @php
                            $avatarColors = [
                                ['bg-blue-100', 'text-blue-500'],
                                ['bg-pink-100', 'text-pink-500'],
                                ['bg-cyan-100', 'text-cyan-500'],
                                ['bg-orange-100', 'text-orange-500'],
                                ['bg-green-100', 'text-green-500'],
                                ['bg-purple-100', 'text-purple-500'],
                                ['bg-yellow-100', 'text-yellow-500'],
                            ];
                        @endphp
                        @forelse($departments as $department)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div @click="handleRowSelect({{ $department->id }})"
                                            class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                            :class="selectedRows.includes({{ $department->id }}) ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                            <svg :class="selectedRows.includes({{ $department->id }}) ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $department->code }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $color = $avatarColors[$loop->index % count($avatarColors)];
                                            $initials = collect(explode(' ', $department->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('');
                                        @endphp
                                        <div class="flex items-center justify-center w-10 h-10 rounded-full font-medium text-sm {{ $color[0] }} {{ $color[1] }}">
                                            <span>{{ $initials }}</span>
                                        </div>
                                        <div>
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $department->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $department->company->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route('departments.edit', $department) }}" class="hover:text-brand-500 transition-colors">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                        <form action="{{ route('departments.destroy', $department) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-error-500 transition-colors">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No departments found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Create departments to group your employees and assets.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-6 dark:border-gray-800">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ $departments->firstItem() ?? 0 }} to {{ $departments->lastItem() ?? 0 }} of {{ $departments->total() }} results
                    </p>
                    {{ $departments->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

