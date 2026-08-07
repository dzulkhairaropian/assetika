@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Edit Custom Field" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <form action="{{ route('custom-fields.update', $customField) }}" method="POST" x-data="{ fieldType: '{{ old('field_type', $customField->field_type) }}' }">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="list" class="w-5 h-5 text-brand-500"></i>
                        Field Information
                    </h3>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Field Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $customField->name) }}" placeholder="e.g., MAC Address" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Field Type <span class="text-error-500">*</span>
                            </label>
                            <select name="field_type" x-model="fieldType" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="text" {{ old('field_type', $customField->field_type) === 'text' ? 'selected' : '' }}>Text</option>
                                <option value="textarea" {{ old('field_type', $customField->field_type) === 'textarea' ? 'selected' : '' }}>Textarea</option>
                                <option value="dropdown" {{ old('field_type', $customField->field_type) === 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                <option value="date" {{ old('field_type', $customField->field_type) === 'date' ? 'selected' : '' }}>Date</option>
                                <option value="boolean" {{ old('field_type', $customField->field_type) === 'boolean' ? 'selected' : '' }}>Boolean (Yes/No)</option>
                                <option value="number" {{ old('field_type', $customField->field_type) === 'number' ? 'selected' : '' }}>Number</option>
                            </select>
                            @error('field_type') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Help Text
                            </label>
                            <input type="text" name="help_text" value="{{ old('help_text', $customField->help_text) }}" placeholder="Hint shown below the field"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Default Value
                            </label>
                            <input type="text" name="default_value" value="{{ old('default_value', $customField->default_value) }}" placeholder="Default value"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Order
                            </label>
                            <input type="number" name="order" value="{{ old('order', $customField->order) }}" min="0"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div x-show="fieldType === 'dropdown'" class="lg:col-span-2" style="{{ old('field_type', $customField->field_type) !== 'dropdown' ? 'display: none;' : '' }}">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Dropdown Options <span class="text-error-500">*</span>
                            </label>
                            <textarea name="field_values" rows="5" placeholder="One option per line"
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('field_values', is_array($customField->field_values) ? implode("\n", $customField->field_values) : $customField->field_values) }}</textarea>
                            @error('field_values') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('custom-fields.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                        Update Field
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
