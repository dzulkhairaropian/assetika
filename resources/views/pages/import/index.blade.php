@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Import Data" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Bulk import assets, consumables, and user data from CSV spreadsheets.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif
    @if(session('import_failed_details'))
        <div class="rounded-xl border border-red-200 bg-red-50/50 p-4 text-xs text-red-700 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400 space-y-2">
            <h4 class="font-bold uppercase tracking-wider flex items-center gap-1.5 text-red-800 dark:text-red-300">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i> Skipped / Failed Import Rows Report
            </h4>
            <ul class="list-disc list-inside space-y-1 font-mono text-[11px]">
                @foreach(session('import_failed_details') as $detail)
                    <li>{{ $detail }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        {{-- Main Form Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <i data-lucide="cloud-upload" class="w-5 h-5 text-brand-500"></i>
                    Upload Spreadsheet File
                </h3>

                <form action="{{ route('import.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <!-- Import Type -->
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Data Type to Import <span class="text-error-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="type" required class="dark:bg-gray-950 h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 pr-11 text-xs text-gray-800 shadow-sm outline-none focus:border-brand-500 focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                    <optgroup label="Core Modules">
                                        <option value="assets">IT Assets Hardware</option>
                                        <option value="consumables">Consumables Inventory</option>
                                        <option value="components">Components & Parts</option>
                                        <option value="licenses">Software Licenses</option>
                                        <option value="users">User Accounts</option>
                                    </optgroup>
                                    <optgroup label="Master Data">
                                        <option value="companies">Companies</option>
                                        <option value="departments">Departments</option>
                                        <option value="locations">Locations</option>
                                        <option value="positions">Positions</option>
                                        <option value="brands">Brands</option>
                                        <option value="categories">Categories</option>
                                        <option value="products">Products / Models</option>
                                        <option value="vendors">Vendors</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <!-- CSV File Upload Dropzone -->
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                Select CSV File <span class="text-error-500">*</span>
                            </label>
                            <div class="relative flex items-center justify-center w-full">
                                <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50/50 hover:bg-gray-100/50 dark:bg-white/[0.02] dark:border-gray-700 dark:hover:border-gray-600 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <i data-lucide="file-text" class="w-8 h-8 mb-2 text-brand-500"></i>
                                        <p class="mb-1 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-500">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs text-gray-400">CSV or TXT files (MAX. 10MB)</p>
                                    </div>
                                    <input type="file" name="file" accept=".csv,.txt" required class="hidden" />
                                </label>
                            </div>
                            @error('file') <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 shadow-theme-xs">
                            <i data-lucide="eye" class="w-4 h-4"></i> Preview & Validate Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Panel: Download Templates Card --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                    <i data-lucide="download" class="w-5 h-5 text-brand-500"></i>
                    Sample Templates
                </h3>
                <p class="mb-5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Download pre-formatted CSV templates with valid column headers before uploading data to ensure accurate mapping.
                </p>
                <div class="space-y-2.5 max-h-[520px] overflow-y-auto pr-1">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 pt-1 pb-0.5">Core Modules</div>
                    <a href="{{ route('import.template', 'assets') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="hard-drive" class="w-4 h-4 text-brand-500"></i> IT Assets Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'consumables') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-amber-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="package" class="w-4 h-4 text-amber-500"></i> Consumables Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'components') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-indigo-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="cpu" class="w-4 h-4 text-indigo-500"></i> Components Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'licenses') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-emerald-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="key" class="w-4 h-4 text-emerald-500"></i> Licenses Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'users') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-purple-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-purple-500"></i> Users Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>

                    <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 pt-3 pb-0.5">Master Data</div>
                    <a href="{{ route('import.template', 'companies') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="briefcase" class="w-4 h-4 text-blue-500"></i> Companies Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'departments') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="layers" class="w-4 h-4 text-teal-500"></i> Departments Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'locations') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-rose-500"></i> Locations Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'positions') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 text-orange-500"></i> Positions Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'brands') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="tag" class="w-4 h-4 text-cyan-500"></i> Brands Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'categories') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="grid" class="w-4 h-4 text-sky-500"></i> Categories Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'products') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="box" class="w-4 h-4 text-violet-500"></i> Products Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('import.template', 'vendors') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition">
                        <span class="flex items-center gap-2">
                            <i data-lucide="truck" class="w-4 h-4 text-amber-600"></i> Vendors Template
                        </span>
                        <i data-lucide="download-cloud" class="w-4 h-4 text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
