# Standards Guidelines: TailAdmin Views (Assetika)

Dokumen ini berisi standar UI/UX, komponen, skema warna, dan kriteria wajib (*must-have features*) untuk halaman **Index**, **Show**, **Create**, dan **Edit** di seluruh modul aplikasi Assetika menggunakan kustomisasi **TailAdmin / Tailwind CSS**.

---

## 1. Skema Warna & Identitas Visual (Design Tokens)

Seluruh halaman wajib mendukung **Light Mode** dan **Dark Mode** secara konsisten.

| Elemen | Light Mode | Dark Mode | Keterangan |
|:---|:---|:---|:---|
| **Background Halaman** | `bg-gray-50` / `bg-white` | `dark:bg-gray-900` | Latar belakang area konten utama |
| **Card / Table Wrapper** | `bg-white border-gray-200` | `dark:bg-white/[0.03] dark:border-gray-800` | Card pembungkus tabel/form (`rounded-2xl`) |
| **Header Tabel** | `bg-gray-50 text-gray-500` | `dark:bg-gray-900 dark:text-gray-400` | `thead` dengan font `text-theme-xs` |
| **Primary Accent (Brand)**| `bg-brand-500 hover:bg-brand-600` | `dark:bg-brand-500` | Tombol utama, link aktif |
| **Success State** | `text-emerald-700 bg-emerald-50` | `dark:text-emerald-400 dark:bg-emerald-500/10` | Status Ready / Found / Complete |
| **Warning State** | `text-amber-700 bg-amber-50` | `dark:text-amber-400 dark:bg-amber-500/10` | Aging 2-3th / Low Stock / Warning |
| **Danger State** | `text-error-700 bg-error-50` | `dark:text-error-400 dark:bg-error-500/10` | Fully Depreciated / Expired / Broken |

---

## 2. Standar Halaman INDEX (List View)

Halaman Index adalah pusat pengelolaan data. **Halaman IT Assets (`pages/assets/index.blade.php`) dijadikan acuan standar utama.**

```
[Page Breadcrumb]
[Search Bar] ------------------------- [Popover Filters] [Bulk Actions] [Add Button]
[Bulk Floating Action Bar (Tampil saat checkbox terpilih)]
[Table: Checkbox | Identitas Utama | Attributes | Badges | Actions]
[Pagination]
```

### ✅ Checklist Fungsi Wajib di INDEX:
- [ ] **Page Breadcrumb**: Komponen `<x-common.page-breadcrumb pageTitle="..." />`.
- [ ] **Search Bar**: Input pencarian dengan ikon `feather-search`, mendukung pencarian real-time via query string `search`.
- [ ] **Popover Filter Dropdown** *(Untuk data kompleks)*:
  - Tombol filter dengan badge counter filter aktif (`$activeCount`).
  - Menu melayang (Alpine.js `x-show="filterOpen"`) berisi dropdown filter spesifik.
  - Tombol *Apply Filters* & *Clear All Filters*.
- [ ] **Checkbox Selection & Bulk Action Bar**:
  - Checkbox "Select All" di `<th>` dan checkbox di setiap `<td>`.
  - Floating bar (Alpine.js) yang muncul otomatis saat `selectedRows.length > 0`.
  - Tombol aksi massal: *Bulk Delete*, *Batch Print*, *Bulk Edit*, dll.
- [ ] **Tabel Data Standard**:
  - Border & Rounded: `rounded-2xl border border-gray-200 dark:border-gray-800`.
  - Row Hover: Efek hover halus pada baris tabel.
  - Cell Typography: `text-theme-sm` untuk isi, `text-theme-xs` untuk label pendukung.
- [ ] **Visual Badges**: Status, Umur (Aging), Garansi, atau Stok menggunakan komponen `<x-ui.badge>` dengan warna semantik.
- [ ] **Kolom Aksi (Actions)**:
  - Ikon konsisten (`feather-eye` untuk Detail, `feather-edit-3` untuk Edit, `feather-trash-2` untuk Delete).
  - Konfirmasi SweetAlert / JS Confirm sebelum hapus (`onsubmit="return confirm('...')"`).
- [ ] **Empty State**: Tampilan visual terstruktur jika data kosong (ikon + pesan deskriptif + tombol buat data).
- [ ] **Pagination**: `{{ $data->links() }}` terbungkus dalam `border-t border-gray-100 dark:border-gray-800`.

---

## 3. Standar Halaman SHOW (Detail View)

Halaman Show menyajikan informasi rinci entitas, riwayat transaksi, dan aksi cepat.

```
[Page Breadcrumb]
[Header Card: Icon/Avatar + Name + Status Badges + Quick Action Buttons]
-------------------------------------------------------------------------
[Grid Layout: 1/3 Left (Info Ringkas & Card Khusus) | 2/3 Right (Tabs / Table History)]
```

