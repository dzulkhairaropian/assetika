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
        <x-common.page-breadcrumb pageTitle="General Affairs Asset Management" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('ga-assets.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400">
                        <i data-feather="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search assets..."
                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <select name="category_id" onchange="this.form.submit()"
                    class="h-10 rounded-lg border border-gray-300 bg-white pl-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="h-10 rounded-lg border border-gray-300 bg-white pl-3 pr-8 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                    <option value="">All Statuses</option>
                    <option value="Available" {{ request('status') == 'Available' ? 'selected' : '' }}>Available</option>
                    <option value="Deployed" {{ request('status') == 'Deployed' ? 'selected' : '' }}>Deployed</option>
                    <option value="In Repair" {{ request('status') == 'In Repair' ? 'selected' : '' }}>In Repair</option>
                    <option value="Disposed" {{ request('status') == 'Disposed' ? 'selected' : '' }}>Disposed</option>
                </select>

                @if(request()->anyFilled(['search', 'category_id', 'status']))
                    <a href="{{ route('ga-assets.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
                @endif
            </form>

            <a href="{{ route('ga-assets.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                <i data-feather="plus" class="w-4 h-4"></i>
                Add GA Asset
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <!-- Bulk Action Form -->
        <form id="bulkDeleteForm" action="{{ route('ga-assets.bulk-delete') }}" method="POST" class="hidden">
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
                <button @click="if (confirm('Are you sure you want to delete the selected assets?')) { document.getElementById('bulkDeleteForm').submit(); }" 
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
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
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Asset</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Status</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Category</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Value (Rp)</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Assigned To</th>
                            <th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors border-b border-gray-100 dark:border-white/[0.05]">
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex justify-center items-center">
                                        <label class="flex cursor-pointer items-center select-none">
                                            <div class="relative">
                                                <input type="checkbox" 
                                                    :checked="selectedAssets.some(a => a.id === {{ $asset->id }})" 
                                                    @click="toggleAsset({
                                                        id: {{ $asset->id }}, 
                                                        tag: '{{ $asset->asset_code }}', 
                                                        sn: '{{ $asset->serial_number ?? '-' }}', 
                                                        company: 'General Affairs',
                                                        url: '{{ route('ga-assets.show', $asset) }}'
                                                    })" 
                                                    class="asset-checkbox sr-only"
                                                    data-asset='{"id":{{ $asset->id }},"tag":"{{ $asset->asset_code }}","sn":"{{ $asset->serial_number ?? "-" }}","company":"General Affairs","url":"{{ route("ga-assets.show", $asset) }}"}'>
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
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                                            <i data-feather="briefcase" class="w-5 h-5"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-gray-700 text-theme-sm dark:text-gray-450">{{ $asset->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $asset->asset_code }} • S/N: {{ $asset->serial_number ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                     @php
                                         $statusObject = $asset->statusObject;
                                         $statusName = optional($statusObject)->name;
                                         $statusColor = optional($statusObject)->color;
                                         $statusIcon = optional($statusObject)->icon;
                                         $iconHtml = $statusIcon ? new \Illuminate\Support\HtmlString("<i data-feather='{$statusIcon}' class='w-3 h-3'></i>") : null;
                                     @endphp
                                     <x-ui.badge :color="$statusColor" variant="light" size="sm" :startIcon="$iconHtml">{{ $statusName }}</x-ui.badge>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ $asset->category->name ?? '-' }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $asset->subcategory->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-bold text-gray-700 dark:text-gray-400">
                                            Rp {{ number_format($asset->total_asset_value, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    @if($asset->currentHolder)
                                        <div class="flex flex-col">
                                            <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                                {{ $asset->currentHolder->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-theme-sm text-gray-400 italic">Not Assigned</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-sm font-medium">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <a href="{{ route('ga-assets.show', $asset) }}" class="hover:text-brand-500 transition-colors" title="View Details">
                                            <i data-feather="eye" class="w-5 h-5"></i>
                                        </a>
                                        <a href="{{ route('ga-assets.edit', $asset) }}" class="hover:text-brand-500 transition-colors" title="Edit">
                                            <i data-feather="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        <form action="{{ route('ga-assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this asset?')" class="inline-flex">
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
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                            <i data-feather="package" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No assets found</p>
                                        <p class="text-xs text-gray-450 dark:text-gray-550">Try adjusting your search filters.</p>
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
                                     <p class="text-label">Department</p>
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
    </script>
@endsection
