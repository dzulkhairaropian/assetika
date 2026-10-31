@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Create New User" :items="[['label' => 'Users', 'url' => route('users.index')], ['label' => 'Create']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Add a new employee or staff user account with corporate access role and department.
                </p>
            </div>
            <a href="{{ route('users.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Users
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        @php
            $posOpts = array_merge([['id' => '', 'text' => 'Select Position']], $positions->map(fn($p) => ['id' => $p->id, 'text' => $p->name])->toArray());
            $locOpts = array_merge([['id' => '', 'text' => 'Select Location']], $locations->map(fn($l) => ['id' => $l->id, 'text' => $l->name])->toArray());
        @endphp

        <form action="{{ route('users.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
            @csrf

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card 1: User Profile --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Account Information</h3>
                                <p class="text-[11px] text-gray-400">Employee legal name, login email credentials, and phone</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Full Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Email Address <span class="text-error-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="john@company.com"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('email') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Phone Number
                            </label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+62 812 3456 7890"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('phone') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Password <span class="text-error-500">*</span>
                            </label>
                            <input type="password" name="password" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                            @error('password') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Confirm Password <span class="text-error-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition" />
                        </div>
                    </div>
                </div>

                {{-- Card 2: Role & Organizational Assignment --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i data-lucide="briefcase" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Organization & Permissions</h3>
                                <p class="text-[11px] text-gray-400">Role level, company entity, department, and work location</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <x-form.searchable-select
                                name="roles[]"
                                label="System Role"
                                :options="$roles->map(fn($r) => ['id' => $r->id, 'text' => $r->name])->toArray()"
                                :selected="old('roles.0')"
                                placeholder="Select Role"
                                required="true"
                            />
                            @error('roles') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select name="company_id" label="Company" :options="$companies->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray()" :selected="old('company_id')" placeholder="Select Company" />
                        </div>

                        <div>
                            <x-form.searchable-select name="department_id" label="Department" :options="$departments->map(fn($d) => ['id' => $d->id, 'text' => $d->name . ($d->company ? ' - ' . $d->company->name : '')])->toArray()" :selected="old('department_id')" placeholder="Select Department" />
                        </div>

                        <div>
                            <x-form.searchable-select name="position_id" label="Job Position" :options="$posOpts" :selected="old('position_id')" placeholder="Select Position" />
                        </div>

                        <div>
                            <x-form.searchable-select name="location_id" label="Primary Office Location" :options="$locOpts" :selected="old('location_id')" placeholder="Select Location" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Account Access Status</label>
                            <select name="is_active" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active Account</option>
                                <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive / Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            New Account
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Save User
                        </button>

                        <button type="submit" name="create_another" value="1" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-500/30 bg-brand-50/50 px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-100/60 active:scale-[0.98] dark:bg-brand-950/20 dark:text-brand-400 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Save & Add Another
                        </button>

                        <a href="{{ route('users.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
