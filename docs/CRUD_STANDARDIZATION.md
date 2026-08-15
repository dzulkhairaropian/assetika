# Assetika — CRUD Standardization Guide

Panduan standar pembuatan dan perbaikan **semua halaman CRUD** di Assetika: Master Data, IT Assets, Consumables, Tickets, GA Assets, Users & Roles, Stock Counts, dan modul lainnya — berdasarkan pola acuan **Asset CRUD** & **TailAdmin Design System**.

> **Status**: Standar ini sudah diterapkan pada 94+ file Blade (16 modul master data + 10 modul non-master + halaman standalone). Semua halaman CRUD baru **wajib** mengikuti panduan ini.

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
- **Master Data (16)**: brands, categories, companies, custom-fields, departments, depreciations, eulas, fieldsets, locations, maintenance-types, master-categories, operating-systems, positions, products, statuses, vendors
- **Non-Master (10)**: tickets, components, consumables, licenses, kits, ga-assets, ga-categories, users, roles, stock-counts
- **Standalone**: labels, reports, backups, import, requests

---

## 2. Standar Halaman Index (`index.blade.php`)

### A. Struktur Layout
1. **Breadcrumb & Deskripsi Modul**:
   ```blade
   <x-common.page-breadcrumb pageTitle="Brands" />
   <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
       Manage brand catalog for your asset products.
   </p>
   ```

