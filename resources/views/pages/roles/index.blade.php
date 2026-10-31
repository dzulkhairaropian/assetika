@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Role Management" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage system access control roles, assigned members, and capability permissions.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Main Table Container (Exact Asset Index Layout) --}}
        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('roles.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @if(request()->filled('search'))
                            <a href="{{ route('roles.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('roles.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Role
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Role Name</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Total Users</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Permissions</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-4 py-2.5 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                    {{ $role->name }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-[10px] font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                                        {{ number_format($role->users_count ?? 0) }} members
                                    </span>
                                </td>
                                <td class="px-4 py-2.5" x-data="{ expanded: false }">
                                    <div class="flex flex-wrap gap-1 max-w-md items-center">
                                        @foreach($role->permissions->take(4) as $permission)
                                            <span class="inline-flex items-center rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                        
                                        @if($role->permissions->count() > 4)
                                            <span x-show="expanded" class="flex flex-wrap gap-1">
                                                @foreach($role->permissions->slice(4) as $permission)
                                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        {{ $permission->name }}
                                                    </span>
                                                @endforeach
                                            </span>
                                            
                                            <button @click="expanded = !expanded" 
                                                    class="inline-flex items-center justify-center rounded-md bg-brand-50 hover:bg-brand-100 px-1.5 py-0.5 text-[10px] font-bold text-brand-700 transition dark:bg-brand-500/10 dark:text-brand-400">
                                                <span x-text="expanded ? 'Less' : '+{{ $role->permissions->count() - 4 }}'"></span>
                                            </button>
                                        @endif
                                        
                                        @if($role->permissions->isEmpty())
                                            <span class="text-xs text-gray-400 italic">No permissions assigned</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('roles.edit', $role) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="Edit Role">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </a>
                                        @if(!in_array($role->name, ['Super Admin', 'Admin']))
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?')" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 shadow-xs hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-400 transition"
                                                    title="Delete Role">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <i data-lucide="shield" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No Roles Found</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        No access control roles found matching your search.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Per page:</span>
                    <select onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();"
                        class="h-8 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="flex-1 max-w-full overflow-x-auto flex justify-end">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
