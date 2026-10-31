@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Bulk Checkout Assets" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => 'Bulk Checkout']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Assign all {{ count($assets) }} selected hardware units to a single employee in one checkout transaction.
                </p>
            </div>
            <a href="{{ route('assets.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Change Selection
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        @php
            $userOptions = array_merge(
                [['id' => '', 'text' => 'Select User']],
                $users->map(fn($u) => ['id' => $u->id, 'text' => $u->name . ' (' . ($u->department?->name ?? $u->email) . ')'])->toArray()
            );
            $departmentOptions = array_merge(
                [['id' => '', 'text' => 'Select Department (Auto from User)']],
                $departments->map(fn($d) => ['id' => $d->id, 'text' => $d->name])->toArray()
            );
            $locationOptions = array_merge(
                [['id' => '', 'text' => 'Select Location (Auto from User)']],
                $locations->map(fn($l) => ['id' => $l->id, 'text' => $l->name])->toArray()
            );
        @endphp

        <form action="{{ route('assets.bulk-checkout.process') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]"
              x-data="{
                  userMeta: @json($users->mapWithKeys(fn ($u) => [(string) $u->id => ['department' => (string) ($u->department_id ?? ''), 'location' => (string) ($u->location_id ?? '')]])),
                  onUserSelected(e) {
                      if (e.detail.name !== 'checked_out_to_user_id') return;
                      const meta = this.userMeta[String(e.detail.id)];
                      if (!meta) return;
                      if (meta.department) {
                          this.$dispatch('set-selected-id', { name: 'department_id', id: meta.department });
                      }
                      if (meta.location) {
                          this.$dispatch('set-selected-id', { name: 'location_id', id: meta.location });
                      }
                  }
              }"
              @select-changed.window="onUserSelected($event)">
            @csrf
            @foreach($assets as $a)
                <input type="hidden" name="ids[]" value="{{ $a->id }}">
            @endforeach

            {{-- Main Column --}}
            <div class="space-y-6">
                {{-- Selected Assets Badges --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="layers" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Selected Assets ({{ count($assets) }})</h3>
                        </div>
                        <a href="{{ route('assets.index') }}" class="text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400">
                            Modify Selection
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach($assets as $a)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 font-medium">
                                <i data-lucide="monitor" class="w-3.5 h-3.5 text-brand-500"></i>
                                <strong class="font-mono text-gray-900 dark:text-white">{{ $a->asset_tag }}</strong>
                                <span class="text-gray-500 dark:text-gray-400">({{ optional($a->product?->brand)->name }} {{ $a->product?->model ?? $a->device_name }})</span>
                                @if($a->serial_number)
                                    <span class="font-mono text-[10px] text-gray-400">S/N: {{ $a->serial_number }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Recipient & Assignment --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="user-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Recipient & Assignment Target</h3>
                                <p class="text-[11px] text-gray-400">Choose the employee who will receive all selected assets</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <x-form.searchable-select
                                name="checked_out_to_user_id"
                                label="Checkout to User"
                                :options="$userOptions"
                                :selected="old('checked_out_to_user_id')"
                                placeholder="Select User"
                                required="true"
                            />
                            @error('checked_out_to_user_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select
                                name="department_id"
                                label="Department"
                                :options="$departmentOptions"
                                :selected="old('department_id')"
                                placeholder="Select Department"
                            />
                            @error('department_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select
                                name="location_id"
                                label="Location"
                                :options="$locationOptions"
                                :selected="old('location_id')"
                                placeholder="Select Location"
                            />
                            @error('location_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.date-picker
                                id="checked_out_at"
                                name="checked_out_at"
                                label="Checkout Date"
                                placeholder="Select checkout date"
                                defaultDate="{{ old('checked_out_at', date('Y-m-d')) }}"
                            />
                        </div>

                        <div>
                            <x-form.date-picker
                                id="expected_return_date"
                                name="expected_return_date"
                                label="Expected Return Date (Optional)"
                                placeholder="Select target return date"
                                defaultDate="{{ old('expected_return_date') }}"
                            />
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Notes / Scope Description</label>
                            <textarea name="notes" rows="3" placeholder="Batch checkout purpose, project name, or package description..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="log-out" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Batch Action</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            {{ count($assets) }} Units
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Checkout {{ count($assets) }} Assets
                        </button>

                        <a href="{{ route('assets.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
