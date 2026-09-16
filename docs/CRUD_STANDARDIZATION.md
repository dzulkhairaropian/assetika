# Assetika — CRUD Standardization Guide

Panduan standar pembuatan dan perbaikan **semua halaman CRUD** di Assetika: Master Data, IT Assets, Components, Consumables, Tickets, GA Assets, Users & Roles, Stock Counts, dan modul lainnya — berdasarkan pola acuan **Asset CRUD** & **Components CRUD** & **TailAdmin Design System**.

> **Status**: Standar ini sudah diterapkan pada semua file Blade di Assetika (13 modul master data + 11 modul non-master + projects & standalone). Semua halaman CRUD baru **wajib** mengikuti panduan ini.

---

## 1. Overview & Struktur File

Setiap modul CRUD wajib memiliki 3-4 view utama. Modul master data berada di `resources/views/pages/master/{module}/`, modul lain di `resources/views/pages/{module}/`:

```
resources/views/pages/{module}/
├── index.blade.php      # Halaman daftar/tabel data
├── create.blade.php     # Form tambah data baru
├── edit.blade.php       # Form edit data
└── show.blade.php       # Detail data (opsional jika ada detail khusus)
```

Modul yang sudah terstandarisasi:
- **Master Data (13)**: brands, categories, companies, departments, depreciations, locations, maintenance-types, master-categories, operating-systems, positions, products, statuses, vendors
- **Non-Master (11)**: tickets, components, consumables, licenses, kits, ga-assets, ga-categories, users, roles, stock-counts, projects
- **Standalone**: backups, import, requests

---

## 2. Standar Halaman Index (`index.blade.php`)

### A. Struktur Layout
1. **Breadcrumb & Deskripsi Modul**:
   ```blade
   <x-common.page-breadcrumb pageTitle="Components" />
   <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
       Manage individual hardware component units (RAM, SSD, GPU), serial tags, and asset installations.
   </p>
   ```

2. **Unified Table Card — Semua kontrol data di satu card**:
   Semua elemen yang berhubungan dengan data (tabs, search, filter modal/dropdown, date range, action button, tabel, pagination) berada **di dalam satu card**. Urutan di luar card hanya: breadcrumb, deskripsi, alert flash, dan bulk action bar.

   Struktur card:
   ```
   Card (rounded-xl border shadow-sm)
   ├── Card Header (p-4, border-b)
   │   ├── Pill Tabs (opsional, jika ada segmentasi + Added Today tab filter)
   │   └── Toolbar (search + Filter Modal + Date Range + Export + Action Buttons)
   ├── Table (overflow-x-auto)
   └── Pagination Footer (p-4 sm:p-6, border-t)
   ```

   ```blade
   <div class="overflow-hidden xl:overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
       {{-- Card Header --}}
       <div class="space-y-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-4">
           {{-- Pill Tabs (opsional) --}}
           {{-- Toolbar --}}
       </div>

       {{-- Table --}}
       <div class="max-w-full overflow-x-auto">
           <table class="min-w-full">...</table>
       </div>

       {{-- Pagination Footer --}}
       <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
           {{ $items->links() }}
       </div>
   </div>
   ```

   **Aturan penting halaman list:**
   - **Icon System**: Menggunakan **Lucide Icons** (`data-lucide="..."`) secara konsisten di seluruh elemen UI.
   - **Stat Cards Header DILARANG** — kartu metrik dekoratif di atas tabel (Total X, counter, dsb.) tidak boleh ada di halaman list. Metrik/summary adalah milik halaman **dashboard**, bukan halaman CRUD.
   - **Kolom checkbox HANYA jika ada bulk-delete** — jangan render kolom checkbox bila modul tidak punya route `{module}.bulk-delete` + method `bulkDestroy`.
   - **Monospace Typography**: Tag unik seperti Asset Tag (`AST-xxx`), Component Tag (`CMP-xxx`), Serial Number, Ticket Request ID (`REQ-xxx`) wajib menggunakan `font-mono font-medium`.
   - **Alert flash & bulk action bar di LUAR card** — posisinya di antara deskripsi dan card tabel.

