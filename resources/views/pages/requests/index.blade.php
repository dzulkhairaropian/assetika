@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Asset Requests" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Review, approve, or reject user asset & equipment requests.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Card Header: Tabs + Toolbar --}}
        <div class="space-y-4 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            {{-- Quick Filter Tabs --}}
            @php
                $currentTab = request('tab');
                $tabFilters = array_filter(request()->except(['page', 'tab']));
                $tabs = [
                    ['key' => null,        'label' => 'All Requests', 'count' => $requestSummary['total'],      'icon' => 'layers',       'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                    ['key' => 'hardware',  'label' => 'Hardware',     'count' => $requestSummary['hardware'],   'icon' => 'monitor',      'badge' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                    ['key' => 'consumable','label' => 'Consumables',  'count' => $requestSummary['consumable'], 'icon' => 'package',      'badge' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
                    ['key' => 'license',   'label' => 'Licenses',     'count' => $requestSummary['license'],    'icon' => 'key',          'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                ];
            @endphp
            <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5">
                @foreach($tabs as $tab)
                    @php
                        $isActive = $currentTab === $tab['key'];
                        $tabUrl = route('asset-requests.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
                    @endphp
                    <a href="{{ $tabUrl }}"
                       class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all {{ $isActive ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
                        <i data-feather="{{ $tab['icon'] }}" class="w-4 h-4"></i>
                        {{ $tab['label'] }}
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('asset-requests.index') }}" method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-gray-400">
                            <i data-feather="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search requester or item..."
                            class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    @php $activeCount = request('status') ? 1 : 0; @endphp

                    <div class="relative" @click.away="filterOpen = false">
                        <button type="button" @click="filterOpen = !filterOpen"
                            class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 transition">
                            <i data-feather="sliders" class="w-4 h-4"></i>
                            Filters
                            @if($activeCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-brand-500 rounded-full leading-none">{{ $activeCount }}</span>
                            @endif
                        </button>

                        <div x-show="filterOpen"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute left-0 top-full z-50 mt-2 w-72 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                            style="display: none;">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Filter Options</h4>
                                <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i data-feather="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Request Status</label>
                                <select name="status"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                                <a href="{{ route('asset-requests.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">
                                    Clear All Filters
                                </a>
                                <button type="submit" @click="filterOpen = false"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-3">
                    <a href="{{ route('requestable-assets.catalog') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                        <i data-feather="grid" class="w-4 h-4"></i>
                        Request Catalog
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">User / Requester</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requested Item</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date Requested</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center justify-end">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions / Process</p>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse($requests as $r)
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6">
                                <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $r->user?->name ?? 'Unknown User' }}</span>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col">
                                    <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $r->requestable_name }}</span>
                                    <span class="text-xs text-gray-400">Type: {{ $r->requestable_type_label }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @php
                                    $badgeColor = match($r->status) {
                                        'approved' => 'success',
                                        'rejected' => 'error',
                                        default => 'warning',
                                    };
                                @endphp
                                <x-ui.badge :color="$badgeColor" variant="light" size="sm">{{ ucfirst($r->status) }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-500 dark:text-gray-400">
                                {{ $r->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-right">
                                @if($r->status === 'pending')
                                    <form action="{{ route('asset-requests.process', $r) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        <button name="action" value="approve" type="submit"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-600">
                                            <i data-feather="check" class="w-3.5 h-3.5"></i> Approve
                                        </button>
                                        <button name="action" value="reject" type="submit"
                                            class="inline-flex items-center gap-1 rounded-lg bg-error-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-error-600">
                                            <i data-feather="x" class="w-3.5 h-3.5"></i> Reject
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 italic">Processed by {{ $r->processedBy?->name ?? 'System' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="inbox" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No asset requests found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Asset requests submitted by users will appear here for processing.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection