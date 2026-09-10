@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Kit Detail" :items="[['label' => 'Kits', 'url' => route('kits.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        View equipment kit details and bundled items for {{ $kit->name }}.
    </p>
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="box" class="w-4 h-4 text-brand-500"></i> {{ $kit->name }}
            </h3>
            <div class="flex gap-2">
                <a href="{{ route('kits.checkout-form', $kit) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition"><i data-lucide="share" class="w-3.5 h-3.5"></i> Checkout Kit</a>
                <a href="{{ route('kits.edit', $kit) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit</a>
            </div>
        </div>
        @if($kit->description) <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ $kit->description }}</p> @endif

        @php
            $groups = [
                ['label' => 'Assets', 'items' => $kit->assets, 'color' => 'blue', 'show_tag' => true],
                ['label' => 'Licenses', 'items' => $kit->licenses, 'color' => 'green', 'show_tag' => false],
                ['label' => 'Components', 'items' => $kit->components, 'color' => 'purple', 'show_tag' => false],
                ['label' => 'Consumables', 'items' => $kit->consumables, 'color' => 'orange', 'show_tag' => false],
            ];
        @endphp
        @foreach($groups as $group)
            @if($group['items']->isNotEmpty())
                <div class="mt-3">
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ $group['label'] }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($group['items'] as $item)
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-1.5 dark:border-gray-800 dark:bg-gray-950">
                                <span class="h-2 w-2 rounded-full bg-{{ $group['color'] }}-500"></span>
                                <span class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $item->name }}</span>
                                @if($group['show_tag'] && $item->asset_tag)
                                    <span class="text-[11px] text-gray-400 font-mono ml-auto">{{ $item->asset_tag }}</span>
                                @endif
                                <span class="text-[11px] font-semibold text-gray-500 ml-auto">x{{ $item->pivot->qty }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