2. **Unified Table Card — Semua kontrol data di satu card**:
   Semua elemen yang berhubungan dengan data (tabs, search, filter, action button, tabel, pagination) berada **di dalam satu card**. Urutan di luar card hanya: breadcrumb, deskripsi, alert flash, dan bulk action bar.

   Struktur card:
   ```
   Card (rounded-2xl border)
   ├── Card Header (p-4 sm:p-5, border-b)
   │   ├── Pill Tabs (opsional, jika ada segmentasi)
   │   └── Toolbar (search + filter + action buttons)
   ├── Table (overflow-x-auto)
   └── Pagination Footer (p-4 sm:p-6, border-t)
   ```

   ```blade
   <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
       {{-- Card Header --}}
       <div class="space-y-4 border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
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
   - **Stat Cards Header DILARANG** — kartu metrik dekoratif di atas tabel (Total X, counter, dsb.) tidak boleh ada di halaman list. Metrik/summary adalah milik halaman **dashboard**, bukan halaman CRUD. (Dihapus dari: components, consumables, licenses, kits, stock-counts, brands, categories, dll.)
   - **Kolom checkbox HANYA jika ada bulk-delete** — jangan render kolom checkbox bila modul tidak punya route `{module}.bulk-delete` + method `bulkDestroy` (dead UI, tidak ada fungsinya). Contoh tanpa checkbox: users, roles, ga-categories, stock-counts.
   - **Alert flash & bulk action bar di LUAR card** — posisinya di antara deskripsi dan card tabel.

3. **Pill Tabs di Card Header** (TailAdmin Pro pattern, opsional untuk modul dengan segmentasi seperti Asset & Ticket):
   - Container `inline-flex` agar lebar mengikuti isi (tidak full-width), tetap scrollable di layar kecil.
   - Hitungan per tab dari summary controller, badge warna per segmen.
   - **Modul dengan Tabs**:
      - **Asset**: `All Assets`, `Ready to Deploy`, `Deployed`, `Service & Repair`, `Broken`, `Aged (≥4y)`
      - **Tickets**: `All Tickets`, `New`, `In Progress`, `Pending`, `Unassigned`, `Closed`
      - **Consumable Requests**: `All Requests`, `Pending`, `Approved`, `Completed`, `Rejected`
      - **Asset Requests**: `All Requests`, `Hardware`, `Consumables`, `Licenses`
      - **Request Catalog**: `Hardware Categories`, `Consumables`, `Licenses`
   ```blade
   @php
       $currentTab = request('tab');
       $tabFilters = array_filter(request()->except(['page', 'tab']));
       $tabs = [
           ['key' => null,    'label' => 'All',    'count' => $summary['total'], 'icon' => 'layers', 'badge' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'],
           ['key' => 'active','label' => 'Active', 'count' => $summary['active'],'icon' => 'check-circle', 'badge' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'],
           // ... segmen lain
       ];
   @endphp
   <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5">
       @foreach($tabs as $tab)
           @php
               $isActive = $currentTab === $tab['key'];
               $tabUrl = route('{module}.index', $tab['key'] ? array_merge($tabFilters, ['tab' => $tab['key']]) : $tabFilters);
           @endphp
           <a href="{{ $tabUrl }}"
              class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all {{ $isActive ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' }}">
               <i data-feather="{{ $tab['icon'] }}" class="w-4 h-4"></i>
               {{ $tab['label'] }}
               <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $tab['badge'] }}">{{ number_format($tab['count']) }}</span>
           </a>
       @endforeach
   </div>
   ```

   Controller: hitung summary **sebelum** filter tab diterapkan, lalu terapkan filter tab:
   ```php
   $summary = [
       'total'  => (clone $query)->count(),
       'active' => (clone $query)->whereHas('status', fn($q) => $q->where('name', 'Active'))->count(),
       // ...
   ];

   switch ($request->input('tab')) {
       case 'active':
           $query->whereHas('status', fn($q) => $q->where('name', 'Active'));
           break;
       // ...
   }

   $items = $query->latest()->paginate(50)->withQueryString();
   ```

4. **Toolbar (Search, Filter & Direct Action)** — di dalam card header, di bawah tabs:
   ```blade
   <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
       <form action="{{ route('{module}.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
           <div class="relative flex items-center">
               <span class="absolute left-3 text-gray-400">
                   <i data-feather="search" class="w-4 h-4"></i>
               </span>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="Search {module}..."
                   class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
           </div>
           @if(request()->filled('search'))
               <a href="{{ route('{module}.index') }}" class="text-sm text-gray-500 hover:text-brand-500">Clear</a>
           @endif
       </form>

       <div class="flex items-center gap-3">
           {{-- Export / tombol sekunder lain (opsional) --}}
           <a href="{{ route('{module}.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
               <i data-feather="plus" class="w-4 h-4"></i>
               Add {Module}
           </a>
       </div>
   </div>
   ```

5. **Table Header (TailAdmin Standard)**:
   - Table: `<table class="min-w-full">`
   - Header: `<thead class="border-b border-gray-100 dark:border-gray-800">` (**Tanpa** `bg-gray-50` / `border-y`)
   - Header cell format:
      ```blade
      <th class="px-5 py-3 sm:px-6">
          <div class="flex items-center">
              <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Column Title</p>
          </div>
      </th>
      ```

6. **Table Body Cell Padding**:
   - Standard cell: `<td class="px-5 py-4 sm:px-6">`
   - Actions cell: `<td class="px-5 py-4 sm:px-6 text-right">`

7. **Action Icons (Right Column)**:
   - Gunakan Feather Icon dengan hover color TailAdmin:
      ```blade
      <div class="flex items-center justify-end gap-3 text-gray-500">
          <a href="{{ route('{module}.show', $item) }}" class="transition-colors hover:text-brand-500" title="View">
              <i data-feather="eye" class="w-5 h-5"></i>
          </a>
          <a href="{{ route('{module}.edit', $item) }}" class="transition-colors hover:text-brand-500" title="Edit">
              <i data-feather="edit-3" class="w-5 h-5"></i>
          </a>
          <form action="{{ route('{module}.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline-flex">
              @csrf @method('DELETE')
              <button type="submit" class="transition-colors hover:text-error-500" title="Delete">
                  <i data-feather="trash-2" class="w-5 h-5"></i>
              </button>
          </form>
      </div>
      ```

8. **Empty State Table Row**:
   ```blade
   @empty
       <tr>
           <td colspan="{TOTAL_COLS}" class="px-6 py-12 text-center">
               <div class="flex flex-col items-center justify-center space-y-2">
                   <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                       <i data-feather="inbox" class="w-6 h-6"></i>
                   </div>
                   <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No {module} found</p>
                   <p class="text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or add a new entry to get started.</p>
               </div>
           </td>
       </tr>
   @endforelse
   ```

9. **Row Highlighting Berbasis Kondisi** (opsional, referensi: `pages/assets/index.blade.php`):
   - Hitung kondisi di awal loop `@forelse`, terapkan class background ke `<tr>`.
   - Prioritas: **status > kondisi lain** (mis. aging).
   ```blade
   @forelse($items as $item)
       @php
           $statusName = strtolower(optional($item->status)->name ?? '');
           if (in_array($statusName, ['broken'])) {
               $rowBg = 'bg-error-50/70 dark:bg-error-500/10 hover:bg-error-100/60 dark:hover:bg-error-500/15';
           } elseif (in_array($statusName, ['service', 'maintenance', 'in repair'])) {
               $rowBg = 'bg-warning-50/70 dark:bg-warning-500/10 hover:bg-warning-100/60 dark:hover:bg-warning-500/15';
           } else {
               $rowBg = 'hover:bg-gray-50/50 dark:hover:bg-white/[0.02]';
           }
       @endphp
       <tr class="border-b border-gray-100 dark:border-white/[0.05] {{ $rowBg }}">
           ...
       </tr>
   @endforelse
   ```

10. **Footer Pagination**:
    - **Cukup panggil `{{ $items->links() }}` saja** di dalam container border-t, **tidak perlu** menambahkan `<p>Showing ...</p>` manual karena `vendor/pagination/tailwind.blade.php` sudah merendernya otomatis.
    ```blade
    <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
        {{ $items->links() }}
    </div>
    ```

---

## 3. Standar Form Create & Edit (`create.blade.php` / `edit.blade.php`)

### A. Layout Grid (Split Layout)
Gunakan layout 2 kolom responsif: `grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]`

**Deskripsi di bawah judul** — wajib ada, sama seperti halaman index. Kalimat menjelaskan apa yang dilakukan form ini:
- Create: *"Add a new {item} to your organization's {context}."*
- Edit: *"Update information, status, or assignment for {item} {identifier}."*

**Divider antar section** — setiap section form di dalam card utama dipisahkan dengan `<hr class="border-gray-100 dark:border-gray-800">` agar batas antar kelompok field jelas.

```blade
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Create {Module}" :items="[['label' => '{Modules}', 'url' => route('{module}.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Add a new {item} to your organization's {context}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('{module}.store') }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <!-- Kolom Kiri: Form Utama -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
                            General Information
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <!-- Form Input Fields -->
                        </div>
                    </div>

                    {{-- Divider antar section --}}
                    <hr class="border-gray-100 dark:border-gray-800">

                    <div class="pt-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-feather="box" class="w-5 h-5 text-brand-500"></i>
                            Next Section
                        </h3>
                        <!-- Fields section berikutnya -->
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Actions Sidebar -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-medium text-gray-800 dark:text-white/90">
                        <i data-feather="save" class="w-5 h-5 text-brand-500"></i>
                        Actions
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            Save {Module}
                        </button>
                        <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">
                            Save & Create Another
                        </button>
                        <a href="{{ route('{module}.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
```

### B. Standard Input Styling
```blade
<!-- Input Text/Email/Number -->
<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
        Label <span class="text-error-500">*</span>
    </label>
    <input type="text" name="field_name" value="{{ old('field_name') }}" placeholder="Enter placeholder..." required
        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
    @error('field_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
</div>

<!-- Select Input -->
<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
        Select Label <span class="text-error-500">*</span>
    </label>
    <div x-data="{ isOptionSelected: {{ old('select_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
        <select name="select_id" required
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select Option</option>
            @foreach($options as $opt)
                <option value="{{ $opt->id }}" {{ old('select_id') == $opt->id ? 'selected' : '' }}>{{ $opt->name }}</option>
            @endforeach
        </select>
        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
    </div>
    @error('select_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
</div>
```

---

## 4. Standar Backend Search Controller (`Lookup Query Pattern`)

### Rules:
1. **DILARANG** melakukan SQL `like` pada **Computed Accessor / Attribute** (contoh: `$product->model` yang hanya accessor `name`). Ini akan menyebabkan error `SQLSTATE[42S22] Unknown Column`.
2. Gunakan `orWhereHas()` untuk mencari di relasi terkait (`brand`, `category`, `department`, `user`, dll.).
3. **Multi-context search** — search satu keyword harus bisa mencari di semua konteks yang relevan, bukan hanya 1-2 kolom. Contoh: search "zaki" di asset muncul dari asset_tag, serial_number, user name, brand, department, location, vendor, dll. (Referensi: `AssetController@index` — 18 field/relasi).
4. Selalu tambahkan `->withQueryString()` pada pagination agar parameter search & filter tidak hilang saat ganti halaman.

```php
public function index(Request $request)
{
    $query = ModelName::with(['relation1', 'relation2']);

    if ($request->filled('search')) {
        $search = $request->search;
        $like = '%' . $search . '%';
        $query->where(function($q) use ($like) {
            $q->where('name', 'like', $like)
              ->orWhere('code', 'like', $like)
              ->orWhere('description', 'like', $like)
              ->orWhereHas('relation1', function($r) use ($like) {
                  $r->where('name', 'like', $like);
              });
        });
    }

    $items = $query->latest()->paginate(50)->withQueryString();

    return view('pages.{module}.index', compact('items'));
}
```

---

## 5. Standar Bulk Actions (Bulk Select & Delete)

Pola dari Asset CRUD: checkbox per row + bulk action bar yang muncul saat ada item terpilih.

### A. Alpine State di wrapper utama
```blade
<div class="space-y-6" x-data="{
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
```

### B. Bulk Delete Form (hidden)
```blade
<form id="bulkDeleteForm" action="{{ route('{module}.bulk-delete') }}" method="POST" class="hidden">
    @csrf
    <template x-for="id in selectedItems" :key="id">
        <input type="hidden" name="ids[]" :value="id">
    </template>
</form>
```

### C. Bulk Action Bar
```blade
<div x-show="selectedItems.length > 0"
    x-transition
    class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-500/10"
    style="display: none;">
    <div class="flex items-center gap-2">
        <i data-feather="info" class="w-5 h-5 text-brand-500"></i>
        <span class="text-sm font-medium text-brand-700 dark:text-brand-400">
            <span x-text="selectedItems.length"></span> items selected.
        </span>
    </div>
    <div class="flex items-center gap-4">
        <button type="button" @click="if (confirm('Are you sure you want to delete the selected items?')) { document.getElementById('bulkDeleteForm').submit(); }"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-error-600">
            <i data-feather="trash-2" class="w-4 h-4"></i>
            Delete Selected
        </button>
    </div>
</div>
```

### D. Checkbox Cell (Header & Body)
```blade
<!-- Header checkbox -->
<th class="px-5 py-3 sm:px-6 w-10">
    <div class="flex items-center">
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

<!-- Body checkbox -->
<td class="px-5 py-4 sm:px-6">
    <label class="flex cursor-pointer items-center select-none">
        <div class="relative">
            <input type="checkbox" value="{{ $item->id }}" class="row-checkbox sr-only"
                :checked="selectedItems.includes({{ $item->id }})" @click="toggleItem({{ $item->id }})">
            <div :class="selectedItems.includes({{ $item->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                class="hover:border-brand-500 dark:hover:border-brand-500 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition">
                <span :class="selectedItems.includes({{ $item->id }}) ? '' : 'opacity-0'">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        </div>
    </label>
</td>
```

### E. Controller Bulk Delete
```php
public function bulkDestroy(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:{table},id',
    ]);

    ModelName::whereIn('id', $request->ids)->delete();

    return redirect()->route('{module}.index')->with('success', 'Selected items deleted successfully.');
}
```

---

## 6. Standar Filter Dropdown (Opsional, jika > 1 filter)

Gunakan dropdown filter dengan badge counter aktif (pola Asset index):

```blade
@php
    $activeCount = 0;
    if (request('status_id')) $activeCount++;
    if (request('category_id')) $activeCount++;
@endphp

<div class="relative" @click.away="filterOpen = false" x-data="{ filterOpen: false }">
    <button type="button" @click="filterOpen = !filterOpen"
        class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 transition">
        <i data-feather="sliders" class="w-4 h-4"></i>
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
        <div class="mb-4 flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Filter Options</h4>
            <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Select filters -->
        </div>

        <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
            <a href="{{ route('{module}.index') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition">
                Clear All Filters
            </a>
            <button type="submit" @click="filterOpen = false"
                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition">
                Apply Filters
            </button>
        </div>
    </div>
</div>
```

---

## 7. Standar Halaman Show/Detail (`show.blade.php`)

Untuk modul yang butuh detail (seperti Asset), gunakan pola:

### A. Breadcrumb & Sub-header Description
```blade
<x-common.page-breadcrumb pageTitle="{Module} Details" :items="[['label' => '{Modules}', 'url' => route('{module}.index')], ['label' => $item->name]]" />

<p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
    Detailed specification, assignment history, maintenance, and support tickets for {item->name}.
</p>
```

### B. Header Card
```blade
<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                <i data-feather="{icon}" class="h-8 w-8"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $item->name }}</h2>
                    <x-ui.badge color="success" variant="light" size="sm">Active</x-ui.badge>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->code }} • Subtitle info</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('{module}.edit', $item) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                <i data-feather="edit-3" class="h-4 w-4"></i>
                Edit
            </a>
            <!-- Other action buttons -->
        </div>
    </div>
