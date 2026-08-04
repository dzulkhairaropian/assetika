@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Bulk Checkout Consumables" />

        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="mb-8 flex items-center gap-4 border-b border-gray-100 pb-6 dark:border-gray-800">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-feather="log-out" class="h-6 w-6"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">Bulk Checkout</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Assigning multiple consumables to a single user in one transaction.</p>
                </div>
            </div>

            <form action="{{ route('consumables.process-bulk-checkout') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Section: Assignment Target -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="user" class="w-5 h-5 text-brand-500"></i>
                        Assignment Target
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Checkout to User <span class="text-error-500">*</span>
                            </label>
                            <div x-data="{ isOptionSelected: {{ old('checked_out_to_user_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                <select name="checked_out_to_user_id" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('checked_out_to_user_id') == $user->id ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $user->name }} ({{ $user->email }})</option>
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
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-800">

                <!-- Section: Consumables & Quantities -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="package" class="w-5 h-5 text-brand-500"></i>
                        Select Quantities
                    </h3>
                    
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="max-w-full overflow-x-auto">
                            <table class="w-full">
                                <thead class="border-b border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Product</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Brand / Model</th>
                                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Available Stock</th>
                                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400" style="width: 150px;">Checkout Qty</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                    @foreach($consumables as $consumable)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="block text-sm font-medium text-gray-800 dark:text-white/90">
                                                    {{ optional($consumable->product)->name }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ optional(optional($consumable->product)->brand)->name ?? '-' }} / {{ optional($consumable->product)->model ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-750 dark:text-gray-300">
                                                {{ $consumable->quantity }} items
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <input type="number" 
                                                    name="quantities[{{ $consumable->id }}]" 
                                                    min="1" 
                                                    max="{{ $consumable->quantity }}" 
                                                    value="{{ old('quantities.'.$consumable->id, 1) }}" 
                                                    required 
                                                    class="dark:bg-dark-900 h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-right text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                                @error('quantities.'.$consumable->id) 
                                                    <p class="mt-1 text-xs text-error-500 text-right">{{ $message }}</p> 
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-800">

                <!-- Section: Additional Info -->
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Notes
                        </label>
                        <textarea name="notes" rows="3" placeholder="Checkout notes..."
                            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Handover Documentation -->
                    <div class="lg:col-span-2">
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
                                <input type="file" name="evidence" class="hidden" accept="image/*,.pdf" />
                            </label>
                        </div>
                        @error('evidence') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('consumables.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
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
