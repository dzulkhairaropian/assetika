# Standardisasi Pages CRUD Application

Dokumen ini berisi standar UI/UX dan struktur Blade untuk seluruh halaman CRUD pada aplikasi ini, mengacu pada implementasi baseline `resources/views/pages/assets/`.

---

## 1. Halaman Index (List Page)

### Layout Utama & Wrapper
- Gunakan `<div class="space-y-6">` sebagai container utama.
- Deklarasikan state AlpineJS (seperti selection bulk/filter modal) pada container utama.

### Filter & Action Topbar
- **Kiri**: Form `GET` search + dropdown `Filters` (popover Alpine).
- **Kanan**: Action utama (`Export Excel`, `Add / Create`).
- **Badge Active Filter**: Muncul jumlah filter yang aktif jika `request('filter_name')` terisi.

```blade
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex flex-wrap items-center gap-3" x-data="{ filterOpen: false }">
        <div class="relative flex items-center">
            <span class="absolute left-3 text-gray-400">
                <i data-feather="search" class="w-4 h-4"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                class="h-10 w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 text-sm outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900" />
        </div>

        {{-- Dropdown Filters Panel --}}
    </form>

    <div class="flex items-center gap-3">
        {{-- Export / Add buttons --}}
    </div>
</div>
```

### Bulk Action Bar
- Muncul otomatis saat item dipilih (`x-show="selected.length > 0"`).
- Berisi info jumlah terdaftar dan opsi aksi (Print, Export Selected, Delete Selected).

### Tabel Data & Action Dropdown
- Outer card: `rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]`.
- Header tabel: `bg-gray-50 dark:bg-gray-900 border-y border-gray-100 dark:border-white/[0.05]`.
- Checkbox header + row: `pl-6 pr-2 w-10 text-start`.
- Header kolom Aksi: `<th class="... text-end">Actions</th>`.
- **Kolom Aksi**: Menggunakan **Single Action Dropdown Button** (`more-vertical`) berbasis AlpineJS `x-data="{ open: false }"` dengan perataan kanan (`text-right`) agar melayang rapi di atas tabel.

```blade
<td class="px-4 sm:px-6 py-3.5 text-right">
    <div x-data="{ open: false }" class="relative inline-block text-left" @click.away="open = false">
        <button type="button" @click="open = !open" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
            <i data-feather="more-vertical" class="w-5 h-5"></i>
        </button>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 top-full z-50 mt-1 w-44 rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
            style="display: none;">
            
            <a href="{{ route('items.show', $item) }}" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                <i data-feather="eye" class="w-4 h-4 text-gray-400"></i>
                View Details
            </a>
            <a href="{{ route('items.edit', $item) }}" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                <i data-feather="edit-3" class="w-4 h-4 text-gray-400"></i>
                Edit
            </a>
            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
            <form action="{{ route('items.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 transition-colors">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                    Delete
                </button>
            </form>
        </div>
    </div>
</td>
```

### Footer & Pagination Summary
Wajib menampilkan ringkasan data di kiri dan link pagination Laravel di kanan.

```blade
<div class="border-t border-gray-100 p-6 dark:border-gray-800">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} results
        </p>
        {{ $items->links() }}
    </div>
</div>
```

---

## 2. Halaman Form (Create / Edit)

### Layout 2-Kolom Desktop
- Menggunakan grid split desktop `grid-cols-[minmax(0,1.75fr)_380px]`.
- **Kolom Kiri (Main Section)**: Informasi utama (General, Network, Specs, Custom Fields).
- **Kolom Kanan (Sidebar)**: Form pelengkap (Financial Info, Meta Data) + Card Actions (Submit, Draft, Cancel).

```blade
<form action="..." method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
    @csrf

    {{-- Left Column --}}
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            {{-- Main Form Fields --}}
        </div>
    </div>

    {{-- Right Column (Sidebar) --}}
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            {{-- Financial / Secondary Fields --}}
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white/90">
                <i data-feather="save" class="w-5 h-5 text-brand-500"></i> Actions
            </h3>
            <div class="space-y-3">
                <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save / Create</button>
                <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 transition hover:bg-brand-50">Create & Create New</button>
                <a href="..." class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700">Cancel</a>
            </div>
        </div>
    </div>
</form>
```

### Spacing & Sub-Section Form
- Berikan pembatas sub-section yang jelas menggunakan `border-b border-gray-100 pb-3 dark:border-gray-800` pada judul section `<h3>`.
- Gunakan spacing `space-y-8` pada container card form utama dan `pt-2` pada sub-section berikutnya agar judul tidak saling berdempetan.