</div>
```

### C. Tab Navigation (TailAdmin Pro Pill Style)
```blade
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="{ activeTab: 'details' }">
    <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
        <div class="inline-flex max-w-full overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5">
            <button @click="activeTab = 'details'"
                :class="activeTab === 'details' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                <i data-feather="file-text" class="w-4 h-4"></i>
                Details
            </button>
            <button @click="activeTab = 'history'"
                :class="activeTab === 'history' ? 'bg-white text-gray-700 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                class="flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                <i data-feather="activity" class="w-4 h-4"></i>
                History
            </button>
        </div>
    </div>

    <div class="p-6">
        <div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
            <h4 class="mb-5 flex items-center gap-2 text-base font-medium text-gray-800 border-b border-gray-100 pb-3 dark:border-gray-800 dark:text-white/90">
                <i data-feather="info" class="h-5 w-5 text-brand-500"></i>
                Section Title
            </h4>
            <!-- Key-value info cards grid -->
        </div>
        <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
            <!-- Table with standard thead font-medium text-theme-xs -->
        </div>
    </div>
</div>
```

---

## 8. Standar Alert & Flash Messages

Gunakan komponen `<x-ui.alert>` dengan auto-dismiss (5 detik):

```blade
@if(session('success'))
    <x-ui.alert variant="success" message="{{ session('success') }}" />
