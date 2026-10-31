@extends('layouts.app')

@section('content')
<div class="space-y-5" x-data="{ activeTab: 'items' }">
    <x-common.page-breadcrumb pageTitle="Kit Details" :items="[['label' => 'Kits', 'url' => route('kits.index')], ['label' => $kit->name]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Pre-packaged onboarding bundle specifications, bundled hardware, software seats, and deployment actions.
    </p>

    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    @if(session('error')) <x-ui.alert variant="error" message="{{ session('error') }}" /> @endif

    @php
        $totalItems = $kit->assets->count() + $kit->components->count() + $kit->licenses->count() + $kit->consumables->count();
    @endphp

    {{-- Entity Header Hero Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 lg:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-xs">
                    <i data-lucide="box" class="h-7 w-7"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $kit->name }}</h2>
                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-[11px] font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                            {{ $totalItems }} Bundled Items
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-medium">
                        {{ $kit->description ?: 'Pre-defined equipment kit package for rapid employee onboarding.' }}
                    </p>
                </div>
            </div>

            {{-- Action Toolbar --}}
            <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                <a href="{{ route('kits.checkout-form', $kit) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-600 active:scale-[0.98] transition">
                    <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                    Checkout Kit
                </a>
                <a href="{{ route('kits.edit', $kit) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                    <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
                    Edit
                </a>
                <a href="{{ route('kits.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-xs hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                    <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
                    Back to Kits
                </a>
            </div>
        </div>
    </div>

    {{-- 3-Column Layout: Left (1 Col Context Sidebar) vs Right (2 Col Content Hub) --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        {{-- Left Column: Kit Specification & Package Summary --}}
        <div class="space-y-4 lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Package Breakdown</h3>
                </div>

                <dl class="mt-4 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Hardware Assets:</dt>
                        <dd class="font-bold text-gray-900 dark:text-white tabular-nums">{{ $kit->assets->count() }} items</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Components / Parts:</dt>
                        <dd class="font-bold text-gray-900 dark:text-white tabular-nums">{{ $kit->components->count() }} items</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Software Licenses:</dt>
                        <dd class="font-bold text-gray-900 dark:text-white tabular-nums">{{ $kit->licenses->count() }} seats</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Consumable Supplies:</dt>
                        <dd class="font-bold text-gray-900 dark:text-white tabular-nums">{{ $kit->consumables->count() }} packs</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-2.5 dark:border-gray-800">
                        <dt class="text-gray-400">Created At:</dt>
                        <dd class="font-mono text-gray-800 dark:text-gray-200">{{ $kit->created_at?->format('d M Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Right Column: Bundled Items Hub --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 p-4 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Bundled Items in this Kit</h3>
                    <p class="text-[11px] text-gray-400">Equipment packaged for combined single-click dispatch</p>
                </div>

                @php
                    $groups = [
                        ['label' => 'Hardware Assets', 'items' => $kit->assets, 'icon' => 'monitor', 'show_tag' => true],
                        ['label' => 'Components / Upgrades', 'items' => $kit->components, 'icon' => 'cpu', 'show_tag' => false],
                        ['label' => 'Software Licenses', 'items' => $kit->licenses, 'icon' => 'key', 'show_tag' => false],
                        ['label' => 'Consumable Supplies', 'items' => $kit->consumables, 'icon' => 'package', 'show_tag' => false],
                    ];
                @endphp

                <div class="p-5 sm:p-6 space-y-6">
                    @if($totalItems > 0)
                        @foreach($groups as $group)
                            @if($group['items']->isNotEmpty())
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                                        <i data-lucide="{{ $group['icon'] }}" class="w-4 h-4 text-brand-500"></i>
                                        {{ $group['label'] }} ({{ $group['items']->count() }})
                                    </h4>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach($group['items'] as $item)
                                            <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-gray-950/50">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white shadow-xs dark:bg-gray-900 text-brand-500">
                                                        <i data-lucide="{{ $group['icon'] }}" class="w-4 h-4"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $item->name }}</p>
                                                        @if($group['show_tag'] && $item->asset_tag)
                                                            <p class="text-[11px] font-mono font-medium text-brand-600 dark:text-brand-400">{{ $item->asset_tag }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400 tabular-nums">
                                                    x{{ $item->pivot->qty }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-12 text-gray-400 text-xs">
                            <i data-lucide="box" class="h-8 w-8 mx-auto mb-2 text-gray-300 dark:text-gray-600"></i>
                            No bundled hardware or software assigned to this kit yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
