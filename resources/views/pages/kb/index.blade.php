@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Knowledge Base" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Browse documentation center and self-service solution articles.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    {{-- Unified Table/Card Container --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        {{-- Card Header --}}
        <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('kb.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <div class="relative flex items-center">
                        <span class="absolute left-2.5 text-gray-400">
                            <i data-feather="search" class="w-3.5 h-3.5"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..."
                            class="h-9 w-60 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
                    </div>

                    <div class="relative flex items-center">
                        <select name="category_id" onchange="this.form.submit()"
                            class="h-9 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} ({{ $cat->articles_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(request()->filled('search') || request()->filled('category_id'))
                        <a href="{{ route('kb.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Clear Filters</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Title</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Author</th>
                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Views</th>
                        <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($articles as $article)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                            <td class="px-4 py-2.5">
                                <a href="{{ route('kb.show', $article) }}" class="font-semibold text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">
                                    {{ $article->title }}
                                </a>
                            </td>
                            <td class="px-4 py-2.5">
                                <x-ui.badge color="primary" variant="light" size="sm">
                                    {{ $article->category->name }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                {{ $article->author->name ?? 'Admin' }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                {{ number_format($article->views_count) }}
                            </td>
                            <td class="px-4 py-2.5 text-end">
                                <div class="flex items-center justify-end gap-2 text-gray-500">
                                    <a href="{{ route('kb.show', $article) }}" class="hover:text-brand-600 transition" title="View Article">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-1.5">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                        <i data-feather="inbox" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No Knowledge Base articles found</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Try adjusting your search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
