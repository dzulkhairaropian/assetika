@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Edit Role" :items="[['label' => 'Roles', 'url' => route('roles.index')], ['label' => 'Edit']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Update role title and granted system permissions for <strong class="text-gray-900 dark:text-white">{{ $role->name }}</strong>.
                </p>
            </div>
            <a href="{{ route('roles.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Roles
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        @php
            $rolePermissions = $role->permissions->pluck('name')->toArray();
        @endphp

        <form action="{{ route('roles.update', $role) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf
            @method('PUT')

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card 1: Role Identity --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="shield" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Role Identity</h3>
                                <p class="text-[11px] text-gray-400">Unique role name and title for access authorization</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                            Role Name <span class="text-error-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $role->name) }}" required placeholder="e.g. IT Administrator / Asset Auditor"
                            class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Card 2: Permissions Matrix --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm"
                     x-data="{ 
                        allChecked: false,
                        toggleAll() {
                            this.allChecked = !this.allChecked;
                            document.querySelectorAll('.permission-checkbox').forEach(c => {
                                c.checked = this.allChecked;
                                c.dispatchEvent(new Event('change'));
                            });
                        },
                        updateAllChecked() {
                            const checkboxes = Array.from(document.querySelectorAll('.permission-checkbox'));
                            this.allChecked = checkboxes.length > 0 && checkboxes.every(c => c.checked);
                        }
                    }"
                    @change="updateAllChecked()"
                    x-init="$nextTick(() => updateAllChecked())">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Module Permissions</h3>
                            <p class="text-[11px] text-gray-400">Select capabilities granted to this role</p>
                        </div>
                        <button type="button" @click="toggleAll()" 
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 bg-brand-50 dark:bg-brand-500/10 px-3 py-1.5 rounded-lg border border-brand-200 dark:border-brand-500/20 transition">
                            <i data-lucide="check-square" class="w-3.5 h-3.5" x-show="!allChecked"></i>
                            <i data-lucide="square" class="w-3.5 h-3.5" x-show="allChecked" style="display:none;"></i>
                            <span x-text="allChecked ? 'Deselect All' : 'Select All'">Select All</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @php
                            $groupedPermissions = $permissions->groupBy(function($item) {
                                return explode('.', $item->name)[0] ?? 'general';
                            });
                        @endphp
                        @foreach($groupedPermissions as $group => $perms)
                            <div class="space-y-2 rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-gray-950/40">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 mb-2 border-b border-gray-200/50 pb-1.5 dark:border-gray-800">
                                    {{ ucfirst($group) }}
                                </h4>
                                <div class="space-y-2">
                                    @foreach($perms as $permission)
                                        <label class="flex items-start gap-2 cursor-pointer select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                class="permission-checkbox mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900"
                                                {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                            <span class="text-xs text-gray-700 dark:text-gray-300 leading-snug">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                            Editing
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Update Role
                        </button>

                        <a href="{{ route('roles.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
