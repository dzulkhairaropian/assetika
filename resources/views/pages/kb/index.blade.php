@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Knowledge Base" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Browse documentation center and self-service solution articles.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    {{-- Unified Table/Card Container --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Card Header --}}
        <div class="space-y-4 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('kb.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-gray-400">
                            <i data-feather="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..."
                            class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    <div class="relative flex items-center">
                        <select name="category_id" onchange="this.form.submit()"
                            class="h-10 rounded-lg border border-gray-300 bg-white px-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} ({{ $cat->articles_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(request()->filled('search') || request()->filled('category_id'))
                        <a href="{{ route('kb.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear Filters</a>
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
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Title</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Category</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Author</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Views</p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6 text-right">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Action</p>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($articles as $article)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 sm:px-6">
                                <a href="{{ route('kb.show', $article) }}" class="font-medium text-gray-800 hover:text-brand-500 dark:text-white/90 dark:hover:text-brand-400">
                                    {{ $article->title }}
                                </a>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge color="primary" variant="light" size="sm">
                                    {{ $article->category->name }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-600 dark:text-gray-400">
                                {{ $article->author->name ?? 'Admin' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($article->views_count) }}
                            </td>
                            <td class="px-5 py-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-3 text-gray-500">
                                    <a href="{{ route('kb.show', $article) }}" class="transition-colors hover:text-brand-500" title="View Article">
                                        <i data-feather="eye" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-gray-800/50">
                                        <i data-feather="inbox" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No Knowledge Base articles found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
