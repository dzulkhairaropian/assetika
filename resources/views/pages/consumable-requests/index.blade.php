@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Consumable Requests" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Review, approve, or reject user consumable stock requests.
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
                $currentTab = request('status');
                $tabFilters = array_filter(request()->except(['page', 'status']));
                $tabs = [
                    ['key' => null,        'label' => 'All Requests', 'count' => $summary['total'],     'icon' => 'layers',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
                    ['key' => 'pending',   'label' => 'Pending',      'count' => $summary['pending'],   'icon' => 'clock',         'badge' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500'],
                    ['key' => 'approved',  'label' => 'Approved',     'count' => $summary['approved'],  'icon' => 'check-circle',  'badge' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                    ['key' => 'completed', 'label' => 'Completed',    'count' => $summary['completed'], 'icon' => 'check',         'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
                    ['key' => 'rejected',  'label' => 'Rejected',     'count' => $summary['rejected'],  'icon' => 'x-circle',      'badge' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'],
                ];
            @endphp
            <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5">
                @foreach($tabs as $tab)
                    @php
                        $isActive = $currentTab === $tab['key'];
                        $tabUrl = route('consumable-requests.index', $tab['key'] ? array_merge($tabFilters, ['status' => $tab['key']]) : $tabFilters);
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
                <form action="{{ route('consumable-requests.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-gray-400">
                            <i data-feather="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search request #, requester..."
                            class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('consumable-requests.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Request #</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requester / Dept</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Requested Items</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p>
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
                    @forelse($requests as $req)
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6 font-mono text-theme-sm font-semibold text-brand-600 dark:text-brand-400">
                                {{ $req->request_number }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col">
                                    <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $req->requester->name ?? 'Unknown' }}</span>
                                    <span class="text-xs text-gray-400">{{ $req->department->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-col gap-1">
                                    @foreach($req->items as $item)
                                        <span class="text-xs text-gray-700 dark:text-gray-300">
                                            • {{ $item->consumable->product->name ?? 'Item' }}
                                            <strong class="text-brand-600 dark:text-brand-400">({{ $item->quantity_requested }}x)</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @php
                                    $isEnum = $req->status instanceof \App\Enums\ConsumableRequestStatus;
                                    $statusLabel = $isEnum ? $req->status->getLabel() : ucfirst($req->status);
                                    $color = $isEnum ? $req->status->getColor() : match((string)$req->status) {
                                        'approved' => 'primary',
                                        'completed' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning',
                                    };
                                    $icon = $isEnum ? $req->status->getIcon() : match((string)$req->status) {
                                        'approved' => 'thumbs-up',
                                        'completed' => 'check',
                                        'rejected' => 'x',
                                        default => 'clock',
                                    };
                                @endphp
                                <x-ui.badge :color="$color" variant="light" size="sm" :featherIcon="$icon">{{ $statusLabel }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-500 dark:text-gray-400">
                                {{ $req->requested_at?->format('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-right">
                                @php
                                    $st = is_object($req->status) ? $req->status->value : $req->status;
                                @endphp
                                @if($st === 'pending')
                                    <form action="{{ route('consumable-requests.process', $req) }}" method="POST" class="inline-flex items-center gap-2">
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
                                    <span class="text-xs text-gray-400 italic">Processed by {{ $req->processedBy?->name ?? 'System' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="package" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No consumable requests found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Consumable stock requests submitted by users will appear here for approval.</p>
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
