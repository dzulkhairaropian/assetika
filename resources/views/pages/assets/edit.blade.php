@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Edit IT Asset" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Update information, status, or assignment for asset {{ $asset->asset_tag }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('assets.update', $asset) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-8">
                    <!-- Section: General Information -->
                    <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                        General Information
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Asset Tag
                            </label>
                            @if($asset->status && $asset->status->name === 'Draft')
                                <input type="text" name="asset_tag" value="{{ old('asset_tag', $asset->asset_tag) }}" placeholder="Generate automatically if empty"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @else
                                <input type="text" value="{{ $asset->asset_tag }}" disabled
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-400 dark:border-gray-800 dark:bg-white/[0.03]" />
                            @endif
                            <p class="mt-1 text-xs text-gray-400 italic">Generated automatically if left empty</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Device Name
                            </label>
                            @if($asset->status && $asset->status->name === 'Draft')
                                <input type="text" name="device_name" value="{{ old('device_name', $asset->device_name) }}" placeholder="Generate automatically if empty"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @else
                                <input type="text" value="{{ $asset->device_name }}" disabled
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-400 dark:border-gray-800 dark:bg-white/[0.03]" />
                            @endif
                            <p class="mt-1 text-xs text-gray-400 italic">Generated automatically if left empty</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Product <span class="text-error-500">*</span>
                            </label>
                            <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                                <select name="product_id" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id', $asset->product_id) == $product->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            {{ $product->brand->name ?? '' }} - {{ $product->model }} ({{ $product->category->name ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            @error('product_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Status <span class="text-error-500">*</span>
                            </label>
                            <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                                <select name="status_id" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ old('status_id', $asset->status_id) == $status->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            @error('status_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Vendor
                            </label>
                            <div x-data="{ isOptionSelected: {{ $asset->vendor_id ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                <select name="vendor_id"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id', $asset->vendor_id) == $vendor->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            @error('vendor_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Department <span class="text-error-500">*</span>
                            </label>
                            <div x-data="{ isOptionSelected: {{ $asset->department_id ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                <select name="department_id" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $asset->department_id) == $dept->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $dept->name }}{{ $dept->company ? ' - ' . $dept->company->name : '' }}</option>
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
                            <div x-data="{ isOptionSelected: {{ $asset->location_id ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                <select name="location_id"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Location</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ old('location_id', $asset->location_id) == $loc->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
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
                                Serial Number <span class="text-error-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" placeholder="Enter serial number" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <button type="button" onclick="generateSN()" title="Generate Random Serial Number" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-theme-xs transition hover:bg-gray-50 hover:text-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-brand-400">
                                    <i data-feather="shuffle" class="w-4 h-4"></i>
                                </button>
                            </div>
                            @error('serial_number') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Product Number
                            </label>
                            <input type="text" name="product_number" value="{{ old('product_number', $asset->product_number) }}" placeholder="Enter product number"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('product_number') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                User Name
                            </label>
                            <input type="text" name="user_name" value="{{ old('user_name', $asset->user_name) }}" placeholder="e.g. John Doe"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('user_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.date-picker
                                id="release_date"
                                name="release_date"
                                label="Release Date"
                                placeholder="Select release date"
                                defaultDate="{{ old('release_date', $asset->release_date?->format('Y-m-d')) }}"
                            />
                            @error('release_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Network Information -->
                <hr class="border-gray-100 dark:border-gray-800">
                <div class="pt-2">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="wifi" class="w-5 h-5 text-brand-500"></i>
                        Network Information
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                MAC Address Wireless
                            </label>
                            <input type="text" name="mac_address_wireless" value="{{ old('mac_address_wireless', $asset->mac_address_wireless) }}" placeholder="00:00:00:00:00:00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('mac_address_wireless') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                MAC Address Ethernet
                            </label>
                            <input type="text" name="mac_address_ethernet" value="{{ old('mac_address_ethernet', $asset->mac_address_ethernet) }}" placeholder="00:00:00:00:00:00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('mac_address_ethernet') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Software Licenses -->
                <hr class="border-gray-100 dark:border-gray-800">
                <div class="pt-2">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="key" class="w-5 h-5 text-brand-500"></i>
                        Software Licenses
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Windows License
                            </label>
                            <input type="text" name="license_windows" value="{{ old('license_windows', $asset->license_windows) }}" placeholder="Enter Windows license key"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('license_windows') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Office License
                            </label>
                            <input type="text" name="license_office" value="{{ old('license_office', $asset->license_office) }}" placeholder="Enter Office license key"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('license_office') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Technical Specifications -->
                <hr class="border-gray-100 dark:border-gray-800">
                <div class="pt-2">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="cpu" class="w-5 h-5 text-brand-500"></i>
                        Technical Specifications
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Operating System
                            </label>
                            <div x-data="{ isOptionSelected: {{ $asset->operating_system_id ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                <select name="operating_system_id"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select OS</option>
                                    @foreach($operatingSystems as $os)
                                        <option value="{{ $os->id }}" {{ old('operating_system_id', $asset->operating_system_id) == $os->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $os->name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            @error('operating_system_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Specification
                            </label>
                            <textarea name="specification" rows="4" placeholder="e.g. Core i7, 16GB RAM, 512GB SSD"
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('specification', $asset->specification) }}</textarea>
                            @error('specification') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Accessories
                            </label>
                            <textarea name="accessories" rows="3" placeholder="e.g. Mouse, Keyboard, Charger"
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('accessories', $asset->accessories) }}</textarea>
                            @error('accessories') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-800">
                @include('pages.assets.partials.custom-fields')
                </div>
            </div>

            <div class="space-y-6">
                <!-- Section: Financial Information -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-feather="dollar-sign" class="w-5 h-5 text-brand-500"></i>
                        Financial Information
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <x-form.date-picker
                                id="purchase_date"
                                name="purchase_date"
                                label="Purchase Date"
                                placeholder="Select purchase date"
                                defaultDate="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}"
                            />
                            @error('purchase_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Purchase Cost
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">Rp</span>
                                <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost) }}" placeholder="0.00"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>
                            @error('purchase_cost') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Depreciation Profile
                            </label>
                            <select name="depreciation_id"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">-- Select Depreciation --</option>
                                @foreach($depreciations as $depr)
                                    <option value="{{ $depr->id }}" {{ old('depreciation_id', $asset->depreciation_id) == $depr->id ? 'selected' : '' }}>{{ $depr->name }} ({{ $depr->months }} Months)</option>
                                @endforeach
                            </select>
                            @error('depreciation_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.date-picker
                                id="warranty_expiry_date"
                                name="warranty_expiry_date"
                                label="Warranty Expiry Date"
                                placeholder="Select warranty expiry date"
                                defaultDate="{{ old('warranty_expiry_date', $asset->warranty_expiry_date?->format('Y-m-d')) }}"
                            />
                            @error('warranty_expiry_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Invoice Number
                            </label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number', $asset->invoice_number) }}" placeholder="e.g. INV/2026/06/001"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('invoice_number') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Notes
                            </label>
                            <textarea name="notes" rows="3" placeholder="Additional notes..."
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('notes', $asset->notes) }}</textarea>
                            @error('notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Update Asset
                        </button>
                        <button type="submit" name="is_draft" value="1" formnovalidate class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                            Save as Draft
                        </button>
                        <a href="{{ route('assets.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function generateSN() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let sn = 'DUMMY-SN-';
        for (let i = 0; i < 8; i++) sn += chars.charAt(Math.floor(Math.random() * chars.length));
        document.querySelector('input[name="serial_number"]').value = sn;
    }
</script>
@endpush