@endif

@if(session('error'))
    <x-ui.alert variant="error" message="{{ session('error') }}" />
@endif

@if($errors->any())
    <x-ui.alert variant="error" title="Validation Error" :message="$errors->first()" />
@endif
```

### Controller flash message convention:
```php
// Create
return redirect()->route('{module}.index')->with('success', '{Module} created successfully.');

// Update
return redirect()->route('{module}.index')->with('success', '{Module} updated successfully.');

// Delete
return redirect()->route('{module}.index')->with('success', '{Module} deleted successfully.');

// Error
return back()->with('error', 'Something went wrong.');
```

---

## 9. Standar Status Badge

Gunakan `<x-ui.badge>` component untuk semua status:

```blade
<!-- Static color -->
<x-ui.badge color="success" variant="light" size="sm">Active</x-ui.badge>
<x-ui.badge color="error" variant="light" size="sm">Inactive</x-ui.badge>
<x-ui.badge color="warning" variant="light" size="sm">Pending</x-ui.badge>
<x-ui.badge color="primary" variant="light" size="sm">Default</x-ui.badge>

<!-- Dynamic from DB (hex color) -->
<x-ui.badge :color="$item->status->color" variant="light" size="sm" :featherIcon="$item->status->icon">
    {{ $item->status->name }}
