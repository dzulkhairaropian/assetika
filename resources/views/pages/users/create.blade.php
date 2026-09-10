@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create New User" :items="[['label' => 'Users', 'url' => route('users.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Add a new user account to the system.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('users.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-lucide="user" class="w-5 h-5 text-brand-500"></i>
                            General Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                            <!-- Name -->
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Full Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Email Address <span class="text-error-500">*</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="john@example.com"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                @error('email') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Phone Number
                                </label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+62 812 3456 7890"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                @error('phone') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Password <span class="text-error-500">*</span>
                                </label>
                                <input type="password" name="password" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                                @error('password') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Confirm Password <span class="text-error-500">*</span>
                                </label>
                                <input type="password" name="password_confirmation" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800">

                    <div class="pt-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-lucide="briefcase" class="w-5 h-5 text-brand-500"></i>
                            Role & Organization
                        </h3>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                            <!-- Roles -->
                            <div>
                                <x-form.searchable-select
                                    name="roles[]"
                                    label="Role"
                                    :options="$roles->map(fn($r) => ['id' => $r->id, 'text' => $r->name])->toArray()"
                                    :selected="old('roles.0')"
                                    placeholder="Select Role"
                                    required="true"
                                />
                                @error('roles') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Company -->
                            <div>
                                <x-form.searchable-select name="company_id" label="Company" :options="$companies->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray()" :selected="old('company_id')" placeholder="Select Company" />
                            </div>

                            <!-- Department -->
                            <div>
                                <x-form.searchable-select name="department_id" label="Department" :options="$departments->map(fn($d) => ['id' => $d->id, 'text' => $d->name . ($d->company ? ' - ' . $d->company->name : '')])->toArray()" :selected="old('department_id')" placeholder="Select Department" />
                            </div>

                            <!-- Position -->
                            <div>
                                <x-form.searchable-select name="position_id" label="Position" :options="$positions->map(fn($p) => ['id' => $p->id, 'text' => $p->name])->toArray()" :selected="old('position_id')" placeholder="Select Position" />
                                @endphp
                                <x-form.searchable-select name="position_id" label="Position" :options="$posOpts" :selected="old('position_id')" placeholder="Select Position" />
                            </div>

                            <!-- Location -->
                            <div>
                                @php
                                    $locOpts = array_merge([['id' => '', 'text' => 'Select Location']], $locations->map(fn($l) => ['id' => $l->id, 'text' => $l->name])->toArray());
                                @endphp
                                <x-form.searchable-select name="location_id" label="Location" :options="$locOpts" :selected="old('location_id')" placeholder="Select Location" />
                            </div>

                            <!-- Account Status -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Account Status</label>
                                <div class="relative">
                                    <select name="is_active" class="dark:bg-gray-950 h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:placeholder:text-white/30 dark:focus:border-brand-800 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-lucide="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Create User
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                            Create & Create New
                        </button>
                        <a href="{{ route('users.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
