@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ activeTab: 'items' }">
    <x-common.page-breadcrumb pageTitle="Kit Detail" :items="[['label' => 'Kits', 'url' => route('kits.index')], ['label' => $kit->name]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        View equipment kit details, bundled hardware components, software licenses, and deployment actions for {{ $kit->name }}.
    </p>

    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

    <!-- Header Hero Card -->
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3.5">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-lucide="box" class="h-6 w-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $kit->name }}</h2>
                        <x-ui.badge color="primary" variant="light" size="sm">Equipment Kit</x-ui.badge>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $kit->description ?: 'No detailed description provided for this pre-defined kit.' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('kits.checkout-form', $kit) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.97] transition-all duration-150 ease-out">
                    <i data-lucide="share" class="h-3.5 w-3.5"></i>
                    Checkout Kit
                </a>
                <a href="{{ route('kits.edit', $kit) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 active:scale-[0.97] transition-all duration-150 ease-out">
                    <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                    Edit
                </a>
                <a href="{{ route('kits.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 active:scale-[0.97] transition-all duration-150 ease-out">
                    <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
                    Back to Kits
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Pill style) -->
    <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5">
        <button @click="activeTab = 'items'"
            :class="activeTab === 'items' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
            class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            Bundled Equipment & Items
        </button>
        <button @click="activeTab = 'info'"
            :class="activeTab === 'info' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
            class="flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
            <i data-lucide="info" class="w-3.5 h-3.5"></i>
            Kit Overview & Specs
        </button>
    </div>

    <!-- Tab 1: Bundled Items -->
    <div x-show="activeTab === 'items'" class="space-y-4">
        @php
            $groups = [
                ['label' => 'Assets', 'items' => $kit->assets, 'color' => 'blue', 'icon' => 'hard-drive', 'show_tag' => true],
                ['label' => 'Components', 'items' => $kit->components, 'color' => 'purple', 'icon' => 'cpu', 'show_tag' => false],
                ['label' => 'Licenses', 'items' => $kit->licenses, 'color' => 'green', 'icon' => 'key-round', 'show_tag' => false],
                ['label' => 'Consumables', 'items' => $kit->consumables, 'color' => 'orange', 'icon' => 'package', 'show_tag' => false],
            ];
            $totalCount = $kit->assets->count() + $kit->components->count() + $kit->licenses->count() + $kit->consumables->count();
        @endphp

        @if($totalCount > 0)
            @foreach($groups as $group)
                @if($group['items']->isNotEmpty())
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
                        <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                            <i data-lucide="{{ $group['icon'] }}" class="w-4 h-4 text-brand-500"></i>
                            Bundled {{ $group['label'] }} ({{ $group['items']->count() }})
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($group['items'] as $item)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-gray-950/50">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white shadow-xs dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                            <i data-lucide="{{ $group['icon'] }}" class="w-4 h-4 text-brand-500"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $item->name }}</p>
                                            @if($group['show_tag'] && $item->asset_tag)
                                                <p class="text-[11px] font-mono font-medium text-brand-600 dark:text-brand-400">{{ $item->asset_tag }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                        x{{ $item->pivot->qty }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-gray-800/50">
                        <i data-lucide="box" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No equipment items attached</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Edit this kit to assign hardware assets, licenses, components, or consumables.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Tab 2: Kit Overview & Specs -->
    <div x-show="activeTab === 'info'" class="space-y-4" style="display: none;">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i>
                Kit General Specification
            </h3>

            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-[11px] font-medium text-gray-400">Kit Name</dt>
                    <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $kit->name }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-medium text-gray-400">Total Asset Categories</dt>
                    <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $kit->assets->count() }} Assets</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-medium text-gray-400">Total Component Types</dt>
                    <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $kit->components->count() }} Components</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-medium text-gray-400">Total Licenses Included</dt>
                    <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $kit->licenses->count() }} Software Licenses</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-medium text-gray-400">Total Consumables Included</dt>
                    <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $kit->consumables->count() }} Consumables</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-medium text-gray-400">Created Date</dt>
                    <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $kit->created_at ? $kit->created_at->format('d M Y, H:i') : '-' }}</dd>
                </div>
                @if($kit->description)
                <div class="sm:col-span-2 lg:col-span-3">
                    <dt class="text-[11px] font-medium text-gray-400">Full Kit Description</dt>
                    <dd class="text-xs text-gray-600 dark:text-gray-400 italic mt-0.5">{{ $kit->description }}</dd>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
