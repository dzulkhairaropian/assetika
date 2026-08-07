# Panduan Migrasi Database — Assetika (Local & Production)

Document ini berisi langkah-langkah migrasi database untuk lingkungan **Local (fresh)** dan **Production (existing DB)**, serta aturan keamanan agar **data produksi TIDAK terhapus/ter-wipe**.

---

## ⚠️ PERATURAN UTAMA KEAMANAN DATABASE PRODUKSI

> ⛔ **JANGAN PERNAH JALANKAN COMMAND INI DI PRODUCTION**:
> - `php artisan migrate:fresh` *(Menghapus seluruh tabel dan re-run dari nol!)*
> - `php artisan migrate:refresh` *(Rollback semua lalu re-run!)*
> - `php artisan migrate:reset` *(Rollback semua migrasi!)*
>
> ✅ **COMMAND YANG AMAN UNTUK PRODUCTION**:
> - `php artisan migrate` *(Hanya menjalankan file migrasi BARU yang belum dieksekusi)*

---

## 1. Setup Local Server (Database Kosong / Awal)

Jika Anda menyetting server local baru dari nol:

### Langkah 1: Pastikan file `.env` sudah sesuai
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=assetika
DB_USERNAME=root
DB_PASSWORD=
```

### Langkah 2: Jalankan Migrasi Full
```bash
php artisan migrate
```

### Langkah 3: Seed Permission & User Admin Initial
```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UserSeeder
```

### Langkah 4: Simlink Storage (Untuk File Upload/Evidence)
```bash
php artisan storage:link
```

**Kredensial Default Login Local:**
- Email: `admin@assetika.com`
- Password: `password`

---

## 2. Setup Production Server (Database Sudah Berisi Data)

Apakah data produksi akan ter-wipe jika menjalankan `php artisan migrate`?
**JAWABAN: TIDAK PROSES TER-WIPE.** 

Laravel mencatat file migrasi mana saja yang sudah pernah berjalan di tabel `migrations`. Command `php artisan migrate` **HANYA** akan mengeksekusi file migrasi baru yang belum ada di tabel tersebut.

### Langkah Safe-Deployment di Production:

#### 1. Backup Database terlebih dahulu (Mandatory!)
```bash
# Via mysqldump
mysqldump -u [username] -p [nama_db] > backup_sebelum_migrate_$(date +%Y%m%d_%H%M%S).sql

# Atau via artisan backup jika Spatie Backup terinstall
php artisan backup:run --only-db
```

#### 2. Jalankan Migrasi Baru
```bash
php artisan migrate --force
```
*`--force` diperlukan jika `APP_ENV=production` agar Laravel tidak meminta konfirmasi manual.*

#### 3. Update Permissions (Safe / Non-destructive)
```bash
php artisan db:seed --class=PermissionSeeder --force
```
*`PermissionSeeder` menggunakan `updateOrCreate`, sehingga aman dijalankan tanpa menghapus permission/role yang sudah ada.*

#### 4. Clear Cache Laravel
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 3. Daftar File Migrasi Berdasarkan Fitur (Urutan Eksekusi)

Tabel di bawah adalah referensi urutan file migrasi yang telah dibuat di proyek ini:

| No | File Migrasi | Deskripsi Modul |
|:---|:---|:---|
| 1 | `0001_01_01_000000_create_users_table.php` | Tabel dasar Users & Password Reset |
| 2 | `2025_11_20_*_create_..._table.php` | Master Data (Companies, Departments, Locations, Products, Assets) |
| 3 | `2025_11_28_233156_create_permission_tables.php` | Spatie Roles & Permissions |
| 4 | `2026_02_16_*_create_ga_..._table.php` | Modul GA Assets |
| 5 | `2026_07_28_*_create_tickets_..._table.php` | Support Ticketing System |
| 6 | `2026_08_07_000001_create_custom_fields_table.php` | **Fase 1**: Custom Fields |
| 7 | `2026_08_07_000002_create_fieldsets_table.php` | **Fase 1**: Fieldsets |
| 8 | `2026_08_07_000003_create_fieldset_custom_field_pivot_table.php` | **Fase 1**: Fieldset-Field Pivot |
| 9 | `2026_08_07_000004_create_category_fieldset_table.php` | **Fase 1**: Category-Fieldset Pivot |
| 10 | `2026_08_07_000005_create_custom_field_values_table.php` | **Fase 1**: Custom Field Values |
| 11 | `2026_08_07_000006_create_components_table.php` | **Fase 1**: Hardware Components |
| 12 | `2026_08_07_000007_create_component_asset_table.php` | **Fase 1**: Component-Asset Pivot |
| 13 | `2026_08_07_000008_create_licenses_table.php` | **Fase 2**: Software Licenses |
| 14 | `2026_08_07_000009_create_license_seats_table.php` | **Fase 2**: License Seats |
| 15 | `2026_08_07_000010_create_kits_table.php` | **Fase 2**: Pre-defined Kits |
| 16 | `2026_08_07_000011_create_kit_pivot_tables.php` | **Fase 2**: Kit Item Pivots |
| 17 | `2026_08_07_000012_create_depreciations_table.php` | **Fase 3**: Depreciation Profiles |
| 18 | `2026_08_07_000013_add_depreciation_to_assets_table.php` | **Fase 3**: Depreciation FK on Assets |
| 19 | `2026_08_07_000014_add_signature_to_checkouts_table.php` | **Fase 4**: Signature on Checkouts |
| 20 | `2026_08_07_000015_create_eulas_table.php` | **Fase 4**: EULA Templates |
| 21 | `2026_08_07_000016_create_maintenance_types_table.php` | **High Pri**: Maintenance Types |
| 22 | `2026_08_07_000017_add_maintenance_type_to_maintenance_tables.php` | **High Pri**: Maintenance Type FK |
| 23 | `2026_08_07_000018_create_stock_counts_table.php` | **High Pri**: Stock Count / Audit Opname |

---

## 4. Problem Solving & Checklist

- **Problem:** `Class "PermissionSeeder" not found`
  - **Solusi:** Jalankan `composer dump-autoload` lalu ulangi `php artisan db:seed --class=PermissionSeeder`.
- **Problem:** `SQLSTATE[42S01]: Base table or view already exists`
  - **Solusi:** Berarti tabel tersebut sudah dibuat sebelumnya. Jangan jalankan file migrasi manual di SQL, biarkan Laravel mengelolanya lewat `php artisan migrate`.
