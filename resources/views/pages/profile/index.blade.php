@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="My Profile" />

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Column: Profile Card -->
            <div class="space-y-6">
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <!-- Profile Card Header Banner -->
                    <div class="h-32 w-full bg-gradient-to-r from-brand-500 to-blue-600"></div>
                    
                    <!-- Profile Details -->
                    <div class="px-6 pb-6 flex flex-col items-center -mt-14">
                        <div class="relative w-28 h-28 rounded-full bg-white dark:bg-gray-900 border-4 border-white dark:border-gray-900 flex items-center justify-center text-brand-500 text-3xl font-bold shadow-md mb-4 overflow-hidden">
                            <div class="w-full h-full bg-brand-50/50 dark:bg-white/5 flex items-center justify-center">
                                {{ collect(explode(' ', $user->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white/90 text-center">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500 text-center mb-6">{{ $user->position->name ?? 'No Position' }}</p>
                        
                        <div class="w-full space-y-4 border-t border-gray-100 dark:border-gray-800 pt-6">
                            <div class="flex items-center gap-3 text-sm">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 dark:bg-white/5 text-gray-400">
                                    <i data-feather="mail" class="w-4 h-4"></i>
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 uppercase tracking-wider">Email</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->email }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 dark:bg-white/5 text-gray-400">
                                    <i data-feather="phone" class="w-4 h-4"></i>
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 uppercase tracking-wider">Phone</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->phone ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 dark:bg-white/5 text-gray-400">
                                    <i data-feather="home" class="w-4 h-4"></i>
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 uppercase tracking-wider">Department</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->department->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Edit Profile Form -->
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-8">
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- Section: Personal Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                                <i data-feather="user" class="w-5 h-5 text-brand-500"></i>
                                Personal Information
                            </h3>
                            <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Full Name <span class="text-error-500">*</span>
                                    </label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Email Address <span class="text-error-500">*</span>
                                    </label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    @error('email') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Phone Number
                                    </label>
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+62..."
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    @error('phone') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-800">

                        <!-- Section: Organization Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                                <i data-feather="home" class="w-5 h-5 text-brand-500"></i>
                                Organization Details
                            </h3>
                            <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Company
                                    </label>
                                    <div x-data="{ isOptionSelected: {{ $user->company_id ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                        <select name="company_id"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                            :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Company</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                    @error('company_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Department
                                    </label>
                                    <div x-data="{ isOptionSelected: {{ $user->department_id ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                        <select name="department_id"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                            :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Department</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $dept->name }}{{ $dept->company ? ' - ' . $dept->company->name : '' }}</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                    @error('department_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Location
                                    </label>
                                    <div x-data="{ isOptionSelected: {{ $user->location_id ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                        <select name="location_id"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                            :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Location</option>
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}" {{ old('location_id', $user->location_id) == $loc->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                    @error('location_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Position
                                    </label>
                                    <div x-data="{ isOptionSelected: {{ $user->position_id ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                        <select name="position_id"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                            :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Position</option>
                                            @foreach($positions as $pos)
                                                <option value="{{ $pos->id }}" {{ old('position_id', $user->position_id) == $pos->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $pos->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                    @error('position_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-800">

                        <!-- Section: Security Settings -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                                <i data-feather="lock" class="w-5 h-5 text-brand-500"></i>
                                Security Settings
                            </h3>
                            <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        New Password (Optional)
                                    </label>
                                    <input type="password" name="password" placeholder="Enter new password"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    @error('password') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Confirm New Password
                                    </label>
                                    <input type="password" name="password_confirmation" placeholder="Confirm new password"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3">
                            <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-8 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
