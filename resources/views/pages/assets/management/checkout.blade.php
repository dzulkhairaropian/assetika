@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Asset Checkout" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="mb-8 flex items-center gap-4 border-b border-gray-100 pb-6 dark:border-gray-800">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-feather="log-out" class="h-6 w-6"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">Checkout Asset</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Assigning <strong>{{ $asset->asset_tag }}</strong> ({{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model }}) to a user.</p>
                </div>
            </div>

            <form action="{{ route('assets.checkout.process', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Section: Assignment Details -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="user" class="w-5 h-5 text-brand-500"></i>
                        Assignment Details
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2"
                         x-data="{
                             selectedUser: '{{ old('checked_out_to_user_id') }}',
                             selectedLocation: '{{ old('location_id', $asset->location_id) }}',
                             selectedDepartment: '{{ old('department_id', $asset->department_id) }}',
                             onUserChange(e) {
                                 const opt = e.target.options[e.target.selectedIndex];
                                 if (opt) {
                                     const dept = opt.getAttribute('data-department');
                                     const loc = opt.getAttribute('data-location');
                                     if (dept) this.selectedDepartment = dept;
                                     if (loc) this.selectedLocation = loc;
                                 }
                             }
                         }">
                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Checkout to User <span class="text-error-500">*</span>
                            </label>
                            <div class="relative z-20 bg-transparent">
                                <select name="checked_out_to_user_id" required x-model="selectedUser" @change="onUserChange($event)"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-department="{{ $user->department_id }}" data-location="{{ $user->location_id }}" {{ old('checked_out_to_user_id') == $user->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            @error('checked_out_to_user_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Location <span class="text-error-500">*</span>
                            </label>
                            <div class="relative z-20 bg-transparent">
                                <select name="location_id" required x-model="selectedLocation"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id', $asset->location_id) == $location->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            {{ $location->name }}
                                        </option>
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
                                Department <span class="text-error-500">*</span>
                            </label>
                            <div class="relative z-20 bg-transparent">
                                <select name="department_id" required x-model="selectedDepartment"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id', $asset->department_id) == $department->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            {{ $department->name }}{{ $department->company ? ' - ' . $department->company->name : '' }}
                                        </option>
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
                                Supervisor
                            </label>
                            <div x-data="{ isOptionSelected: {{ old('supervisor_user_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                <select name="supervisor_user_id"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Supervisor</option>
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}" {{ old('supervisor_user_id') == $supervisor->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            {{ $supervisor->name }}{{ $supervisor->department ? ' - ' . $supervisor->department->name : '' }}{{ $supervisor->company ? ' - ' . $supervisor->company->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            @error('supervisor_user_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Checkout Date <span class="text-error-500">*</span>
                            </label>
                            <input type="date" name="checked_out_at" value="{{ old('checked_out_at', date('Y-m-d')) }}" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('checked_out_at') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Notes
                            </label>
                            <textarea name="checkout_notes" rows="3" placeholder="Additional details about this assignment..."
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('checkout_notes') }}</textarea>
                            @error('checkout_notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-800">

                <!-- Section: Handover Documentation -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="file-text" class="w-5 h-5 text-brand-500"></i>
                        Handover Documentation
                    </h3>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Evidence (Image/PDF)
                        </label>
                        <div class="relative flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:border-gray-700 dark:hover:border-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i data-feather="upload-cloud" class="w-8 h-8 mb-2 text-gray-400"></i>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-400">PNG, JPG or PDF (MAX. 5MB)</p>
                                </div>
                                <input type="file" name="checkout_evidence" class="hidden" accept="image/*,.pdf" />
                            </label>
                        </div>
                        @error('checkout_evidence') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('assets.show', $asset) }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                        Process Checkout
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