3. **Pill Tabs di Card Header** (TailAdmin Pro pattern + Date Filter integration):
   - Container `inline-flex` agar lebar mengikuti isi (tidak full-width), tetap scrollable di layar kecil.
   - Hitungan per tab dari summary controller, badge warna per segmen.
   - **Modul dengan Tabs**:
      - **Asset**: `All Assets`, `Added Today`, `Ready to Deploy`, `Deployed`, `Service & Repair`, `Broken`, `Aged (≥4y)`
      - **Components**: `All Components`, `Added Today`, `Ready (In Stock)`, `Installed`, `In Repair`, `Scrapped / Broken`
      - **Tickets**: `All Tickets`, `Created Today`, `New`, `In Progress`, `Pending`, `Unassigned`, `Closed`
      - **Consumable Requests**: `All Requests`, `Pending`, `Approved`, `Completed`, `Rejected`
   ```blade
   @php
       $currentTab = request('tab');
       $tabFilters = array_filter(request()->except(['page', 'tab']));
       $tabs = [
           ['key' => null,        'label' => 'All',          'count' => $summary['total'],     'icon' => 'layers',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
           ['key' => 'today',     'label' => 'Added Today',  'count' => $summary['today'] ?? 0,'icon' => 'calendar',      'badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'],
           ['key' => 'ready',     'label' => 'Ready',        'count' => $summary['ready'],     'icon' => 'circle-check',  'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
       ];
   @endphp
   <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-950 [&::-webkit-scrollbar]:h-1.5">
       @foreach($tabs as $tab)
           @php
               $isActive = $currentTab === $tab['key'];
               $tabUrl = route('{module}.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
           @endphp
           <a href="{{ $tabUrl }}"
              class="flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all {{ $isActive ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
               <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
               {{ $tab['label'] }}
               <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
           </a>
       @endforeach
   </div>
   ```

4. **Toolbar (Search, Filter Modal, Date Range, Export & Direct Action)**:
   ```blade
   <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
       <form action="{{ route('{module}.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
           @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif

           <div class="relative flex items-center">
               <span class="absolute left-3 text-gray-400">
                   <i data-lucide="search" class="w-4 h-4"></i>
               </span>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="Search {module}..."
                   class="h-9 w-60 rounded-lg border border-gray-300 bg-white pl-9 pr-4 text-xs text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500">
           </div>

           <!-- Filter Modal Button & Drawer -->
           <!-- Date Range Inputs -->
       </form>

       <div class="flex items-center gap-2">
           <a href="{{ route('{module}.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.97] transition-all duration-150 ease-out">
               <i data-lucide="plus" class="w-4 h-4"></i>
               Add {Module}
           </a>
       </div>
   </div>
   ```

5. **Table Header & Cell Formatting**:
   - Monospace font untuk identifier (`font-mono text-xs font-medium text-gray-900 dark:text-white`).
   - Standard cell: `<td class="px-4 py-3 text-xs">`
   - Header cell: `<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">`
   - Dynamic Badge Status via `<x-ui.badge>` dengan `variant="light"` & `size="sm"`.

---

## 3. Standar Form Create & Edit (`create.blade.php` / `edit.blade.php`)

### A. Dynamic Master Category Dropdown Cascade Pattern
Setiap form CRUD yang mereferensikan Kategori/Product wajib meneruskan variable `$masterCategories` dari Controller agar dropdown kategori dapat terkelompokkan secara terstruktur (misal: Hardware, Consumables, Software):

```blade
<div>
    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
        Category <span class="text-error-500">*</span>
    </label>
    <select name="category_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
        <option value="">Select Category</option>
        @foreach($masterCategories as $masterCat)
            <optgroup label="{{ $masterCat->name }}">
                @foreach($masterCat->categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>
```

---

## 4. Standar Filter Modal & Drawer Pattern

Untuk halaman CRUD dengan filter kompleks, gunakan Modal Filter Popover (terhubung Alpine.js `filterOpen` state):

```blade
<div class="relative" x-data="{ filterOpen: false }">
    <button type="button" @click="filterOpen = !filterOpen"
        class="relative inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
        Filters
        @if($activeFilterCount > 0)
            <span class="ml-1 inline-flex items-center justify-center h-4 w-4 rounded-full bg-brand-500 text-[10px] font-bold text-white">{{ $activeFilterCount }}</span>
        @endif
    </button>
</div>
```

---

## 5. Checklist Aplikasi Modul Terkini

- [x] Alignment 1:1 antara `assets`, `components`, `tickets`, `products`, `consumables` (Filter modal, Tabs filter, Date range).
- [x] Monospace typography (`font-mono`) pada Asset Tag, Serial Number, Ticket Request ID, Component Tag.
- [x] Feather Icons dialihkan ke **Lucide Icons** (`data-lucide="..."`) secara bertahap & konsisten.
- [x] Multi-context lookup controller tanpa SQL error pada accessor.
- [x] Dynamic `$masterCategories` grouping pada dropdown create & edit views.
