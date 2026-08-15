@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ catalogTab: 'hardware' }">
    <x-common.page-breadcrumb pageTitle="Request Catalog" :items="[['label' => 'Asset Requests', 'url' => route('asset-requests.index')]]" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Browse available IT items, peripherals, and consumables, then submit a request for approval.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Catalog Table Card --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                {{-- Card Header: Pill Tabs --}}
                <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
                    <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5">
                        <button @click="catalogTab = 'hardware'"
                            :class="catalogTab === 'hardware' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                            class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                            <i data-feather="hard-drive" class="w-4 h-4"></i>
                            Hardware Categories
                            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $categories->count() }}</span>
                        </button>
                        <button @click="catalogTab = 'consumable'"
                            :class="catalogTab === 'consumable' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                            class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                            <i data-feather="package" class="w-4 h-4"></i>
                            Consumables
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">{{ $consumables->count() }}</span>
                        </button>
                        <button @click="catalogTab = 'license'"
                            :class="catalogTab === 'license' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                            class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                            <i data-feather="key" class="w-4 h-4"></i>
                            Licenses
                            <span class="rounded-full bg-success-50 px-2 py-0.5 text-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500">{{ $licenses->count() }}</span>
                        </button>
                    </div>
                </div>

                {{-- Hardware Categories Table --}}
                <div x-show="catalogTab === 'hardware'" class="max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Category Name</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Type</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center justify-end">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Request Action</p>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                            @forelse($categories as $cat)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 sm:px-6">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">IT Hardware Category</span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6 text-right">
                                        <form action="{{ route('asset-requests.store') }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="requestable_type" value="category">
                                            <input type="hidden" name="requestable_id" value="{{ $cat->id }}">
                                            <input type="number" name="qty" min="1" max="50" value="1"
                                                class="w-16 rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-center text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                title="Quantity">
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-brand-600">
                                                <i data-feather="shopping-cart" class="w-3.5 h-3.5"></i> Request
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                <i data-feather="hard-drive" class="w-6 h-6"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No hardware categories available</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Consumables Table --}}
                <div x-show="catalogTab === 'consumable'" class="max-w-full overflow-x-auto" style="display: none;">
                    <table class="min-w-full">
                        <thead class="border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Product</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Category / Brand</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Stock Status</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center justify-end">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Request Action</p>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                            @forelse($consumables as $con)
                                @php
                                    $conOut = $con->quantity <= 0;
                                    $conLow = !$conOut && $con->quantity <= ($con->low_threshold ?? 5);
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex flex-col">
                                            <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $con->product?->name ?? 'Consumable' }}</span>
                                            <span class="text-xs text-gray-400">Model: {{ $con->product?->product_type ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex flex-col">
                                            <span class="text-theme-sm text-gray-700 dark:text-gray-300">{{ $con->product?->category?->name ?? '-' }}</span>
                                            <span class="text-xs text-gray-400">{{ $con->product?->brand?->name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        @if($conOut)
                                            <x-ui.badge color="error" variant="light" size="sm">Out of Stock</x-ui.badge>
                                        @elseif($conLow)
                                            <x-ui.badge color="warning" variant="light" size="sm">Low: {{ $con->quantity }} left</x-ui.badge>
                                        @else
                                            <x-ui.badge color="success" variant="light" size="sm">{{ $con->quantity }} available</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 sm:px-6 text-right">
                                        @if($conOut)
                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3.5 py-2 text-xs font-semibold text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                                <i data-feather="x-circle" class="w-3.5 h-3.5"></i> Unavailable
                                            </span>
                                        @else
                                            <form action="{{ route('asset-requests.store') }}" method="POST" class="inline-flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="requestable_type" value="consumable">
                                                <input type="hidden" name="requestable_id" value="{{ $con->id }}">
                                                <input type="number" name="qty" min="1" max="50" value="1"
                                                    class="w-16 rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-center text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                    title="Quantity">
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-brand-600">
                                                    <i data-feather="shopping-cart" class="w-3.5 h-3.5"></i> Request
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                <i data-feather="package" class="w-6 h-6"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No consumables available</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Licenses Table --}}
                <div x-show="catalogTab === 'license'" class="max-w-full overflow-x-auto" style="display: none;">
                    <table class="min-w-full">
                        <thead class="border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">License Name</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Manufacturer</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Seats</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center justify-end">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Request Action</p>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                            @forelse($licenses as $lic)
                                @php
                                    $licAvail = $lic->availableSeats();
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex flex-col">
                                            <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $lic->name }}</span>
                                            <span class="text-xs text-gray-400 font-mono truncate max-w-[200px]">{{ $lic->product_key ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $lic->manufacturer ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        @if($licAvail <= 0)
                                            <x-ui.badge color="error" variant="light" size="sm">All Seats Used</x-ui.badge>
                                        @else
                                            <x-ui.badge color="success" variant="light" size="sm">{{ $licAvail }} of {{ $lic->seats }} seats</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 sm:px-6 text-right">
                                        @if($licAvail <= 0)
                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3.5 py-2 text-xs font-semibold text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                                <i data-feather="x-circle" class="w-3.5 h-3.5"></i> Unavailable
                                            </span>
                                        @else
                                            <form action="{{ route('asset-requests.store') }}" method="POST" class="inline-flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="requestable_type" value="license">
                                                <input type="hidden" name="requestable_id" value="{{ $lic->id }}">
                                                <input type="number" name="qty" min="1" max="50" value="1"
                                                    class="w-16 rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-center text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                    title="Quantity">
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-brand-600">
                                                    <i data-feather="shopping-cart" class="w-3.5 h-3.5"></i> Request
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                <i data-feather="key" class="w-6 h-6"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No licenses available</p>
                                        </div>
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
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
                    <h3 class="flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-feather="clock" class="w-5 h-5 text-brand-500"></i>
                        My Recent Requests
                    </h3>
                </div>
                <div class="p-4 sm:p-5 space-y-3">
                    @forelse($myRequests as $req)
                        @php
                            $reqColor = match($req->status) {
                                'approved' => 'success',
                                'rejected' => 'error',
                                default => 'warning',
                            };
                            $typeColor = $req->requestable_type_label === 'Hardware' ? 'primary' : 'info';
                        @endphp
                        <div class="rounded-xl border border-gray-100 p-3.5 dark:border-gray-800 hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate text-sm font-semibold text-gray-800 dark:text-white" title="{{ $req->requestable_name }}">{{ $req->requestable_name }}</span>
                                <x-ui.badge :color="$reqColor" variant="light" size="sm">{{ ucfirst($req->status) }}</x-ui.badge>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <x-ui.badge :color="$typeColor" variant="light" size="sm">{{ $req->requestable_type_label }}</x-ui.badge>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Qty: {{ $req->qty ?? 1 }}</span>
                                <span class="ml-auto text-[10px] text-gray-400">{{ $req->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                <i data-feather="inbox" class="w-6 h-6"></i>
                            </div>
                            <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">No requests submitted yet</p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Request an item from the catalog to track it here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
