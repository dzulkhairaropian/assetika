@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
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

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage user employee accounts, system authorization roles, and department assignments.
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
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 p-3 shadow-xs dark:border-brand-900/50 dark:bg-brand-950/40"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> users selected.
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="submitBulkStatus(1)"
                    class="inline-flex items-center justify-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 transition">
                    <i data-lucide="circle-check" class="w-3.5 h-3.5"></i>
                    Set Active
                </button>
                <button type="button" @click="submitBulkStatus(0)"
                    class="inline-flex items-center justify-center gap-1 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-amber-600 transition">
                    <i data-lucide="circle-x" class="w-3.5 h-3.5"></i>
                    Set Inactive
                </button>
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected users?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        {{-- Main Table Container (Exact Asset Index Layout) --}}
        <div class="overflow-hidden xl:overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Toolbar --}}
            <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400 pointer-events-none">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, role..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50/70 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                        </div>

                        @php
                            $activeCount = 0;
                            if (request('role')) $activeCount++;
                            if (request('company_id')) $activeCount++;
                            if (request('department_id')) $activeCount++;
                            if (request('is_active') !== null && request('is_active') !== '') $activeCount++;
                        @endphp

                        <div class="relative" @click.away="filterOpen = false">
                            <button type="button" @click="filterOpen = !filterOpen"
                                class="relative inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                Filters
                                @if($activeCount > 0)
                                    <span class="inline-flex items-center justify-center min-w-[18px] h-4 px-1 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                                @endif
                            </button>

                            <div x-show="filterOpen" x-transition
                                class="absolute left-0 top-full z-50 mt-2 w-80 sm:w-96 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                style="display: none;">
                                <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-2.5 text-left">
                                    <div class="col-span-2">
                                        <x-form.searchable-select name="role" label="Role" :options="$roles->map(fn($r) => ['id' => $r->name, 'text' => $r->name])->toArray()" :selected="request('role')" placeholder="All Roles" />
                                    </div>
                                    <div>
                                        <x-form.searchable-select name="company_id" label="Company" :options="$companies->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray()" :selected="request('company_id')" placeholder="All Companies" />
                                    </div>
                                    <div>
                                        <x-form.searchable-select name="department_id" label="Department" :options="$departments->map(fn($d) => ['id' => $d->id, 'text' => $d->name])->toArray()" :selected="request('department_id')" placeholder="All Departments" />
                                    </div>
                                    <div class="col-span-2">
                                        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Account Status</label>
                                        <select name="is_active" class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Statuses</option>
                                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('users.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear All Filters
                                    </a>
                                    <button type="submit" @click="filterOpen = false"
                                        class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(request()->filled('search') || $activeCount > 0)
                            <a href="{{ route('users.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add User
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-gray-100 bg-gray-50/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">
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
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Employee User</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Role</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Company & Department</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" value="{{ $user->id }}" class="row-checkbox sr-only"
                                                :checked="selectedItems.includes({{ $user->id }})" @click="toggleItem({{ $user->id }})">
                                            <div :class="selectedItems.includes({{ $user->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                                <span :class="selectedItems.includes({{ $user->id }}) ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-[11px] font-bold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</span>
                                            <span class="text-[11px] text-gray-400">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ optional($user->company)->name ?? 'General Company' }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">
                                            {{ optional($user->department)->name ?? 'No Department' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($user->is_active ?? true)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('users.edit', $user) }}"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400 transition"
                                           title="Edit User">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <i data-lucide="users" class="h-6 w-6"></i>
                                    </div>
                                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No Users Found</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                        No employee or administrator accounts match current filter criteria.
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
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
