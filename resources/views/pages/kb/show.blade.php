@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Knowledge Base Article" :items="[['label' => 'Knowledge Base', 'url' => route('kb.index')], ['label' => $article->title]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Detailed article information and solution guidance.
    </p>

    {{-- Header Card --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3.5">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-feather="book-open" class="h-6 w-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $article->title }}</h2>
                        <x-ui.badge color="primary" variant="light" size="sm">{{ $article->category->name }}</x-ui.badge>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Published on {{ $article->created_at->format('M d, Y') }} • {{ number_format($article->views_count) }} views</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('kb.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                    <i data-feather="arrow-left" class="h-3.5 w-3.5"></i>
                    Back to KB
                </a>
            </div>
        </div>
    </div>

    {{-- Content Card --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
            <i data-feather="file-text" class="w-4 h-4 text-brand-500"></i>
            Article Solution
        </h3>

        <div class="prose dark:prose-invert max-w-none text-xs leading-relaxed text-gray-700 dark:text-gray-300">
            {!! nl2br(e($article->content)) !!}
        </div>
    </div>
</div>
@endsection
