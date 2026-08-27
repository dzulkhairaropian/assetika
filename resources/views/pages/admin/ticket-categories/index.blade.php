@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ openAddCat: false, openAddSub: false, selectedCatId: null, selectedCatName: '' }">
    <x-common.page-breadcrumb pageTitle="Ticket Categories & Subcategories" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-sm font-bold text-gray-800 dark:text-white/90">ITSM Ticket Categories</h2>
            <p class="text-xs text-gray-500">Manage Ticket Categories and Subcategories specifically for Support Ticketing.</p>
        </div>
        <button @click="openAddCat = true" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">
            <i data-feather="plus" class="w-4 h-4"></i> Add Category
        </button>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl border border-success-200 bg-success-50 text-success-700 dark:border-success-900/30 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6">
        @forelse($categories as $category)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center rounded-lg bg-brand-50 px-3 py-1 text-xs font-mono font-bold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            {{ $category->code }}
                        </span>
                        <h3 class="text-base font-bold text-gray-800 dark:text-white">{{ $category->name }}</h3>
                        <span class="text-xs text-gray-400">({{ $category->subcategories->count() }} subcategories)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="selectedCatId = {{ $category->id }}; selectedCatName = '{{ addslashes($category->name) }}'; openAddSub = true" class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:underline">
                            + Add Subcategory
                        </button>
                        <form action="{{ route('admin.ticket-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-gray-400 hover:text-error-500" title="Delete">
                                <i data-feather="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Subcategories List -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @forelse($category->subcategories as $sub)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-900/30">
                            <div>
                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $sub->name }}</p>
                                <p class="text-[10px] text-gray-400 font-mono">{{ $sub->code }}</p>
                            </div>
                            <form action="{{ route('admin.ticket-subcategories.destroy', $sub) }}" method="POST" onsubmit="return confirm('Delete subcategory?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-error-500">
                                    <i data-feather="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 col-span-full">No subcategories defined for this category.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-gray-500 dark:border-gray-800 dark:bg-white/[0.03]">
                No Ticket Categories found. Click "+ Add Category" to create one.
            </div>
        @endforelse
    </div>

    <!-- Modal Add Category -->
    <div x-show="openAddCat" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Add Ticket Category</h3>
            <form action="{{ route('admin.ticket-categories.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Hardware, Software" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Code (Optional)</label>
                    <input type="text" name="code" placeholder="e.g. HW, SW" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"/>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openAddCat = false" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Add Subcategory -->
    <div x-show="openAddSub" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Add Subcategory</h3>
            <p class="text-[11px] text-gray-500 mb-4">Under category: <span class="font-semibold text-brand-600" x-text="selectedCatName"></span></p>
            <form action="{{ route('admin.ticket-subcategories.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <input type="hidden" name="ticket_category_id" :value="selectedCatId"/>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Subcategory Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Laptop / Notebook, Printer" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Code (Optional)</label>
                    <input type="text" name="code" placeholder="e.g. PRINTER" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-xs text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"/>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openAddSub = false" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