### Field Khusus & Action Button Inline (Contoh: Generator Dummy SN)
- Tombol helper inline (seperti generate SN) menggunakan **icon-only button** yang pas dengan height input (`h-11 w-11`):
```blade
<div class="flex items-center gap-2">
    <input type="text" name="serial_number" value="{{ old('serial_number') }}" placeholder="Enter serial number" required
        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
    <button type="button" onclick="generateSN()" title="Generate Random Serial Number" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-theme-xs transition hover:bg-gray-50 hover:text-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-brand-400">
        <i data-feather="shuffle" class="w-4 h-4"></i>
    </button>
</div>
```
- Format penamaan dummy otomatis diawali prefix penanda yang jelas (contoh: `DUMMY-SN-[8random]`).

### Penanganan Partial Custom Fields
Partial yang dipakai shared (Create/Edit) harus aman dari `Undefined variable`:
```blade
const existingValues = @json(isset($asset) ? ($asset->customFieldValues?->pluck('value', 'custom_field_id') ?? []) : []);
```

---

## 3. Halaman Detail (Show / View Page)

### Top Alert & Header Card
- **Alert Khusus**: Tampilkan alert teratas jika ada kondisi kritis (contoh: *Aging Alert* lifecycle >= 4 tahun).
- **Header Card**:
  - **Kiri**: Icon ilustrasi besar (`flex h-16 w-16`), judul/tag entitas, badge status, badge sekunder (umur/kategori).
  - **Kanan**: Action bar responsif (`Print Label`, `Edit`, `Check-in / Check-out`, `Maintenance`, dll.).

```blade
<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                <i data-feather="monitor" class="h-8 w-8"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $entity->tag }}</h2>
                    <x-ui.badge :color="$statusColor" variant="light" size="sm">{{ $statusName }}</x-ui.badge>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $entity->subtitle }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Action buttons --}}
        </div>
    </div>
</div>
```

### Layout Grid Detail (1:2 Split)
- Desktop split `grid grid-cols-1 gap-6 lg:grid-cols-3`.
- **Sidebar Kiri (`lg:col-span-1`)**: Mini cards berisi informasi cepat (Current Assignment, System Info, Depreciation & Value progress bar).
- **Main Area (`lg:col-span-2`)**: Tabbed container untuk data lengkap & riwayat relasi.

### Component Tabbed Content
Gunakan AlpineJS `x-data="{ activeTab: 'details' }"` pada container utama untuk perpindahan tab tanpa reload halaman:
- Tab 1: **Details** (Informasi teknis, jaringan, lisensi, custom fields).
- Tab 2: **Movement / Activity History** (Log pergerakan atau transaksi).
- Tab 3: **Maintenance / Related Log** (Log perbaikan/perawatan).
- Tab 4: **Support Tickets / Associated Records** (Riwayat ticket terhubung + counter badge).

---

## 4. Checklist Penerapan CRUD Baru

1. **Index**:
   - [ ] Menggunakan Topbar Search + Dropdown Filter + Badge Active.
   - [ ] Menyediakan Bulk Action Bar jika fitur hapus/cetak banyak tersedia.
   - [ ] Menggunakan pagination footer dengan `Showing X to Y of Z results`.
2. **Create / Edit**:
   - [ ] Menggunakan layout 2-kolom (`xl:grid-cols-[minmax(0,1.75fr)_380px]`).
   - [ ] Memisahkan field pendukung & tombol aksi di sidebar kanan.
   - [ ] Menyediakan tombol `Create & Create New` (`name="create_another" value="1"`) pada form Create untuk mempercepat input berulang.
   - [ ] Mengatur spacing sub-section (`space-y-8`, `pt-2`, `border-b`) agar judul form tidak berdempetan.
   - [ ] Menyesuaikan helper inline button ke style icon-only (`h-11 w-11`).
   - [ ] Menjaga null safety pada partial blade.
3. **Show / Detail**:
   - [ ] Menggunakan Header Card dengan Icon Besar + Badges + Action Buttons.
   - [ ] Menggunakan layout split `grid-cols-1 lg:grid-cols-3` (Sidebar 1 kolom, Main Tab 2 kolom).
   - [ ] Menggunakan AlpineJS Tabbed Navigation untuk memisahkan Detail vs Log/History/Tickets.
