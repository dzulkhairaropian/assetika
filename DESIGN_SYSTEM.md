# DESIGN_SYSTEM.md - Assetika Enterprise UI Specification

Dokumen panduan arsitektur antarmuka (UI/UX) dan sistem desain **Assetika**. Gunakan dokumen ini sebagai referensi utama untuk membangun aplikasi baru agar memiliki estetika, tata letak, warna, tipografi, dan pengalaman pengguna yang identik.

---

## 1. Brand & Creative Direction

- **Nama Platform**: Assetika
- **Filosofi UI**: *"The Precision Asset Console"* — Berorientasi pada kerapatan data (high data-density), kejelasan hirarki visual, dan kemudahan scanning.
- **Prinsip Utama**:
  1. **Clean & High-Contrast**: Mengutamakan kejelasan teks dan status badge dibanding dekorasi berlebih.
  2. **Consistent Spatial System**: Grid spacing ketat (4px/8px multiplier).
  3. **Full Native Dark Mode**: Setiap komponen wajib mendukung mode terang dan gelap (`dark:` variant).

---

## 2. Palet Warna (Color Palette)

Aplikasi menggunakan kombinasi warna netral dingin (*Cool Gray*) dengan warna aksen *Electric Indigo*.

### Core Colors
| Token / Variable | Hex Code (Light) | Dark Mode | Penggunaan |
| :--- | :--- | :--- | :--- |
| **Primary Accent (`brand-500`)** | `#465fff` | `#465fff` | Tombol utama, tab aktif, ring focus, ikon aktif |
| **Primary Hover (`brand-600`)** | `#3641f5` | `#3641f5` | Hover state tombol utama |
| **Primary Soft Tint (`brand-50`)**| `#f0f2ff` | `rgba(70,95,255,0.1)` | Background badge aktif, item terpilih, alert info |
| **Page Background** | `#f9fafb` (`gray-50`) | `#0f172a` (`gray-900`) | Background kanvas aplikasi |
| **Card / Panel Surface** | `#ffffff` | `#1e293b` (`gray-800/900`) | Background kontainer, tabel, modal, sidebar |
| **Primary Text** | `#101828` (`gray-900`)| `#ffffff` | Judul, teks utama, angka data |
| **Muted / Secondary Text** | `#667085` (`gray-500`)| `#94a3b8` (`gray-400`) | Header tabel, deskripsi field, subtitle |
| **Subtle Border** | `#e4e7ec` (`gray-200`)| `#334155` (`gray-800`) | Garis tepi tabel, divider, outline input |

### Status Signals (Semantic Colors)
| Status | Background Soft | Text / Icon Color | Penggunaan |
| :--- | :--- | :--- | :--- |
| **Success / Available** | `bg-emerald-50` / `dark:bg-emerald-500/10` | `text-emerald-600` / `dark:text-emerald-400` | Asset Ready, Approved, Active |
| **Warning / Maintenance** | `bg-amber-50` / `dark:bg-amber-500/10` | `text-amber-600` / `dark:text-amber-400` | Maintenance Pending, Expiring, Low Stock |
| **Danger / Retired** | `bg-rose-50` / `dark:bg-rose-500/10` | `text-rose-600` / `dark:text-rose-400` | Broken, Retired, Rejected, Bulk Delete |
| **Info / Pending** | `bg-blue-50` / `dark:bg-blue-500/10` | `text-blue-600` / `dark:text-blue-400` | Request Pending, Draft, In Progress |

---

## 3. Tipografi (Typography)

- **Font Family Utama**: `Outfit`, sans-serif (Google Fonts).
- **Font Code / Monospace**: `Fira Code` / `ui-monospace` (Untuk Tag Aset, Kode SN, Kode Invoice).

