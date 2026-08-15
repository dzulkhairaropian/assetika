@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Edit Role" :items="[['label' => 'Roles', 'url' => route('roles.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Update role permissions for {{ $role->name }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('roles.update', $role) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="shield" class="w-5 h-5 text-brand-500"></i>
                            General Information
                        </h3>
                        
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Role Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}" required placeholder="e.g. IT Support"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div x-data="{ 
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
                            <label class="font-medium text-gray-800 dark:text-white/90">Permissions</label>
                            <button type="button" @click="toggleAll()" 
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-500 hover:text-brand-600 transition-colors bg-brand-50/50 dark:bg-brand-500/10 px-3 py-1.5 rounded-lg border border-brand-100 dark:border-brand-500/20">
                                <i data-feather="check-square" class="w-3.5 h-3.5" x-show="!allChecked"></i>
                                <i data-feather="square" class="w-3.5 h-3.5" x-show="allChecked" style="display:none;"></i>
                                <span x-text="allChecked ? 'Deselect All' : 'Select All'">Select All</span>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @php
                                $groupedPermissions = $permissions->groupBy(function($item) {
                                    return explode('.', $item->name)[0] ?? 'general';
                                });
                            @endphp

                            @foreach($groupedPermissions as $group => $items)
                                <div class="space-y-3 p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/0"
                                     x-data="{
                                        groupChecked: false,
                                        toggleGroup(root) {
                                            this.groupChecked = !this.groupChecked;
                                            const checkboxes = root.querySelectorAll('.permission-checkbox');
                                            checkboxes.forEach(c => {
                                                c.checked = this.groupChecked;
                                            });
                                            checkboxes.forEach(c => {
                                                c.dispatchEvent(new Event('change'));
                                            });
                                        },
                                        checkState(root) {
                                            const checkboxes = Array.from(root.querySelectorAll('.permission-checkbox'));
                                            this.groupChecked = checkboxes.length > 0 && checkboxes.every(c => c.checked);
                                        }
                                     }"
                                     x-init="$nextTick(() => checkState($root))">
                                    <div class="flex items-center justify-between pb-1.5 border-b border-gray-100 dark:border-gray-800 mb-2">
                                        <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $group }}</h5>
                                        <button type="button" @click="toggleGroup($root)" 
                                            class="text-[10px] font-semibold text-brand-500 hover:text-brand-600 transition-colors">
                                            <span x-text="groupChecked ? 'Deselect' : 'Select'">Select</span>
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($items as $permission)
                                            <label class="flex items-start gap-2 cursor-pointer group">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    @change="checkState($root)"
                                                    class="permission-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                                    {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-brand-500 transition-colors">
                                                    {{ $permission->name }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Update Role
                        </button>
                        <a href="{{ route('roles.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

