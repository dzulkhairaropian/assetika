@extends('layouts.app')

@section('content')
    <div class="space-y-4" x-data="{
        selectedItems: [],
        selectAll: false,
        toggleAll() {
            this.selectAll = !this.selectAll;
            if (this.selectAll) {
                this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(el => Number(el.value));
            } else {
                this.selectedItems = [];
            }
        },
        toggleItem(id) {
            if (this.selectedItems.includes(id)) {
                this.selectedItems = this.selectedItems.filter(i => i !== id);
            } else {
                this.selectedItems.push(id);
            }
            this.selectAll = this.selectedItems.length === document.querySelectorAll('.row-checkbox').length;
        }
    }">
        <x-common.page-breadcrumb pageTitle="Components" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Manage hardware components (RAM, SSD, GPU) and stock levels.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <!-- Bulk Delete Form -->
        <form id="bulkDeleteForm" action="{{ route('components.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <template x-for="id in selectedItems" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedItems.length > 0"
            x-transition
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/50 p-3 dark:border-brand-900/30 dark:bg-brand-500/10"
            style="display: none;">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i>
                <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                    <span x-text="selectedItems.length"></span> items selected.
                </span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-500 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-600">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
	            {{-- Card Header: Toolbar --}}
	            <div class="border-b border-gray-100 p-3.5 dark:border-gray-800 sm:p-4">
	                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
	                    <form action="{{ route('components.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
	                        <div class="relative flex items-center">
	                            <span class="absolute left-2.5 text-gray-400">
	                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
	                            </span>
	                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search components..."
	                                class="h-9 w-48 sm:w-56 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
	                        </div>

	                        <select name="product_id" onchange="this.form.submit()" class="h-9 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
	                            <option value="">All Master Products</option>
	                            @foreach($products as $p)
	                                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
	                            @endforeach
	                        </select>

	                        <select name="category_id" onchange="this.form.submit()" class="h-9 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
	                            <option value="">All Categories</option>
	                            @foreach($categories as $c)
	                                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
	                            @endforeach
	                        </select>

	                        <select name="status" onchange="this.form.submit()" class="h-9 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
	                            <option value="">All Statuses</option>
	                            <option value="Ready" {{ request('status') == 'Ready' ? 'selected' : '' }}>Ready (In Stock)</option>
	                            <option value="Installed" {{ request('status') == 'Installed' ? 'selected' : '' }}>Installed</option>
	                            <option value="Maintenance" {{ request('status') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
	                            <option value="Retired" {{ request('status') == 'Retired' ? 'selected' : '' }}>Retired</option>
	                        </select>

	                        @if(request()->filled('search') || request()->filled('product_id') || request()->filled('category_id') || request()->filled('status'))
	                            <a href="{{ route('components.index') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Reset</a>
	                        @endif
	                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('components.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Component
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">
                                <label class="flex cursor-pointer items-center select-none">
                                    <div class="relative">
                                        <input type="checkbox" :checked="selectAll" @click="toggleAll()" class="sr-only">
                                        <div :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                            class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                            <span :class="selectAll ? '' : 'opacity-0'">
                                                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Tag / Component</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Category & Model</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Assigned Asset</th>
                            <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Location</th>
                            <th class="px-4 py-2.5 text-end font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($components as $component)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2.5">
                                    <label class="flex cursor-pointer items-center select-none">
                                        <div class="relative">
                                            <input type="checkbox" value="{{ $component->id }}" class="row-checkbox sr-only"
                                                :checked="selectedItems.includes({{ $component->id }})" @click="toggleItem({{ $component->id }})">
                                            <div :class="selectedItems.includes({{ $component->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-4 w-4 items-center justify-center rounded border transition">
                                                <span :class="selectedItems.includes({{ $component->id }}) ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="block font-mono text-[11px] font-bold text-gray-900 dark:text-white">{{ $component->component_tag ?? '-' }}</span>
                                    <a href="{{ route('components.show', $component) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                        {{ $component->name }}
                                    </a>
                                    @if($component->serial)
                                        <span class="block text-[10px] text-gray-400 font-mono">SN: {{ $component->serial }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                    {{ $component->category?->name ?? '-' }}
                                    <span class="block text-[11px] text-gray-400">{{ $component->model_number ?? $component->manufacturer }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($component->status === 'Installed')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            Installed
                                        </span>
                                    @elseif($component->status === 'Maintenance')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            Maintenance
                                        </span>
                                    @elseif($component->status === 'Retired')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            Retired
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                            Ready (In Stock)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($component->assignedAsset)
                                        <a href="{{ route('assets.show', $component->assignedAsset) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50/80 px-2 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:border-brand-900/40 dark:bg-brand-500/10 dark:text-brand-300 transition">
                                            <i data-lucide="laptop" class="w-3.5 h-3.5 text-brand-500"></i>
                                            {{ $component->assignedAsset->asset_tag ?? $component->assignedAsset->device_name }}
                                        </a>
                                        @if($component->assignedAsset->assignedToUser)
                                            <span class="block text-[10px] text-gray-400 mt-0.5">User: {{ $component->assignedAsset->assignedToUser->name }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-[11px] italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ $component->location?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <div x-data="{
                                        open: false,
                                        dropdownStyle: '',
                                        toggle(e) {
                                            this.open = !this.open;
                                            if (this.open) {
                                                const rect = e.currentTarget.getBoundingClientRect();
                                                const menuHeight = 120;
                                                const spaceBelow = window.innerHeight - rect.bottom;
                                                const top = spaceBelow < menuHeight ? Math.max(10, rect.top - menuHeight) : (rect.bottom + 4);
                                                const left = Math.max(10, rect.right - 144);
                                                this.dropdownStyle = `position: fixed; top: ${top}px; left: ${left}px; z-index: 9999;`;
                                            }
                                        }
                                    }" @click.away="open = false" @scroll.window="open = false" class="inline-block text-left">
                                        <button type="button" @click="toggle($event)" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>

                                        <div x-show="open"
                                            x-transition
                                            :style="dropdownStyle"
                                            class="w-36 rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                            style="display: none;">
                                            
                                            <a href="{{ route('components.show', $component) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                                                View Details
                                            </a>

                                            <a href="{{ route('components.edit', $component) }}" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-gray-400"></i>
                                                Edit
                                            </a>

                                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                            <form action="{{ route('components.destroy', $component) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-1.5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-lucide="cpu" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No components found</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Add hardware components like RAM, SSD, GPU to track stock and assignments.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="border-t border-gray-100 p-3 dark:border-gray-800 sm:p-4">
                {{ $components->links() }}
            </div>
        </div>
    </div>
@endsection
