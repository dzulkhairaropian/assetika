@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{
        selectedItems: [],
        selectAll: false,
        toggleAll() {
            this.selectAll = !this.selectAll;
            if (this.selectAll) {
                this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(el => Number(el.value));
            } else {
                this.selectedItems = [];
            }
        },
        toggleItem(id) {
            if (this.selectedItems.includes(id)) {
                this.selectedItems = this.selectedItems.filter(i => i !== id);
            } else {
                this.selectedItems.push(id);
            }
            this.selectAll = this.selectedItems.length === document.querySelectorAll('.row-checkbox').length;
        },
        submitBulkStatus(status) {
            document.getElementById('bulkStatusValue').value = status;
            document.getElementById('bulkStatusForm').submit();
        }
    }">
        <x-common.page-breadcrumb pageTitle="User Management" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Manage user accounts, roles, and department assignments.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        {{-- Hidden Bulk Action Forms --}}
        <form id="bulkDeleteForm" action="{{ route('users.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="id in selectedItems" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        <form id="bulkStatusForm" action="{{ route('users.bulk-status') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="is_active" id="bulkStatusValue" value="1">
            <template x-for="id in selectedItems" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        {{-- Bulk Action Bar --}}
        <div x-show="selectedItems.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                <span class="text-sm font-medium text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> users selected.
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="submitBulkStatus(1)"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-success-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-success-600">
                    <i data-feather="check-circle" class="w-4 h-4"></i>
                    Set Active
                </button>
                <button type="button" @click="submitBulkStatus(0)"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-warning-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-warning-600">
                    <i data-feather="x-circle" class="w-4 h-4"></i>
                    Set Inactive
                </button>
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected users?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Card Header: Toolbar --}}
            <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-gray-400">
                                <i data-feather="search" class="w-4 h-4"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..."
                                class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('role')) $activeCount++;
                            if (request('department_id')) $activeCount++;
                            if (request('is_active') !== null && request('is_active') !== '') $activeCount++;
                        @endphp

                        <div class="relative" @click.away="filterOpen = false">
                            <button type="button" @click="filterOpen = !filterOpen"
                                class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 transition">
                                <i data-feather="sliders" class="w-4 h-4"></i>
                                Filters
                                @if($activeCount > 0)
                                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                                @endif
                            </button>

                            <div x-show="filterOpen"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute left-0 top-full z-50 mt-2 w-[420px] rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                                style="display: none;">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-feather="x" class="w-5 h-5"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Role</label>
                                        <select name="role"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                            <option value="">All Roles</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Department</label>
                                        <select name="department_id"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                            <option value="">All Departments</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}{{ $dept->company ? ' - ' . $dept->company->name : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-span-2">
                                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                                        <select name="is_active"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                            <option value="">All Statuses</option>
                                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">
                                        Clear All Filters
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            <i data-feather="user-plus" class="w-4 h-4"></i>
                            Add User
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead class="border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="px-5 py-3 sm:px-6 w-10">
                                <div class="flex items-center">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                            <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                <span :class="selectAll ? '' : 'opacity-0'">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">User</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Organization</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Roles</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center justify-end">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($users as $user)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 sm:px-6">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" value="{{ $user->id }}" class="row-checkbox sr-only"
                                                :checked="selectedItems.includes({{ $user->id }})" @click="toggleItem({{ $user->id }})">
                                            <div :class="selectedItems.includes({{ $user->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                <span :class="selectedItems.includes({{ $user->id }}) ? '' : 'opacity-0'">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 shrink-0">
                                            @if($user->avatar)
                                                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                            @else
                                                <div class="w-full h-full flex items-center justify-center font-bold text-sm text-brand-500 bg-brand-50 dark:bg-brand-500/10">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $user->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $user->email }}</span>
                                            @if($user->phone)
                                                <span class="block text-[11px] text-gray-400">{{ $user->phone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ $user->department->name ?? '-' }}</span>
                                        @if($user->position || $user->location)
                                            <span class="text-xs text-gray-500">{{ $user->position->name ?? '-' }} @if($user->location) • {{ $user->location->name }} @endif</span>
                                        @endif
                                        @if($user->company)
                                            <span class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $user->company->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <x-ui.badge color="primary" variant="light" size="sm">{{ $role->name }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if($user->is_active ?? true)
                                        <x-ui.badge color="success" variant="light" size="sm">Active</x-ui.badge>
                                    @else
                                        <x-ui.badge color="danger" variant="light" size="sm">Inactive</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center justify-end gap-3 text-gray-500">
                                        <a href="{{ route('users.edit', $user) }}" class="transition-colors hover:text-brand-500" title="Edit">
                                            <i data-feather="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="transition-colors hover:text-error-500" title="Delete">
                                                    <i data-feather="trash-2" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-feather="users" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No users found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Invite or register users to access this asset management platform.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
