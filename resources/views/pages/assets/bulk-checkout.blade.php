@extends('layouts.app')
@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Bulk Checkout Assets" :items="[['label' => 'IT Assets', 'url' => route('assets.index')]]" />
    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif
    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Selected Assets ({{ count($assets) }})</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Assign all selected hardware units to a single employee in one transaction.</p>
            </div>
            <a href="{{ route('assets.index') }}" class="text-xs text-brand-600 hover:underline">Change Selection</a>
        </div>

        <div class="flex flex-wrap gap-2 mb-5">
            @foreach($assets as $a)
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 font-medium">
                    <i data-lucide="monitor" class="w-3.5 h-3.5 text-brand-500"></i>
                    <strong class="font-mono text-gray-900 dark:text-white">{{ $a->asset_tag }}</strong>
                    <span class="text-gray-500 dark:text-gray-400">({{ optional(optional($a->product)->brand)->name }} {{ optional($a->product)->model ?? $a->device_name }})</span>
                    @if($a->serial_number)
                        <span class="font-mono text-[10px] text-gray-400">S/N: {{ $a->serial_number }}</span>
                    @endif
                </span>
            @endforeach
        </div>

        <form action="{{ route('assets.bulk-checkout.process') }}" method="POST">
            @csrf
            @foreach($assets as $a)
                <input type="hidden" name="ids[]" value="{{ $a->id }}">
            @endforeach

            @php
                $userMeta = $users->mapWithKeys(fn ($u) => [
                    (string) $u->id => [
                        'department' => (string) ($u->department_id ?? ''),
                        'location'   => (string) ($u->location_id ?? ''),
                    ],
                ]);
            @endphp

            <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2"
                 x-data="{
                     selectedLocation: '{{ old('location_id') }}',
                     selectedDepartment: '{{ old('department_id') }}',
                     userMeta: @json($userMeta),
                     onUserSelected(e) {
                         if (e.detail.name !== 'checked_out_to_user_id') return;
                         const meta = this.userMeta[String(e.detail.id)];
                         if (!meta) return;
                         if (meta.department) this.selectedDepartment = meta.department;
                         if (meta.location) this.selectedLocation = meta.location;
                     }
                 }"
                 @select-changed.window="onUserSelected($event)">

                <div class="sm:col-span-2">
                    @php
                        $userOptions = array_merge(
                            [['id' => '', 'text' => 'Select User']],
                            $users->map(fn($u) => ['id' => $u->id, 'text' => $u->name . ' (' . ($u->department?->name ?? $u->email) . ')'])->toArray()
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
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Department</label>
                    <select name="department_id" x-model="selectedDepartment"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">-- Auto from User / Optional --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}{{ $d->company ? ' - ' . $d->company->name : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Location</label>
                    <select name="location_id" x-model="selectedLocation"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">-- Auto from User / Optional --</option>
                        @foreach($locations as $l)
                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Checkout Date <span class="text-red-500">*</span></label>
                    <input type="date" name="checked_out_at" value="{{ old('checked_out_at', date('Y-m-d')) }}" required
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                        Expected Return Date
                        <span class="text-[10px] text-gray-400 font-normal">(Optional for temporary loans)</span>
                    </label>
                    <input type="date" name="expected_return_date" value="{{ old('expected_return_date') }}"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Checkout Notes</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" placeholder="e.g. New joiner workstation bundle">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 mt-5 border-t border-gray-100 pt-3 dark:border-gray-800">
                <a href="{{ route('assets.index') }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">Checkout All ({{ count($assets) }})</button>
            </div>
        </form>
    </div>
</div>
@endsection
