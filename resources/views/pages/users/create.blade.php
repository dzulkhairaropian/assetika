@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Create New User" :items="[['label' => 'Users', 'url' => route('users.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Add a new user account to the system.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('users.store') }}" method="POST" class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_340px]">
            @csrf

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                            <i data-feather="user" class="w-4 h-4 text-brand-500"></i>
                            General Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                            <!-- Name -->
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="john@example.com"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                @error('email') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Phone Number
                                </label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+62 812 3456 7890"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                @error('phone') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                @error('password') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password_confirmation" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800">

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                            <i data-feather="briefcase" class="w-4 h-4 text-brand-500"></i>
                            Role & Organization
                        </h3>
                        <div class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                            <!-- Roles -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Role <span class="text-red-500">*</span>
                                </label>
                                <select name="roles[]" required
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('roles') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Company -->
                            <div>
                                @php
                                    $companyOpts = array_merge([['id' => '', 'text' => 'Select Company']], $companies->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray());
                                @endphp
                                <x-form.searchable-select name="company_id" label="Company" :options="$companyOpts" :selected="old('company_id')" placeholder="Select Company" />
                            </div>

                            <!-- Department -->
                            <div>
                                @php
                                    $deptOpts = array_merge([['id' => '', 'text' => 'Select Department']], $departments->map(fn($d) => ['id' => $d->id, 'text' => $d->name . ($d->company ? ' - ' . $d->company->name : '')])->toArray());
                                @endphp
                                <x-form.searchable-select name="department_id" label="Department" :options="$deptOpts" :selected="old('department_id')" placeholder="Select Department" />
                            </div>

                            <!-- Position -->
                            <div>
                                @php
                                    $posOpts = array_merge([['id' => '', 'text' => 'Select Position']], $positions->map(fn($p) => ['id' => $p->id, 'text' => $p->name])->toArray());
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
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Account Status</label>
                                <select name="is_active" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                        <i data-feather="save" class="w-4 h-4 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            Create User
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:border-brand-500 dark:bg-gray-950 dark:text-brand-400 transition">
                            Create & Create New
                        </button>
                        <a href="{{ route('users.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
