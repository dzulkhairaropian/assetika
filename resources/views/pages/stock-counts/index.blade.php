@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Stock Counts / Audit Opname" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Run physical stock counts and audit asset inventory.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Card Header: Toolbar --}}
            <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('stock-counts.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-gray-400">
                                <i data-feather="search" class="w-4 h-4"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                                class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        @if(request()->filled('search'))
                            <a href="{{ route('stock-counts.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                        @endif
                    </form>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('stock-counts.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            <i data-feather="plus" class="w-4 h-4"></i>
                            Start Stock Count
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
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Name</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Location</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Start Date</p>
                                </div>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <div class="flex items-center justify-end">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($stockCounts as $sc)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 sm:px-6">
                                    <a href="{{ route('stock-counts.show', $sc) }}" class="text-theme-sm font-medium text-brand-500 hover:underline">{{ $sc->name }}</a>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $sc->location?->name ?? 'All Locations' }}</span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $sc->status === 'completed' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' }}">{{ ucfirst(str_replace('_', ' ', $sc->status)) }}</span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $sc->start_date?->format('d M Y') }}</span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center justify-end gap-3 text-gray-500">
                                        <a href="{{ route('stock-counts.show', $sc) }}" class="transition-colors hover:text-brand-500" title="View">
                                            <i data-feather="eye" class="w-5 h-5"></i>
                                        </a>
                                        <form action="{{ route('stock-counts.destroy', $sc) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="transition-colors hover:text-error-500" title="Delete">
                                                <i data-feather="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-feather="clipboard" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No stock count sessions found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Start a stock count session to audit physical inventory.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                {{ $stockCounts->links() }}
            </div>
        </div>
    </div>
@endsection