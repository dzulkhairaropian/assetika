@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="EULA Templates" />

    <!-- Stat Card Header -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-feather="file-text" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Total Templates</span>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $eulas->total() }}</h4>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10">
                    <i data-feather="shield" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Compliance</span>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">Terms Agreement</h4>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-500 dark:bg-purple-500/10">
                    <i data-feather="check-square" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Scope</span>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">Global / Category</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form action="{{ route('eulas.index') }}" method="GET" class="flex items-center gap-3">
            <div class="relative flex items-center"><span class="absolute left-3 text-gray-400"><i data-feather="search" class="w-4 h-4"></i></span><input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900"></div>
            @if(request()->filled('search')) <a href="{{ route('eulas.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a> @endif
        </form>
        <a href="{{ route('eulas.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"><i data-feather="plus" class="w-4 h-4"></i> Add EULA</a>
    </div>
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-6 py-3 text-xs text-left text-gray-500">Title</th><th class="px-6 py-3 text-xs text-left text-gray-500">Category</th><th class="px-6 py-3 text-xs text-left text-gray-500">Actions</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                @forelse($eulas as $e)
                <tr><td class="px-6 py-3.5 text-sm font-medium">{{ $e->title }}</td><td class="px-6 py-3.5 text-sm text-gray-500">{{ $e->category?->name ?? 'Global' }}</td>
                    <td class="px-6 py-3.5"><div class="flex items-center gap-3 text-gray-500"><a href="{{ route('eulas.edit', $e) }}" class="hover:text-brand-500"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                    <form action="{{ route('eulas.destroy', $e) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">@csrf @method('DELETE')<button class="hover:text-error-500"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button></form></div></td></tr>
                @empty
                <tr><td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">No EULA templates found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-6 border-t">{{ $eulas->links() }}</div>
    </div>
</div>
@endsection
