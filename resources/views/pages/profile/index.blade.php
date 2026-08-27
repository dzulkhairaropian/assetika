@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="My Profile" />

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Left Column: Profile Card -->
            <div class="space-y-4">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Profile Card Header Banner -->
                    <div class="h-28 w-full bg-gradient-to-r from-brand-500 to-blue-600"></div>
                    
                    <!-- Profile Details -->
                    <div class="px-5 pb-5 flex flex-col items-center -mt-12">
                        <div class="relative w-24 h-24 rounded-full bg-white dark:bg-gray-900 border-4 border-white dark:border-gray-900 flex items-center justify-center text-brand-600 text-2xl font-bold shadow-sm mb-3 overflow-hidden">
                            <div class="w-full h-full bg-brand-50/50 dark:bg-white/5 flex items-center justify-center">
                                {{ collect(explode(' ', $user->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center">{{ $user->name }}</h3>
                        <p class="text-xs text-gray-500 text-center mb-4">{{ $user->position->name ?? 'No Position' }}</p>
                        
                        <div class="w-full space-y-3 border-t border-gray-100 dark:border-gray-800 pt-4 text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-50 dark:bg-gray-950 text-gray-400">
                                    <i data-feather="mail" class="w-3.5 h-3.5"></i>
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 uppercase tracking-wider">Email</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->email }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-50 dark:bg-gray-950 text-gray-400">
                                    <i data-feather="phone" class="w-3.5 h-3.5"></i>
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 uppercase tracking-wider">Phone</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->phone ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-50 dark:bg-gray-950 text-gray-400">
                                    <i data-feather="home" class="w-3.5 h-3.5"></i>
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 uppercase tracking-wider">Department</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->department->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Edit Profile Form -->
            <div class="lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Section: Personal Information -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                                <i data-feather="user" class="w-4 h-4 text-brand-500"></i>
                                Personal Information
                            </h3>
                            <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                    @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                    @error('email') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Phone Number
                                    </label>
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+62..."
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                    @error('phone') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-800">

                        <!-- Section: Organization Details -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                                <i data-feather="home" class="w-4 h-4 text-brand-500"></i>
                                Organization Details
                            </h3>
                            <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Company
                                    </label>
                                    <select name="company_id"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('company_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Department
                                    </label>
                                    <select name="department_id"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}{{ $dept->company ? ' - ' . $dept->company->name : '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Location
                                    </label>
                                    <select name="location_id"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        <option value="">Select Location</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}" {{ old('location_id', $user->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('location_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Position
                                    </label>
                                    <select name="position_id"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        <option value="">Select Position</option>
                                        @foreach($positions as $pos)
                                            <option value="{{ $pos->id }}" {{ old('position_id', $user->position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('position_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-800">

                        <!-- Section: Security Settings -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                                <i data-feather="lock" class="w-4 h-4 text-brand-500"></i>
                                Security Settings
                            </h3>
                            <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        New Password (Optional)
                                    </label>
                                    <input type="password" name="password" placeholder="Enter new password"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                    @error('password') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Confirm New Password
                                    </label>
                                    <input type="password" name="password_confirmation" placeholder="Confirm new password"
                                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-gray-800">
                            <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
