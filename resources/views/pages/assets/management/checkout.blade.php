@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Asset Checkout" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)]]" />

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-lucide="log-out" class="h-5 w-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Checkout Asset</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Assigning <strong>{{ $asset->asset_tag }}</strong> ({{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model }}) to a user.</p>
                </div>
            </div>

            <form action="{{ route('assets.checkout.process', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section: Assignment Details -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-brand-500"></i>
                        Assignment Details
                    </h3>
                    <div class="grid grid-cols-1 gap-x-4 gap-y-3.5 lg:grid-cols-2"
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
                            @php
                                $userOptions = array_merge(
                                    [['id' => '', 'text' => 'Select User']],
                                    $users->map(fn($u) => ['id' => $u->id, 'text' => $u->name . ' (' . $u->email . ')'])->toArray()
                                );
                            @endphp
                            <x-form.searchable-select
                                name="checked_out_to_user_id"
                                label="Checkout to User"
                                :options="$userOptions"
                                :selected="old('checked_out_to_user_id')"
                                placeholder="Select User"
                                required="true"
                            />
                            @error('checked_out_to_user_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Location <span class="text-red-500">*</span>
                            </label>
                            <div class="relative z-20 bg-transparent">
                                <select name="location_id" required x-model="selectedLocation"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    <option value="">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id', $asset->location_id) == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('location_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Department <span class="text-red-500">*</span>
                            </label>
                            <div class="relative z-20 bg-transparent">
                                <select name="department_id" required x-model="selectedDepartment"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id', $asset->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}{{ $department->company ? ' - ' . $department->company->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('department_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Supervisor
                            </label>
                            <div class="relative z-20 bg-transparent">
                                <select name="supervisor_user_id"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    <option value="">Select Supervisor</option>
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}" {{ old('supervisor_user_id') == $supervisor->id ? 'selected' : '' }}>
                                            {{ $supervisor->name }}{{ $supervisor->department ? ' - ' . $supervisor->department->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('supervisor_user_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Checkout Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="checked_out_at" value="{{ old('checked_out_at', date('Y-m-d')) }}" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                            @error('checked_out_at') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <textarea name="checkout_notes" rows="3" placeholder="Additional details about this assignment..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('checkout_notes') }}</textarea>
                            @error('checkout_notes') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-800">

                <!-- Section: Handover Documentation -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i>
                        Handover Documentation
                    </h3>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Evidence (Image/PDF)
                        </label>
                        <div class="relative flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-950 hover:bg-gray-100 dark:border-gray-800 transition">
                                <div class="flex flex-col items-center justify-center pt-4 pb-4">
                                    <i data-lucide="cloud-upload" class="w-6 h-6 mb-1 text-gray-400"></i>
                                    <p class="mb-1 text-xs text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-[11px] text-gray-400">PNG, JPG or PDF (MAX. 5MB)</p>
                                </div>
                                <input type="file" name="checkout_evidence" class="hidden" accept="image/*,.pdf" />
                            </label>
                        </div>
                        @error('checkout_evidence') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <a href="{{ route('assets.show', $asset) }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        Process Checkout
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
