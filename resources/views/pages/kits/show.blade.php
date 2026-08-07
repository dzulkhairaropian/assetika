@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Kit Detail" />
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                <i data-feather="box" class="w-5 h-5 text-brand-500"></i> {{ $kit->name }}
            </h3>
            <div class="flex gap-2">
                <a href="{{ route('kits.checkout-form', $kit) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"><i data-feather="share" class="w-4 h-4"></i> Checkout Kit</a>
                <a href="{{ route('kits.edit', $kit) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"><i data-feather="edit-2" class="w-4 h-4"></i> Edit</a>
            </div>
        </div>
        @if($kit->description) <p class="text-sm text-gray-500 mb-4">{{ $kit->description }}</p> @endif

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
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-400 mb-2">{{ $group['label'] }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($group['items'] as $item)
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700">
                                <span class="h-2 w-2 rounded-full bg-{{ $group['color'] }}-500"></span>
                                <span class="text-sm text-gray-700 dark:text-gray-400">{{ $item->name }}</span>
                                @if($group['show_tag'] && $item->asset_tag)
                                    <span class="text-xs text-gray-400 ml-auto">{{ $item->asset_tag }}</span>
                                @endif
                                <span class="text-xs text-gray-400 ml-auto">x{{ $item->pivot->qty }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
