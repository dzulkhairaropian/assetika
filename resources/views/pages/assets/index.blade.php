@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{
        selectedAssets: [],
        selectAll: false,
        toggleAll() {
            this.selectAll = !this.selectAll;
            if (this.selectAll) {
                this.selectedAssets = Array.from(document.querySelectorAll('.asset-checkbox')).map(el => JSON.parse(el.dataset.asset));
            } else {
                this.selectedAssets = [];
            }
        },
        toggleAsset(asset) {
            const index = this.selectedAssets.findIndex(a => a.id === asset.id);
            if (index > -1) {
                this.selectedAssets.splice(index, 1);
            } else {
                this.selectedAssets.push(asset);
            }
            this.selectAll = this.selectedAssets.length === document.querySelectorAll('.asset-checkbox').length;
        }
    }">
        <x-common.page-breadcrumb pageTitle="Asset Management" />

        <!-- Inventory Summary Widgets -->
        <div class="grid grid-cols-5 gap-3 min-w-0 overflow-x-auto">
            <!-- Total Assets -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Assets</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ $inventorySummary['total'] }}</h4>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">All registered IT hardware assets</p>
            </div>

            <!-- Ready to Deploy -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Ready to Deploy</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ $inventorySummary['ready'] }}</h4>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">Available assets for immediate deployment</p>
            </div>

            <!-- Deployed -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Deployed</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ $inventorySummary['deployed'] }}</h4>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">Assets currently assigned to users</p>
            </div>

            <!-- Broken -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Broken / Defective</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ $inventorySummary['broken'] }}</h4>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">Assets marked as broken or disposed</p>
            </div>

            <!-- In Service -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">In Service</span>
                        <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ $inventorySummary['in_service'] }}</h4>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">Assets marked as in service or under maintenance</p>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('assets.index') }}" method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search assets..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                </div>

                @php
                    $activeCount = 0;
                    if (request('status_id')) $activeCount++;
                    if (request('category_id')) $activeCount++;
                    if (request('product_id')) $activeCount++;
                    if (request('department_id')) $activeCount++;
                    if (request('location_id')) $activeCount++;
                    if (request('aging')) $activeCount++;
                @endphp

                <div class="relative" @click.away="filterOpen = false">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 transition">
                        <svg class="stroke-current fill-white dark:fill-gray-900" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5" />
                            <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5" />
                        </svg>
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
                        class="absolute left-0 top-full z-50 mt-2 w-[520px] rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                        style="display: none;">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Filter Options</h4>
                            <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                                <select name="status_id"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                                <select name="category_id"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Product</label>
                                <select name="product_id"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <option value="">All Products</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>{{ $prod->brand->name ?? '' }} - {{ $prod->model }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Department</label>
                                <select name="department_id"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}{{ $dept->company ? ' - ' . $dept->company->name : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Location</label>
                                <select name="location_id"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Asset Age</label>
                                <select name="aging"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <option value="">All Aging</option>
                                    <option value="0-1" {{ request('aging') == '0-1' ? 'selected' : '' }}>&lt; 1 Year</option>
                                    <option value="1-2" {{ request('aging') == '1-2' ? 'selected' : '' }}>1 - 2 Years</option>
                                    <option value="2-3" {{ request('aging') == '2-3' ? 'selected' : '' }}>2 - 3 Years</option>
                                    <option value="3-4" {{ request('aging') == '3-4' ? 'selected' : '' }}>3 - 4 Years</option>
                                    <option value="4+" {{ request('aging') == '4+' ? 'selected' : '' }}>&gt; 4 Years</option>
                                    <option value="fully_depreciated" {{ request('aging') == 'fully_depreciated' ? 'selected' : '' }}>Fully Depreciated</option>
                                    <option value="no_depreciation" {{ request('aging') == 'no_depreciation' ? 'selected' : '' }}>No Depreciation</option>
                                    <option value="warranty_expired" {{ request('aging') == 'warranty_expired' ? 'selected' : '' }}>Warranty Expired</option>
                                    <option value="warranty_30d" {{ request('aging') == 'warranty_30d' ? 'selected' : '' }}>Warranty Expiring (30 Days)</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Depreciation Profile</label>
                                <select name="depreciation_id"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <option value="">All Profiles</option>
                                    @foreach($depreciations as $depr)
                                        <option value="{{ $depr->id }}" {{ request('depreciation_id') == $depr->id ? 'selected' : '' }}>{{ $depr->name }} ({{ $depr->months }}m)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ route('assets.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">
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
                <a href="{{ route('assets.export', request()->query()) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i data-feather="download" class="w-4 h-4 text-gray-500"></i>
                    Export Excel
                </a>
                <a href="{{ route('assets.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                    <i data-feather="plus" class="w-4 h-4"></i>
                    Add Asset
                </a>
            </div>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <!-- Bulk Action Form -->
        <form id="bulkDeleteForm" action="{{ route('assets.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="asset in selectedAssets" :key="asset.id">
                <input type="hidden" name="ids[]" :value="asset.id">
            </template>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedAssets.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                <span class="text-sm font-medium text-brand-700 dark:text-brand-400">
                    <span x-text="selectedAssets.length"></span> assets selected.
                </span>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 dark:text-gray-400">Copies per asset:</label>
                    <input type="number" id="labelCopies" min="1" max="10" value="1"
                        class="w-16 rounded-lg border border-gray-300 bg-white dark:bg-gray-900 dark:border-gray-700 px-2 py-1.5 text-center text-xs font-semibold text-gray-800 dark:text-white">
                </div>
                <button @click="printBulkLabels(selectedAssets, document.getElementById('labelCopies').value)"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-brand-600">
                    <i data-feather="printer" class="w-4 h-4"></i>
                    Print QR Labels
                </button>
                <button @click="exportSelectedAssets(selectedAssets)"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-brand-600">
                    <i data-feather="download" class="w-4 h-4"></i>
                    Export Selected
                </button>
                <button @click="if (confirm('Are you sure you want to delete the selected assets?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">
                                <div class="flex justify-center items-center">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                            <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                <span :class="selectAll ? '' : 'opacity-0'">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Asset Tag / Name</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Product / Serial</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Status</th>
                             <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Depreciation / Value</th>
                             <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Holder</th>
                             <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Age</th>
                             <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($assets as $asset)
                            <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex justify-center items-center">
                                        <label class="flex cursor-pointer items-center select-none">
                                            <div class="relative">
                                                <input type="checkbox"
                                                    :checked="selectedAssets.some(a => a.id === {{ $asset->id }})"
                                                    @click="toggleAsset({
                                                        id: {{ $asset->id }},
                                                        tag: '{{ $asset->asset_tag }}',
                                                        sn: '{{ $asset->serial_number ?? '-' }}',
                                                        company: '{{ optional(optional($asset->department)->company)->name ?? '-' }}',
                                                        url: '{{ rtrim(env('URL_PRODUCTION', 'https://assetika.diastika.co.id'), '/') . '/scan/asset/' . $asset->id }}'
                                                    })"
                                                    class="asset-checkbox sr-only"
                                                    data-asset='{"id":{{ $asset->id }},"tag":"{{ $asset->asset_tag }}","sn":"{{ $asset->serial_number ?? "-" }}","company":"{{ optional(optional($asset->department)->company)->name ?? "-" }}","url":"{{ rtrim(env('URL_PRODUCTION', 'https://assetika.diastika.co.id'), '/') . '/scan/asset/' . $asset->id }}"}'>
                                                <div :class="selectedAssets.some(a => a.id === {{ $asset->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                    class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                                                    <span :class="selectedAssets.some(a => a.id === {{ $asset->id }}) ? '' : 'opacity-0'">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $asset->asset_tag }}</span>
                                        <span class="text-xs text-gray-500">{{ $asset->device_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ $asset->product?->brand?->name ?? '-' }} {{ $asset->product?->model ?? '' }}
                                        </span>
                                        <span class="text-xs text-gray-500">S/N: {{ $asset->serial_number ?? '-' }}</span>
                                        @if($asset->warranty_expiry_date)
                                            @if($asset->warranty_expiry_date->isPast())
                                                <span class="mt-1 inline-flex w-fit items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-[10px] font-medium text-error-700 dark:bg-error-500/10 dark:text-error-400">
                                                    Warranty Expired
                                                </span>
                                            @elseif($asset->warranty_expiry_date->diffInDays() <= 30)
                                                <span class="mt-1 inline-flex w-fit items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                                    Warranty Expiring ({{ $asset->warranty_expiry_date->diffInDays() }}d)
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                 <td class="px-4 sm:px-6 py-3.5">
                                      @php
                                          $statusName = optional($asset->status)->name;
                                          $statusColor = optional($asset->status)->color;
                                      @endphp
                                      <x-ui.badge :color="$statusColor" variant="light" size="sm" :featherIcon="optional($asset->status)->icon">{{ $statusName }}</x-ui.badge>
                                 </td>
                                 <td class="px-4 sm:px-6 py-3.5">
                                     <div class="flex flex-col">
                                         @if($asset->depreciation)
                                             <span class="text-xs font-semibold {{ $asset->bookValue == 0 ? 'text-error-500' : 'text-gray-700 dark:text-gray-300' }}">
                                                 Rp {{ number_format($asset->bookValue, 0, ',', '.') }}
                                             </span>
                                             @if($asset->bookValue == 0)
                                                 <span class="inline-flex items-center gap-1 text-[10px] font-medium text-error-600 dark:text-error-400">
                                                     Fully Depreciated
                                                 </span>
                                             @else
                                                 <span class="text-[10px] text-gray-400">
                                                     {{ $asset->depreciation->name }}
                                                 </span>
                                             @endif
                                         @else
                                             <span class="text-xs text-gray-400 italic">No Depr. Profile</span>
                                         @endif
                                     </div>
                                 </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    @if($asset->isCurrentlyCheckedOut())
                                        <div class="flex flex-col">
                                            <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                                {{ $asset->currentCheckout->checkedOutToUser->name ?? '-' }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                                {{ $asset->currentCheckout->checkedOutToUser->department->name ?? '-' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-theme-sm text-gray-400 italic">Not Assigned</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    @php
                                        $startDate = $asset->release_date ?? $asset->purchase_date;
                                        $ageYears = $startDate ? (int) floor($startDate->diffInDays(now()) / 365.25) : null;
                                    @endphp
                                    @if($ageYears !== null)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $ageYears >= 4 ? 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400' : ($ageYears >= 2 ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400') }}">
                                            {{ $ageYears }}y old {{ $ageYears >= 4 ? '(Review)' : '' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route('assets.show', $asset) }}" class="hover:text-brand-500 transition-colors" title="View Details">
                                            <i data-feather="eye" class="w-5 h-5"></i>
                                        </a>
                                        <a href="{{ route('assets.edit', $asset) }}" class="hover:text-brand-500 transition-colors" title="Edit">
                                            <i data-feather="edit-3" class="w-5 h-5"></i>
                                        </a>

                                        @if($asset->isCurrentlyCheckedOut())
                                            <a href="{{ route('assets.checkin', $asset) }}" class="hover:text-warning-500 transition-colors" title="Check-in">
                                                <i data-feather="log-in" class="w-5 h-5"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('assets.checkout', $asset) }}" class="hover:text-success-500 transition-colors" title="Check-out">
                                                <i data-feather="log-out" class="w-5 h-5"></i>
                                            </a>
                                        @endif

                                        <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this asset?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-error-500 transition-colors" title="Delete">
                                                <i data-feather="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No assets found</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or add a new asset to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-800">
                {{ $assets->links() }}
            </div>
        </div>
    </div>

    <script>
        function printBulkLabels(assets, copies) {
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Print Asset Labels</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; background: #fff; margin: 0; }');
            printWindow.document.write('.label-grid { display: grid; grid-template-columns: repeat(auto-fill, 330px); gap: 12px; justify-content: start; }');
            printWindow.document.write('.label-card { display: flex; align-items: center; gap: 16px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb; width: 330px !important; max-width: 330px !important; box-sizing: border-box; page-break-inside: avoid; }');
            printWindow.document.write('.h-24 { height: 96px; } .w-24 { width: 96px; }');
            printWindow.document.write('.shrink-0 { flex-shrink: 0; }');
            printWindow.document.write('.bg-white { background-color: white; padding: 6px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; }');
            printWindow.document.write('.space-y-1 > * + * { margin-top: 4px; }');
            printWindow.document.write('.text-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 700; margin: 0; }');
            printWindow.document.write('.text-sm { font-size: 13px; font-weight: 700; color: #1f2937; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }');
            printWindow.document.write('.text-xs { font-size: 11px; font-weight: 600; color: #4b5563; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }');
            printWindow.document.write('.text-xs.font-medium { font-weight: 500; color: #6b7280; }');
            printWindow.document.write('img { max-width: 100%; height: auto; display: block; }');
            printWindow.document.write('@media print { body { padding: 0; } .label-card { page-break-inside: avoid; width: 330px !important; max-width: 330px !important; } }');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write('<div class="label-grid">');

            assets.forEach(asset => {
                const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(asset.url)}`;
                for (let i = 0; i < copies; i++) {
                    printWindow.document.write(`
                        <div class="label-card">
                            <div class="bg-white h-24 w-24 shrink-0">
                                <img src="${qrUrl}" alt="QR Code">
                            </div>
                            <div class="space-y-1" style="flex: 1; min-width: 0;">
                                <div>
                                    <p class="text-label">Asset Tag</p>
                                    <p class="text-sm">${asset.tag}</p>
                                </div>
                                <div>
                                    <p class="text-label">Serial Number</p>
                                    <p class="text-xs">${asset.sn}</p>
                                </div>
                                <div>
                                    <p class="text-label">Company</p>
                                    <p class="text-xs font-medium">${asset.company}</p>
                                </div>
                            </div>
                        </div>
                    `);
                }
            });
 
             printWindow.document.write('</div></body></html>');
             printWindow.document.close();
 
             printWindow.onload = function() {
                 printWindow.print();
                 printWindow.close();
             };
         }

        function exportSelectedAssets(assets) {
            if (!assets.length) return;

            const url = new URL('{{ route('assets.export') }}', window.location.origin);
            assets.forEach(asset => url.searchParams.append('ids[]', asset.id));
            window.location.href = url.toString();
        }
     </script>
@endsection