</x-ui.badge>
```

Available colors: `primary`, `success`, `error`, `danger`, `warning`, `info`, `light`, `dark`, atau hex color dari DB.

---

## 10. Standar Date Picker

Gunakan komponen `<x-form.date-picker>` untuk semua input tanggal:

```blade
<div>
    <x-form.date-picker
        id="field_name"
        name="field_name"
        label="Field Label"
        placeholder="Select date"
        defaultDate="{{ old('field_name') }}"
    />
    @error('field_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
</div>
```

---

## 11. Standar Controller Structure

### A. Method Naming Convention
```php
class {Module}Controller extends Controller
{
    public function index(Request $request)      // GET /{module}
    public function create()                      // GET /{module}/create
    public function store(Request $request)       // POST /{module}
    public function show(Model $model)            // GET /{module}/{model}
    public function edit(Model $model)            // GET /{module}/{model}/edit
    public function update(Request $request, Model $model)  // PUT/PATCH /{module}/{model}
    public function destroy(Model $model)         // DELETE /{module}/{model}
    public function bulkDestroy(Request $request) // POST /{module}/bulk-delete
}
```

### B. Validation Pattern
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:{table},code',
        'description' => 'nullable|string',
        'is_active' => 'nullable|boolean',
    ]);

    Model::create($validated);

    if ($request->has('create_another')) {
        return redirect()->route('{module}.create')->with('success', '{Module} created successfully. Add another one.');
    }

    return redirect()->route('{module}.index')->with('success', '{Module} created successfully.');
}

public function update(Request $request, Model $model)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:{table},code,' . $model->id,
        'description' => 'nullable|string',
        'is_active' => 'nullable|boolean',
    ]);

    $model->update($validated);

    return redirect()->route('{module}.index')->with('success', '{Module} updated successfully.');
}
```

### C. Route Registration
```php
// Di routes/web.php (dalam middleware auth)
Route::post('{module}/bulk-delete', [{Module}Controller::class, 'bulkDestroy'])->name('{module}.bulk-delete');
Route::resource('{module}', {Module}Controller::class);
```

---

## 12. Standar Route Naming Convention

| Action | Route Name | Method |
|--------|-----------|--------|
| List | `{module}.index` | GET |
| Create Form | `{module}.create` | GET |
| Store | `{module}.store` | POST |
| Detail | `{module}.show` | GET |
| Edit Form | `{module}.edit` | GET |
| Update | `{module}.update` | PUT/PATCH |
| Delete | `{module}.destroy` | DELETE |
| Bulk Delete | `{module}.bulk-delete` | POST |

Contoh: `brands.index`, `brands.create`, `brands.store`, `brands.show`, `brands.edit`, `brands.update`, `brands.destroy`, `brands.bulk-delete`

---

## 13. Checklist Aplikasi Modul

Setiap modul (Master Data & Non-Master) yang distandarisasi harus memenuhi checklist ini:

### Frontend — Index Page
- [ ] Breadcrumb hierarkis (`Home > [Group] > [Modul]`)
- [ ] Sub-header description singkat di bawah breadcrumb
- [ ] Stat Cards Header **DILARANG** di halaman list (metrik milik dashboard)
- [ ] Toolbar Search & Action **di dalam card header** (di bawah tabs jika ada)
- [ ] Filter dropdown dengan badge counter (jika > 1 filter)
- [ ] Header tabel TailAdmin (`border-b`, tanpa `bg-gray-50`, padding `px-5 py-3 sm:px-6` + `<p>` wrapper)
- [ ] Cell body padding `px-5 py-4 sm:px-6`
- [ ] Action icons feather (`eye`, `edit-3`, `trash-2`) dengan hover color
- [ ] Empty state dengan icon + title + description
- [ ] Footer pagination hanya `{{ $items->links() }}` tanpa duplikat teks "Showing..."
- [ ] Bulk select + bulk action bar (HANYA jika ada route bulk delete — jangan render jika tidak ada)
- [ ] Kolom checkbox dihapus jika tidak ada fungsionalitas bulk delete (dead UI)
- [ ] Status menggunakan `<x-ui.badge>`

### Frontend — Form Pages
- [ ] Sub-header description di bawah judul (apa yang dilakukan form ini)
- [ ] Split layout 2 kolom (`1.75fr + 380px`)
- [ ] Section header `text-base font-medium` dengan icon + border-b
- [ ] Divider `<hr>` antar section form
- [ ] Input styling TailAdmin (`h-11 rounded-lg shadow-theme-xs`)
- [ ] Right sidebar Actions (Save / Save & Create Another / Cancel)
- [ ] Error message per field (`@error`)
- [ ] Alert flash message (`<x-ui.alert>`)
- [ ] Date picker pakai `<x-form.date-picker>`

### Frontend — Show Page
- [ ] Breadcrumb hierarkis (`Home > [Modul] > [Item Name] > Details`)
- [ ] Sub-header description singkat di bawah breadcrumb
- [ ] Header card dengan icon, title, status badge & action buttons (Edit, Print, dll.)
- [ ] Sidebar cards (QR Code, Current Assignment, Quick Specs) ber-border `rounded-2xl`
- [ ] Tabs navigation pakai TailAdmin Pro pill style (`inline-flex bg-gray-100 p-1`)
- [ ] Section headers di dalam tab pakai `text-base font-medium` + `border-b pb-3`
- [ ] Header tabel di dalam tab pakai `font-medium text-theme-xs` (tanpa uppercase)
- [ ] Bebas dari efek animasi berlebihan (seperti `animate-pulse` berkelanjutan)

### Backend — Controller
- [ ] Search hanya query kolom DB nyata, bukan accessor
- [ ] Pakai `orWhereHas()` untuk search relasi
- [ ] Multi-context search — 1 keyword mencari di semua konteks relevan (kolom + relasi)
- [ ] `paginate(50)->withQueryString()`
- [ ] Validation di store/update
- [ ] Flash message: success/error
- [ ] Support `create_another` redirect
- [ ] Bulk delete route + method (jika modul butuh bulk delete)

### Backend — Route
- [ ] Route naming convention: `{module}.index`, `.create`, `.store`, `.show`, `.edit`, `.update`, `.destroy`, `.bulk-delete`
- [ ] Bulk delete route sebelum resource route
