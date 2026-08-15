@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Edit Kit" :items="[['label' => 'Kits', 'url' => route('kits.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Update equipment kit details for {{ $kit->name }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

    <form action="{{ route('kits.update', $kit) }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-feather="box" class="w-5 h-5 text-brand-500"></i> Kit Details
                    </h3>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kit Name <span class="text-error-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $kit->name) }}" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                            <input type="text" name="description" value="{{ old('description', $kit->description) }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </div>
                    </div>

                    @php
                        $attachedAssets = $kit->assets->pluck('id')->toArray();
                        $attachedLicenses = $kit->licenses->pluck('id')->toArray();
                        $attachedComponents = $kit->components->pluck('id')->toArray();
                        $attachedConsumables = $kit->consumables->pluck('id')->toArray();
                        $sections = [
                            ['key' => 'assets', 'label' => 'Assets', 'items' => $assets, 'attached' => $attachedAssets, 'pivotItems' => $kit->assets],
                            ['key' => 'licenses', 'label' => 'Licenses', 'items' => $licenses, 'attached' => $attachedLicenses, 'pivotItems' => $kit->licenses],
                            ['key' => 'components', 'label' => 'Components', 'items' => $components, 'attached' => $attachedComponents, 'pivotItems' => $kit->components],
                            ['key' => 'consumables', 'label' => 'Consumables', 'items' => $consumables, 'attached' => $attachedConsumables, 'pivotItems' => $kit->consumables],
                        ];
                    @endphp

                    @foreach($sections as $section)
                        @if($section['items']->isNotEmpty())
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-400 mb-2">{{ $section['label'] }}</h3>
                            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0">
                                        <tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-12">Add</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Name</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-24">Qty</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                        @foreach($section['items'] as $item)
                                            @php
                                                $isInKit = in_array($item->id, $section['attached']);
                                                $pivotItem = $section['pivotItems']->firstWhere('id', $item->id);
                                                $pivotQty = $pivotItem ? $pivotItem->pivot->qty : 1;
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-2"><input type="checkbox" name="{{ $section['key'] }}[{{ $loop->index }}][id]" value="{{ $item->id }}" {{ $isInKit ? 'checked' : '' }} class="rounded border-gray-300 text-brand-500 focus:ring-brand-500"></td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-400">{{ $item->name }}</td>
                                                <td class="px-4 py-2"><input type="number" name="{{ $section['key'] }}[{{ $loop->index }}][qty]" value="{{ $pivotQty }}" min="1" class="h-8 w-16 rounded border border-gray-300 bg-white px-2 text-xs dark:border-gray-700 dark:bg-gray-900"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                    <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                    Actions
                </h3>
                <div class="space-y-3">
                    <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                        Update Kit
                    </button>
                    <a href="{{ route('kits.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

