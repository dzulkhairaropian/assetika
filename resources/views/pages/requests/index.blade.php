@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Asset Requests" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Review, approve, or reject user asset & equipment requests.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        {{-- Card Header: Tabs + Toolbar --}}
        <div class="space-y-3 border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
            {{-- Quick Filter Tabs --}}
            @php
                $currentTab = request('tab');
                $tabFilters = array_filter(request()->except(['page', 'tab']));
                $tabs = [
                    ['key' => null,        'label' => 'All Requests', 'count' => $requestSummary['total'],      'icon' => 'layers',       'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                    ['key' => 'hardware',  'label' => 'Hardware',     'count' => $requestSummary['hardware'],   'icon' => 'monitor',      'badge' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                    ['key' => 'consumable','label' => 'Consumables',  'count' => $requestSummary['consumable'], 'icon' => 'package',      'badge' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
                    ['key' => 'license',   'label' => 'Licenses',     'count' => $requestSummary['license'],    'icon' => 'key',          'badge' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400'],
                ];
            @endphp
            <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5">
                @foreach($tabs as $tab)
                    @php
                        $isActive = $currentTab === $tab['key'];
                        $tabUrl = route('asset-requests.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                    @endphp
                    <a href="{{ $tabUrl }}"
                       class="flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
                        <i data-feather="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                        {{ $tab['label'] }}
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('asset-requests.index') }}" method="GET" class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
                    <div class="relative flex items-center">
                        <span class="absolute left-2.5 text-gray-400">
                            <i data-feather="search" class="w-3.5 h-3.5"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search requester or item..."
                            class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                    </div>

                    @php $activeCount = request('status') ? 1 : 0; @endphp

                    <div class="relative" @click.away="filterOpen = false">
                        <button type="button" @click="filterOpen = !filterOpen"
                            class="relative inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-feather="sliders" class="w-3.5 h-3.5"></i>
                            Filters
                            @if($activeCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[18px] h-4 px-1 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                            @endif
                        </button>

                        <div x-show="filterOpen"
                            x-transition
                            class="absolute left-0 top-full z-50 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            style="display: none;">
                            <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 dark:border-gray-800">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Filter Options</h4>
                                <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i data-feather="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">Request Status</label>
                                <select name="status"
                                    class="h-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <a href="{{ route('asset-requests.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                    Clear All Filters
                                </a>
                                <button type="submit" @click="filterOpen = false"
                                    class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-2">
                    <a href="{{ route('requestable-assets.catalog') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="grid" class="w-3.5 h-3.5"></i>
                        Request Catalog
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">User / Requester</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Requested Item</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Date Requested</th>
                        <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions / Process</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($requests as $r)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                {{ $r->user?->name ?? 'Unknown User' }}
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $r->requestable_name }}</span>
                                    <span class="text-[11px] text-gray-400">Type: {{ $r->requestable_type_label }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <x-ui.badge :color="$r->status_badge_color" variant="light" size="sm" :featherIcon="$r->status_badge_icon">{{ ucfirst($r->status) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $r->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-2.5 text-end">
                                @if($r->status === 'pending')
                                    <form action="{{ route('asset-requests.process', $r) }}" method="POST" class="inline-flex items-center gap-1.5 justify-end">
                                        @csrf
                                        <button name="action" value="approve" type="submit"
                                            class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700 transition">
                                            <i data-feather="check" class="w-3 h-3"></i> Approve
                                        </button>
                                        <button name="action" value="reject" type="submit"
                                            class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-red-700 transition">
                                            <i data-feather="x" class="w-3 h-3"></i> Reject
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-gray-400 italic">Processed by {{ $r->processedBy?->name ?? 'System' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-1.5">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="inbox" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No asset requests found</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Asset requests submitted by users will appear here for processing.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
