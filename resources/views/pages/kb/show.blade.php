@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Knowledge Base Article" :items="[['label' => 'Knowledge Base', 'url' => route('kb.index')], ['label' => $article->title]]" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Detailed article information and solution guidance.
    </p>

    {{-- Header Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-feather="book-open" class="h-8 w-8"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $article->title }}</h2>
                        <x-ui.badge color="primary" variant="light" size="sm">{{ $article->category->name }}</x-ui.badge>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Published on {{ $article->created_at->format('M d, Y') }} • {{ number_format($article->views_count) }} views</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('kb.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i data-feather="arrow-left" class="h-4 w-4"></i>
                    Back to KB
                </a>
            </div>
        </div>
    </div>

    {{-- Content Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
            <i data-feather="file-text" class="w-5 h-5 text-brand-500"></i>
            Article Solution
        </h3>

        <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed text-gray-700 dark:text-gray-300">
            {!! nl2br(e($article->content)) !!}
        </div>
    </div>
</div>
@endsection
