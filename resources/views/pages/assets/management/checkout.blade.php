@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-common.page-breadcrumb pageTitle="Asset Checkout" :items="[['label' => 'IT Assets', 'url' => route('assets.index')], ['label' => $asset->asset_tag, 'url' => route('assets.show', $asset)], ['label' => 'Checkout']]" />
                <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Assign and hand over asset <strong class="font-mono text-gray-900 dark:text-white">{{ $asset->asset_tag }}</strong> to an employee or department holder.
                </p>
            </div>
            <a href="{{ route('assets.show', $asset) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Asset
            </a>
        </div>

        @if(session('error'))
            <x-ui.alert variant="danger" message="{{ session('error') }}" />
        @endif

        @php
            $userOptions = array_merge(
                [['id' => '', 'text' => 'Select User']],
                $users->map(fn($u) => ['id' => $u->id, 'text' => $u->name . ' (' . ($u->department?->name ?? $u->email) . ')'])->toArray()
            );
            $departmentOptions = array_merge(
                [['id' => '', 'text' => 'Select Department']],
                $departments->map(fn($d) => ['id' => $d->id, 'text' => $d->name])->toArray()
            );
            $locationOptions = array_merge(
                [['id' => '', 'text' => 'Select Location']],
                $locations->map(fn($l) => ['id' => $l->id, 'text' => $l->name])->toArray()
            );
            $supervisorOptions = array_merge(
                [['id' => '', 'text' => 'Select Supervisor / Manager']],
                $supervisors->map(fn($s) => ['id' => $s->id, 'text' => $s->name . ($s->department ? ' - ' . $s->department->name : '')])->toArray()
            );
        @endphp

        <form action="{{ route('assets.checkout.process', $asset) }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]"
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

            {{-- Main Form Column --}}
            <div class="space-y-6">
                {{-- Card 1: Handover Target & Location --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i data-lucide="user-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Assignment Target</h3>
                                <p class="text-[11px] text-gray-400">Recipient employee, operating department, and office location</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <x-form.searchable-select
                                name="checked_out_to_user_id"
                                label="Checkout to User (Recipient)"
                                :options="$userOptions"
                                :selected="old('checked_out_to_user_id')"
                                placeholder="Search & select recipient employee"
                                required="true"
                            />
                            @error('checked_out_to_user_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select
                                name="department_id"
                                label="Department"
                                :options="$departmentOptions"
                                :selected="old('department_id', $asset->department_id)"
                                placeholder="Select Department"
                                required="true"
                            />
                            @error('department_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.searchable-select
                                name="location_id"
                                label="Physical Location"
                                :options="$locationOptions"
                                :selected="old('location_id', $asset->location_id)"
                                placeholder="Select Location"
                                required="true"
                            />
                            @error('location_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <x-form.searchable-select
                                name="supervisor_user_id"
                                label="Supervisor / Knowing Party (Optional)"
                                :options="$supervisorOptions"
                                :selected="old('supervisor_user_id')"
                                placeholder="Select Supervisor / Manager"
                            />
                            @error('supervisor_user_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Handover Schedule & Terms --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Dates & Handover Notes</h3>
                                <p class="text-[11px] text-gray-400">Schedule timeline and return conditions</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                        <div>
                            <x-form.date-picker
                                id="checked_out_at"
                                name="checked_out_at"
                                label="Checkout / Handover Date"
                                placeholder="Select checkout date"
                                defaultDate="{{ old('checked_out_at', date('Y-m-d')) }}"
                            />
                            @error('checked_out_at') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-form.date-picker
                                id="expected_return_date"
                                name="expected_return_date"
                                label="Expected Return Date (For Temporary Loans)"
                                placeholder="Leave empty for permanent assignment"
                                defaultDate="{{ old('expected_return_date') }}"
                            />
                            @error('expected_return_date') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Handover Notes / Equipment Completeness
                            </label>
                            <textarea name="checkout_notes" rows="3" placeholder="Condition remarks, included peripherals (charger, bag, mouse), or special handover instructions..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">{{ old('checkout_notes') }}</textarea>
                            @error('checkout_notes') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Asset Card --}}
            <div class="space-y-6 xl:sticky xl:top-20">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="log-out" class="w-4 h-4 text-brand-500"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Action</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            Checkout
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-brand-500/20 hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Confirm & Checkout Asset
                        </button>

                        <a href="{{ route('assets.show', $asset) }}" class="inline-flex w-full items-center justify-center rounded-lg border border-transparent px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Hardware Summary Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Hardware Info</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Asset Tag:</span>
                            <span class="font-mono font-bold text-brand-600 dark:text-brand-400">{{ $asset->asset_tag }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Model:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Serial Number:</span>
                            <span class="font-mono text-gray-700 dark:text-gray-300">{{ $asset->serial_number ?: '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Current Status:</span>
                            <x-ui.badge :color="optional($asset->status)->color" variant="light" size="sm">
                                {{ optional($asset->status)->name ?? 'Unknown' }}
                            </x-ui.badge>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