### ✅ Checklist Fungsi Wajib di SHOW:
- [ ] **Header Ringkasan**:
  - Nama Utama (Heading `text-xl/2xl font-bold`).
  - Badge Status & Identitas Kunci (Asset Tag / Code / Serial).
  - Tombol Aksi Cepat: *Edit*, *Print Label/QR*, *Checkout/Checkin*, *Maintenance*.
- [ ] **Layout Grid 2-Kolom (Responsive)**:
  - **Kolom Kiri (1/3)**: Detail spesifikasi, lokasi, data finansial/depresiasi, & QR Code.
  - **Kolom Kanan (2/3)**: Tab navigasi (Alpine.js `activeTab`) untuk memisahkan data (contoh: *Details*, *Movement History*, *Maintenance History*, *Assignments*).
- [ ] **Widget Tambahan (Modul Spesifik)**:
  - **Assets**: Progress Bar Depresiasi (% lunas susut & sisa bulan).
  - **Licenses**: Tabel alokasi seat terpakai vs sisa + form checkout seat cepat.
  - **Components**: Jumlah stok tersedia + form checkout komponen ke aset.
  - **Stock Counts**: Tabel daftar verifikasi item (*Found*, *Missing*, *Damaged*).

---

## 4. Standar Halaman CREATE & EDIT (Form View)

Halaman Form harus terstruktur, intuitif, dan tidak membingungkan pengguna.

```
[Page Breadcrumb]
[Form Wrapper Card: rounded-2xl p-6]
  ├── [Section Heading 1: Ikon + Judul Seksi]
  ├── [Grid Input Fields (2 Kolom di Desktop, 1 Kolom di Mobile)]
  ├── [Section Heading 2 (Jika ada seksi tambahan)]
  └── [Action Footer: Cancel | Create & New | Submit]
```

### ✅ Checklist Fungsi Wajib di CREATE & EDIT:
- [ ] **Sectioning & Heading**: Form dibagi menjadi seksi logis (misal: *General Info*, *Specifications*, *Financial Info*) menggunakan Heading `text-lg font-semibold` dengan ikon Feather.
- [ ] **Grid Input Fields**:
  - Menggunakan layout `grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2`.
  - Textarea/Notes mengambil full width (`lg:col-span-2`).
- [ ] **Label & Tanda Wajib**:
  - Label jelas dengan font `text-sm font-medium text-gray-700 dark:text-gray-400`.
  - Tanda bintang merah `<span class="text-error-500">*</span>` untuk field wajib (*required*).
- [ ] **Indikator Validation Error**:
  - Menampilkan pesan error di bawah input jika validasi gagal:
    `@error('field_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror`
- [ ] **Preservasi Nilai Form**:
  - Menggunakan `old('field_name', $model->field_name ?? '')` agar data tidak hilang saat validasi gagal.
- [ ] **Form Control Kustom**:
  - Master dropdown menggunakan data dinamis (Category, Location, Brand).
  - Tipe input khusus (Date Picker, Currency dengan prefix "Rp", Dropdown dinamis).
- [ ] **Footer Tombol Aksi (Sticky/Right-aligned)**:
  - Tombol **Cancel**: Warna netral (`border-gray-300 bg-white text-gray-700`).
  - Tombol **Create & Create New** (Hanya di Create): `border-brand-500 text-brand-500 bg-white` dengan nilai `name="create_another" value="1"`.
  - Tombol **Submit Utama** (Create/Update): `bg-brand-500 text-white hover:bg-brand-600`.

---

## 5. Struktur Struktur Folder View

```
resources/views/
├── components/
│   ├── common/         # Breadcrumb, Preloader, Header, Sidebar
│   └── ui/             # Alert, Badge, Button, Modal
├── layouts/
│   ├── app.blade.php   # Main Layout
│   └── sidebar.blade.php
└── pages/
    ├── assets/         # IT Assets View
    ├── ga-assets/      # GA Assets View
    ├── components/     # Component View
    ├── licenses/       # Software Licenses View
    ├── kits/           # Pre-defined Kits View
    ├── stock-counts/   # Audit Opname View
    ├── requests/       # Self-Service Requests View
    └── master/         # Master Data Views (Companies, Custom Fields, EULAs, dll)
```

---

## 6. Contoh Templat Baris Kode (Boilerplate)

### Form Field Standards:
```html
<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
        Field Name <span class="text-error-500">*</span>
    </label>
    <input type="text" name="field_name" value="{{ old('field_name', $model->field_name ?? '') }}" required
        placeholder="Enter field..."
        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
    @error('field_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
</div>
```

---
*Dokumen ini menjadi standar baku pengembangan view Assetika.*
