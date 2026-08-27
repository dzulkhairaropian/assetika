@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Stock Counts / Audit Opname" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Run physical stock counts and audit asset inventory.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            {{-- Card Header: Toolbar --}}
            <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form action="{{ route('stock-counts.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-gray-400">
                                <i data-feather="search" class="w-3.5 h-3.5"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search stock counts..."
                                class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                        </div>
                        @if(request()->filled('search'))
                            <a href="{{ route('stock-counts.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Clear</a>
                        @endif
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('stock-counts.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-feather="plus" class="w-3.5 h-3.5"></i>
                            Start Stock Count
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Location</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Start Date</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($stockCounts as $sc)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('stock-counts.show', $sc) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ $sc->name }}</a>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ $sc->location?->name ?? 'All Locations' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium {{ $sc->status === 'completed' ? 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400' }}">{{ ucfirst(str_replace('_', ' ', $sc->status)) }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $sc->start_date?->format('d M Y') }}
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <div class="flex items-center justify-end gap-2 text-gray-500">
                                        <a href="{{ route('stock-counts.show', $sc) }}" class="hover:text-brand-600 transition" title="View">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route('stock-counts.destroy', $sc) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-red-600 transition" title="Delete">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-feather="clipboard" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No stock count sessions found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Start a stock count session to audit physical inventory.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $stockCounts->links() }}
            </div>
        </div>
    </div>
@endsection
