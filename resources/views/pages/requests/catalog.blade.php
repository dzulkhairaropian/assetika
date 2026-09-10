@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ catalogTab: 'hardware' }">
    <x-common.page-breadcrumb pageTitle="Request Catalog" :items="[['label' => 'Asset Requests', 'url' => route('asset-requests.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Browse available IT items, peripherals, and consumables, then submit a request for approval.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Main Catalog Table Card --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                {{-- Card Header: Pill Tabs --}}
                <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                    <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5">
                        <button @click="catalogTab = 'hardware'"
                            :class="catalogTab === 'hardware' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                            class="flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all">
                            <i data-lucide="hard-drive" class="w-3.5 h-3.5"></i>
                            Hardware Categories
                            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $categories->count() }}</span>
                        </button>
                        <button @click="catalogTab = 'consumable'"
                            :class="catalogTab === 'consumable' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                            class="flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all">
                            <i data-lucide="package" class="w-3.5 h-3.5"></i>
                            Consumables
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">{{ $consumables->count() }}</span>
                        </button>
                        <button @click="catalogTab = 'license'"
                            :class="catalogTab === 'license' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                            class="flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all">
                            <i data-lucide="key" class="w-3.5 h-3.5"></i>
                            Licenses
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-semibold text-green-600 dark:bg-green-500/15 dark:text-green-400">{{ $licenses->count() }}</span>
                        </button>
                    </div>
                </div>

                {{-- Hardware Categories Table --}}
                <div x-show="catalogTab === 'hardware'" class="max-w-full overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category Name</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Request Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($categories as $cat)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                        {{ $cat->name }}
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                        IT Hardware Category
                                    </td>
                                    <td class="px-4 py-2.5 text-end">
                                        <form action="{{ route('asset-requests.store') }}" method="POST" class="inline-flex items-center gap-1.5 justify-end">
                                            @csrf
                                            <input type="hidden" name="requestable_type" value="category">
                                            <input type="hidden" name="requestable_id" value="{{ $cat->id }}">
                                            <input type="number" name="qty" min="1" max="50" value="1"
                                                class="h-8 w-14 rounded-lg border border-gray-200 bg-gray-50 px-2 text-center text-xs font-semibold text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"
                                                title="Quantity">
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                                                <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Request
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">
                                        No hardware categories available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Consumables Table --}}
                <div x-show="catalogTab === 'consumable'" class="max-w-full overflow-x-auto" style="display: none;">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Product</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category / Brand</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Stock Status</th>
                                <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Request Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($consumables as $con)
                                @php
                                    $conOut = $con->quantity <= 0;
                                    $conLow = !$conOut && $con->quantity <= ($con->low_threshold ?? 5);
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-2.5">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $con->product?->name ?? 'Consumable' }}</span>
                                            <span class="text-[11px] text-gray-400">Model: {{ $con->product?->product_type ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex flex-col">
                                            <span class="text-gray-800 dark:text-gray-200">{{ $con->product?->category?->name ?? '-' }}</span>
                                            <span class="text-[11px] text-gray-400">{{ $con->product?->brand?->name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if($conOut)
                                            <x-ui.badge color="danger" variant="light" size="sm" featherIcon="x-circle">Out of Stock</x-ui.badge>
                                        @elseif($conLow)
                                            <x-ui.badge color="warning" variant="light" size="sm" featherIcon="alert-circle">Low: {{ $con->quantity }} left</x-ui.badge>
                                        @else
                                            <x-ui.badge color="success" variant="light" size="sm" featherIcon="check-circle">{{ $con->quantity }} available</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-end">
                                        @if($conOut)
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                                <i data-lucide="circle-x" class="w-3.5 h-3.5"></i> Unavailable
                                            </span>
                                        @else
                                            <form action="{{ route('asset-requests.store') }}" method="POST" class="inline-flex items-center gap-1.5 justify-end">
                                                @csrf
                                                <input type="hidden" name="requestable_type" value="consumable">
                                                <input type="hidden" name="requestable_id" value="{{ $con->id }}">
                                                <input type="number" name="qty" min="1" max="50" value="1"
                                                    class="h-8 w-14 rounded-lg border border-gray-200 bg-gray-50 px-2 text-center text-xs font-semibold text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"
                                                    title="Quantity">
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                                                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Request
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">
                                        No consumables available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Licenses Table --}}
                <div x-show="catalogTab === 'license'" class="max-w-full overflow-x-auto" style="display: none;">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">License Name</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Manufacturer</th>
                                <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Seats</th>
                                <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Request Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($licenses as $lic)
                                @php
                                    $licAvail = $lic->availableSeats();
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-2.5">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $lic->name }}</span>
                                            <span class="text-[11px] text-gray-400 font-mono truncate max-w-[200px]">{{ $lic->product_key ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                        {{ $lic->manufacturer ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if($licAvail <= 0)
                                            <x-ui.badge color="danger" variant="light" size="sm">All Seats Used</x-ui.badge>
                                        @else
                                            <x-ui.badge color="success" variant="light" size="sm">{{ $licAvail }} of {{ $lic->seats }} seats</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-end">
                                        @if($licAvail <= 0)
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                                <i data-lucide="circle-x" class="w-3.5 h-3.5"></i> Unavailable
                                            </span>
                                        @else
                                            <form action="{{ route('asset-requests.store') }}" method="POST" class="inline-flex items-center gap-1.5 justify-end">
                                                @csrf
                                                <input type="hidden" name="requestable_type" value="license">
                                                <input type="hidden" name="requestable_id" value="{{ $lic->id }}">
                                                <input type="number" name="qty" min="1" max="50" value="1"
                                                    class="h-8 w-14 rounded-lg border border-gray-200 bg-gray-50 px-2 text-center text-xs font-semibold text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"
                                                    title="Quantity">
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                                                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Request
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">
                                        No licenses available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- My Recent Requests Sidebar --}}
        <div class="lg:col-span-1">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                        <i data-lucide="clock" class="w-4 h-4 text-brand-500"></i>
                        My Recent Requests
                    </h3>
                </div>
                <div class="p-3.5 sm:p-4 space-y-2.5">
                    @forelse($myRequests as $req)
                        @php
                            $reqColor = $req->status_badge_color;
                            $reqIcon = $req->status_badge_icon;
                            $typeColor = $req->requestable_type_label === 'Hardware' ? 'primary' : 'info';
                        @endphp
                        <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950 hover:bg-gray-50 transition">
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate text-xs font-semibold text-gray-900 dark:text-white" title="{{ $req->requestable_name }}">{{ $req->requestable_name }}</span>
                                <x-ui.badge :color="$reqColor" variant="light" size="sm" :featherIcon="$reqIcon">{{ ucfirst($req->status) }}</x-ui.badge>
                            </div>
                            <div class="mt-1.5 flex items-center gap-2">
                                <x-ui.badge :color="$typeColor" variant="light" size="sm">{{ $req->requestable_type_label }}</x-ui.badge>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Qty: {{ $req->qty ?? 1 }}</span>
                                <span class="ml-auto text-[10px] text-gray-400">{{ $req->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                <i data-lucide="inbox" class="w-5 h-5"></i>
                            </div>
                            <p class="mt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">No requests submitted yet</p>
                            <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">Request an item from the catalog to track it here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