### Scale & Hierarchy
| Style Name | Font Size | Weight | Line Height | Usage |
| :--- | :--- | :--- | :--- | :--- |
| **Display / Hero** | `32px` (`2xl`) | Bold (700) | `40px` | Angka Stat Widget Utama |
| **Page Title** | `20px` (`xl`) | SemiBold (600) | `28px` | Header Halaman Utama (`x-common.page-breadcrumb`) |
| **Card Title** | `16px` (`base`)| SemiBold (600) | `24px` | Judul Card, Modal Header |
| **Body Standard** | `14px` (`sm`) | Regular (400) / Medium (500) | `20px` | Teks Konten, Input Form |
| **Table & Compact** | `12px` (`xs`) | Regular (400) / Medium (500) | `18px` | Baris Tabel, Teks Deskripsi |
| **Micro Tag / Badge**| `11px` | SemiBold (600) | `16px` | Status Badge, Tagging |

---

## 4. Struktur Layout & Grid (Layout System)

### A. App Shell Structure & Anatomy

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ APP HEADER (Fixed Top: 70px, z-index: 9990)                                            │
│ [Hamburger Toggle] [Logo]              [Global Search]   [Theme Mode] [Notif] [User] │
├────────────────────────┬───────────────────────────────────────────────────────────────┤
│ SIDEBAR NAVIGATION     │ MAIN CONTENT CONTAINER (p-4 md:p-6, max-w-2xl)                │
│ (Fixed Left: 290px/90px│                                                               │
│ z-index: 99999)        │  ┌─────────────────────────────────────────────────────────┐  │
│                        │  │ PageBreadcrumb Title & Subtitle                         │  │
│ - Brand Logo           │  └─────────────────────────────────────────────────────────┘  │
│ - Menu Group Headers   │  ┌─────────────────────────────────────────────────────────┐  │
│ - Nav Items (Icons+Text│  │ Bulk Action Floating Bar (x-show="selected.length > 0") │  │
│ - Collapsed Hover state│  └─────────────────────────────────────────────────────────┘  │
│ - User Mini Panel (Bottom│ ┌─────────────────────────────────────────────────────────┐  │
│                        │  │ Main Card Surface (rounded-xl, border, shadow-sm)       │  │
│                        │  │ ┌─────────────────────────────────────────────────────┐ │  │
│                        │  │ │ Toolbar Header (Search input + Action buttons)    │ │  │
│                        │  │ ├─────────────────────────────────────────────────────┤ │  │
│                        │  │ │ Responsive Data Table / Content Form Grid           │ │  │
│                        │  │ ├─────────────────────────────────────────────────────┤ │  │
│                        │  │ │ Table Footer & Pagination Links                     │ │  │
│                        │  │ └─────────────────────────────────────────────────────┘ │  │
│                        │  └─────────────────────────────────────────────────────────┘  │
└────────────────────────┴───────────────────────────────────────────────────────────────┘
```

1. **Sidebar Navigation (Fixed Left)**:
   - Lebar Expanded: `290px` (`w-[290px]`).
   - Lebar Collapsed: `90px` (`w-[90px]`).
   - Fitur Hover Expand, Dynamic Role Submenu Filter, & Mobile Overlay.
   - User Mini Panel di bagian bawah sidebar (`<x-common.sidebar-user-panel />`).
2. **App Header (Fixed Top)**:
   - Tinggi: `70px` (`sticky top-0 z-[9990]`).
   - Sisi Kiri: Desktop Hamburger Toggle (`xl:flex`), Mobile Menu Button (`xl:hidden`), Brand Logo.
   - Sisi Kanan: Theme Toggle (Dark/Light mode switch), Notification Bell Dropdown, User Profile Dropdown & Logout.
3. **Main Content Container**:
   - Spacing: `p-4 md:p-6`.
   - Max Width: `max-w-7xl` / `max-w-(--breakpoint-2xl)`.

### B. Standard Page Layout Patterns

#### 1. Index / List Page Pattern (Table Layout)
- **Header**: `PageBreadcrumb` + Subtitle deskripsi modul.
- **Bulk Action Bar**: Tampil melayang saat ada item di-select (`selectedItems.length > 0`).
- **Card Wrapper**: Container `rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900`.
- **Toolbar**: Search input (kiri) + Action button (kanan).
- **Data Table**: Header border-y, row hover `hover:bg-gray-50/50 dark:hover:bg-white/[0.02]`.
- **Footer**: Pagination links.

#### 2. Form Page Pattern (Create / Edit Layout)
- **Grid Layout**: 2-kolom asimetris (`grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]`).
- **Main Form Card (Kiri)**: Form field utama terbagi dalam section bermodel border-b header.
- **Action Card (Kanan)**: Tombol `Submit`, `Create & Create Another`, dan `Cancel` (sticky/fixed di samping).

#### 3. Dashboard Pattern (Metrics Grid Layout)
- **Top Row**: 4-Column Stat Cards (`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6`).
- **Middle Row**: Charts & Analytics Grid (`grid grid-cols-1 lg:grid-cols-3 gap-6`) (2-kolom chart + 1-kolom recent activities).
- **Bottom Row**: Table preview / Quick Action list.

---

## 5. Komponen UI Standar (UI Component Library)

### A. Primary Button (`Add / Submit / Action`)
```html
<button class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
    <i data-feather="plus" class="w-3.5 h-3.5"></i>
    Add Item
</button>
```

### B. Secondary / Cancel Button
```html
<a href="#" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
    Cancel
</a>
```

### C. Form Input & Select
```html
<div class="space-y-1">
    <label class="block text-xs font-medium text-gray-700 dark:text-gray-400">
        Item Name <span class="text-rose-500">*</span>
    </label>
    <input type="text" 
        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition"
        placeholder="Enter item name..." />
</div>
```

### D. Status Badge (Pill Badge)
```html
<!-- Success -->
<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
    Available
</span>

<!-- Pending -->
<span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
    In Maintenance
</span>
```

### E. Data Table Standard Layout
- Header dengan background subtle `bg-gray-50/50 dark:bg-gray-800/30`.
- Text Header: `text-xs font-medium text-gray-500 dark:text-gray-400`.
- Row Hover effect: `hover:bg-gray-50/50 dark:hover:bg-white/[0.02]`.
- Checkbox bulk action di kolom pertama.

---

## 6. Elevasi, Shadow & Corner Radius

- **Corner Radius**:
  - `rounded-lg` (`8px`) untuk Tombol, Input Form, Badge, Dropdown.
  - `rounded-xl` (`12px`) untuk Card Container, Card Header Toolbar.
  - `rounded-2xl` (`16px`) untuk Modal & Floating Panel.
- **Shadow**:
  - `shadow-sm`: Digunakan pada Card & Form Controls.
  - `shadow-xl` / `shadow-2xl`: Digunakan untuk Modal, Toast Notification & Floating Greeting.

---

## 7. Icons & Visual Assets

- **Icon Set**: **Feather Icons** (`data-feather="icon-name"`) & **Lucide Icons**.
- **Icon Sizing Standar**:
  - Tabel / Action Icon: `w-3.5 h-3.5` atau `w-4 h-4`.
  - Sidebar / Header Icon: `w-5 h-5` atau `w-6 h-6`.

---

## 8. Panduan Integrasi untuk App Selanjutnya

Ketika membuat project baru dengan gaya Assetika:
1. Pakai **Tailwind CSS v4** dengan ekstensi warna `brand-500: #465fff`.
2. Gunakan font Google **Outfit** sebagai font default di `body`.
3. Pasang **Alpine.js** untuk manajemen state UI (Sidebar, Dropdown, Modal, Dark Mode Toggle).
4. Gunakan komponen Blade / JSX seragam untuk `PageBreadcrumb`, `TableToolbar`, `BulkActionBar`, dan `AlertNotification`.
