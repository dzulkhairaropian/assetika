@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create Fieldset" />

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('fieldsets.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="layers" class="w-5 h-5 text-brand-500"></i>
                            Fieldset Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-1">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Fieldset Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Network Devices" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    @if($availableFields->isNotEmpty())
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                            <i data-feather="list" class="w-5 h-5 text-brand-500"></i>
                            Available Custom Fields
                        </h3>

                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Include</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Field Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Order</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Required</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                    @foreach($availableFields as $field)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <input type="checkbox" name="custom_fields[{{ $loop->index }}][id]" value="{{ $field->id }}" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-400">{{ $field->name }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $field->field_type }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" name="custom_fields[{{ $loop->index }}][order]" value="0" min="0" class="h-9 w-20 rounded border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:bg-gray-900">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="checkbox" name="custom_fields[{{ $loop->index }}][required]" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-900/50">
                        <p class="text-sm text-gray-500">No custom fields available. <a href="{{ route('custom-fields.create') }}" class="text-brand-500 hover:underline">Create one first</a>.</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white/90">
                        <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Create Fieldset
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                            Create & Create New
                        </button>
                        <a href="{{ route('fieldsets.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

