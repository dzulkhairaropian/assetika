@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Role Management" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('roles.index') }}" method="GET" class="flex items-center gap-3">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                </div>
                @if(request()->filled('search'))
                    <a href="{{ route('roles.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                @endif
            </form>

            <a href="{{ route('roles.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-feather="shield" class="w-4 h-4"></i>
                Add Role
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
                        this.selectedRows = {{ json_encode($roles->pluck('id')->toArray()) }}; 
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
                    this.selectAll = this.selectedRows.length === {{ $roles->count() }};
                } 
            }">
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
                                    <span>Role Name</span>
                                </div>
                            </th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Total Users</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Permissions</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($roles as $role)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div @click="handleRowSelect({{ $role->id }})"
                                            class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                            :class="selectedRows.includes({{ $role->id }}) ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                            <svg :class="selectedRows.includes({{ $role->id }}) ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $role->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                    {{ number_format($role->users_count ?? 0) }} users
                                </td>
                                <td class="px-4 sm:px-6 py-3.5" x-data="{ expanded: false }">
                                    <div class="flex flex-wrap gap-1 max-w-md items-center">
                                        @foreach($role->permissions->take(5) as $permission)
                                            <x-ui.badge color="light" variant="light" size="sm">{{ $permission->name }}</x-ui.badge>
                                        @endforeach
                                        
                                        @if($role->permissions->count() > 5)
                                            <span x-show="expanded" class="flex flex-wrap gap-1">
                                                @foreach($role->permissions->slice(5) as $permission)
                                                    <x-ui.badge color="light" variant="light" size="sm">{{ $permission->name }}</x-ui.badge>
                                                @endforeach
                                            </span>
                                            
                                            <button @click="expanded = !expanded" 
                                                    class="inline-flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-650 transition dark:bg-gray-800 dark:hover:bg-gray-750 dark:text-gray-400">
                                                <span x-text="expanded ? 'Show Less' : '+ {{ $role->permissions->count() - 5 }} more'"></span>
                                            </button>
                                        @endif
                                        
                                        @if($role->permissions->isEmpty())
                                            <span class="text-xs text-gray-400 italic">No permissions assigned</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route('roles.edit', $role) }}" class="hover:text-brand-500 transition-colors">
                                            <i data-feather="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        @if($role->name !== 'super_admin')
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="hover:text-error-500 transition-colors">
                                                    <i data-feather="trash-2" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No roles found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Configure roles and permissions to secure application features.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
