# Product Requirement Document (PRD): Migrasi Assetika ke Next.js

Document Status: Draft / Migration Architecture Guide  
Source Project: Assetika (Laravel 13 + Tailwind CSS + Alpine.js)  
Target Project: Assetika Next.js Stack (Next.js App Router + TypeScript + Tailwind CSS + shadcn/ui + Prisma/Drizzle ORM)  
Created Date: 2026-08-20  

---

## 1. Executive Summary & Application Overview

**Assetika** adalah sistem manajemen aset terpadu (Enterprise Asset & Maintenance System) yang mencakup manajemen IT Assets, General Affairs (GA) Assets, Consumables, Software Licenses, Hardware Components, Pre-defined Kits, Stock Audit (Opname), Self-Service Asset Requests, serta Helpdesk Ticketing System dengan SLA tracking.

Dokumen PRD ini disusun untuk memetakan arsitektur dasar, alur kerja (workflow), serta seluruh komponen backend/frontend dari aplikasi Laravel eksisting ke dalam ekosistem **Next.js**.

---

## 2. Dynamic Target Stack (Laravel vs Next.js Mapping)

| Domain | Existing Laravel Architecture | Target Next.js Architecture |
| :--- | :--- | :--- |
| **Framework & Routing** | Laravel 13 (Blade Views, `routes/web.php`) | Next.js 14/15 App Router (`app/` directory) |
| **Language** | PHP 8.3 | TypeScript |
| **Styling & UI Components** | Tailwind CSS v4 + Alpine.js (TailAdmin Template) | Tailwind CSS + shadcn/ui + Lucide / Feather Icons |
| **Database ORM** | Eloquent ORM + MySQL/PostgreSQL | Prisma ORM / Drizzle ORM |
| **Authentication & AuthZ** | Session Auth + Spatie Permission | NextAuth.js / Auth.js (JWT/Session) + RBAC Middleware |
| **State & Data Fetching** | Server-side Blade rendering | React Server Components (RSC) + Server Actions & TanStack Query |
| **Document/PDF Generation** | `barryvdh/laravel-dompdf` | `@react-pdf/renderer` / Puppeteer PDF API |
| **Excel Export/Import** | `maatwebsite/excel` | `exceljs` / `xlsx` |
| **Background Jobs/Queue** | Laravel Queue + Backup (Spatie) | Node-Cron / BullMQ + Redis |

---

## 3. Core Modules & System Workflows

### 3.1 IT Asset & Component Lifecycle Workflow
1. **Pendaftaran Asset**: Pengguna membuat record Aset Baru. Sistem secara otomatis generate `asset_tag` (format: `[Company]/[Category]/[yyMMdd]/[Seq]`) dan `device_name` (format: `[Prefix]-[Seq]`) melalui hook lifecycle.
2. **Checkout (Peminjaman/Penyerahan)**: Aset diserahkan ke User, Department, atau Location. Status aset diperbarui, record `AssetCheckout` dibuat, dan Surat BAST (Berita Acara Serah Terima) dibuat dalam format PDF.
3. **Checkin (Pengembalian)**: Aset dikembalikan. Tanggal pengembalian dicatat, `current_checkout_id` pada Aset diset ke `null`, dan aset kembali berstatus `Available`.
4. **Maintenance (Perawatan/Perbaikan)**: Aset masuk status perawatan via `AssetMaintenance`. Dicatat tipe maintenance, tanggal mulai, estimasi biaya, dan vendor.
5. **Retirement / Disposal**: Aset yang rusak permanen atau habis masa pakai distatuskan `Retired` dengan menyertakan bukti (*evidence*) serta alasan pemusnahan.

### 3.2 General Affairs (GA) Asset Workflow
- Mengelola aset non-IT seperti kendaraan kantor, perabotan, dan alat perlengkapan gedung.
- Mendukung fitur pelacakan BAST serah terima GA (`ga_bast`), sub-kategori spesifik GA, pelacakan histori pemeliharaan (`GaMaintenanceLog`), perhitungan masa guna aset (`useful_life_years`), serta depresiasi otomatis.

### 3.3 Consumable & License Management Workflow
- **Consumable (Barang Habis Pakai)**: Pembuatan item -> Pengajuan oleh User/Dept (`ConsumableRequest`) -> Approval -> Deduct Stock / Restock via `ConsumableTransaction`.
- **Software Licenses**: Manajemen Lisensi -> Alokasi License Seat per User (`LicenseSeat`) -> Checkout/Checkin Seat.

### 3.4 Helpdesk Ticketing System & SLA Workflow
- **Tiket Baru**: User/Admin membuat tiket masalah terkait Aset atau Layanan IT/GA.
- **Assignment & SLA**: Tiket menetapkan Prioritas, Kategori/Subkategori, Tipe Tiket, SLA Status, serta Estimasi Jam Pengerjaan.
- **Komentar & Histori**: Interaksi antar agen & pelapor melalui `TicketComment`, lampiran berkas via `Attachment`, dan audit log pergerakan status via `TicketHistory`.

### 3.5 Stock Count (Audit Opname) Workflow
- Inisiasi Audit Stock -> Tambah daftar aset yang akan diaudit -> Scan QR/Manual Verify per item (`StockCountItem`) -> Tandai perbedaan (match/mismatch) -> Mark Complete.

---

## 4. Database Schema & Models Architecture (Prisma/Drizzle Spec)

Berikut adalah daftar **Entity / Model** utama yang perlu dimigrasikan ke schema ORM target (e.g., Prisma Schema / Drizzle Tables), disusun berurutan dari **Core Domain** ke **Domain-Specific**:

### 4.1 Core Domain Models (Users, Companies, Organizations)

#### 4.1.1 Users & Authentication
1. **User** (core)
   - `id`, `name`, `email`, `password`, `department_id`, `position_id`, `location_id`, `is_active`, `timestamps`.
   - *Relasi*: `department`, `position`, `location`, `checkouts`, `assignments`, `tickets`, `stock_counts`, `consumable_requests`.

#### 4.1.2 Company Hierarchy
2. **Company** (core)
   - `id`, `name`, `code`, `email`, `phone`, `website`, `address`, `npwp_number`, `npwp_name`, `nib`, `bank_name`, `bank_account_number`, `bank_account_name`, `timestamps`, `softDeletes`.
   - *Relasi*: `departments`, `locations`.

3. **Department** (core)
   - `id`, `company_id`, `name`, `code`, `description`, `timestamps`, `softDeletes`.
   - *Relasi*: `assets`, `users`, `consumable_requests`, `ga_asset_assignments`.

4. **Position** (core)
   - `id`, `name`, `code`, `description`, `timestamps`, `softDeletes`.
   - *Relasi*: `users`.

#### 4.1.3 Location & Infrastructure
5. **Location** (core)
   - `id`, `name`, `description`, `company_id`, `timestamps`, `softDeletes`.
   - *Relasi*: `assets`, `users`, `consumable_transactions`, `ga_asset_assignments`, `stock_counts`.

6. **Placement** (legacy – keep schema for backwards compatibility)
   - `id`, `name`, `description`, `timestamps`, `softDeletes`.
   - *Note*: Relasi ke `locations` sudah di-drop, tabel mungkin sudah tidak digunakan.

### 4.2 Master Data Models (Products, Brands, Categories, Statuses)

#### 4.2.1 Product Catalog
7. **Brand** (master)
   - `id`, `name`, `code`, `website`, `description`, `timestamps`, `softDeletes`.
   - *Relasi*: `products`, `operating_systems`.

8. **MasterCategory** (master)
   - `id`, `name`, `description`, `timestamps`, `softDeletes`.
   - *Relasi*: `categories`, `statuses`.

9. **Category** (master)
   - `id`, `master_category_id`, `name`, `code`, `description`, `timestamps`, `softDeletes`, `unique(code)`.
   - *Relasi*: `products`, `assets`, `consumables`, `components`, `kits`, `fieldsets`, `custom_fields`, `eulas`, `ticket_categories`, `ticket_subcategories`.

10. **Product** (master)
    - `id`, `category_id`, `brand_id`, `master_category_id`, `name`, `product_type`, `description`, `timestamps`, `softDeletes`.
    - *Relasi*: `assets`, `consumables`.

#### 4.2.2 Status & Configuration
11. **Status** (master)
    - `id`, `name`, `code`, `type`, `color`, `icon`, `master_category_id`, `is_deployable`, `description`, `timestamps`, `softDeletes`.
    - *Relasi*: `assets`, `consumables`, `tickets`, `stock_count_items`.

12. **Depreciation** (master)
    - `id`, `name`, `months`, `timestamps`.
    - *Relasi*: `assets`.

#### 4.2.3 Vendor & Operating System
13. **Vendor** (master)
    - `id`, `name`, `code`, `email`, `phone`, `website`, `address`, `tax_number`, `bank_name`, `bank_account_number`, `bank_account_name`, `timestamps`, `softDeletes`.
    - *Relasi*: `assets`, `maintenances`, `ga_maintenance_logs`.

14. **OperatingSystem** (master)
    - `id`, `brand_id`, `name`, `version`, `description`, `timestamps`, `softDeletes`.
    - *Relasi*: `assets`.

### 4.3 Asset & Transaction Models

#### 4.3.1 IT Assets
15. **Asset** (core domain)
    - `id`, `parent_asset_id`, `product_id`, `operating_system_id`, `status_id`, `vendor_id`, `asset_tag`, `serial_number`, `product_number`, `device_name`, `user_name`, `license_windows`, `license_office`, `mac_address_wireless`, `mac_address_ethernet`, `specification`, `accessories`, `purchase_date`, `release_date`, `warranty_expiry_date`, `purchase_cost`, `invoice_number`, `notes`, `retired_at`, `retirement_reason`, `retirement_notes`, `assigned_to_user_id`, `department_id`, `location_id`, `current_checkout_id`, `depreciation_id`, `timestamps`, `softDeletes`.
    - *Relasi*: `parentAsset`, `peripherals`, `department`, `product`, `operatingSystem`, `status`, `location`, `vendor`, `assignedToUser`, `currentCheckout`, `checkouts`, `checkoutHistory`, `maintenances`, `tickets`, `components`, `depreciation`.

#### 4.3.2 Asset Checkouts & History
16. **AssetCheckout** (audit & audit trail)
    - `id`, `asset_id`, `checked_out_by_user_id`, `checked_out_to_user_id`, `supervisor_user_id`, `department_id`, `location_id`, `checkout_evidence_file_path`, `checkout_evidence_file_name`, `checked_out_at`, `checkout_notes`, `checked_in_at`, `checked_in_by_user_id`, `checkin_evidence_file_path`, `checkin_evidence_file_name`, `checkin_notes`, `condition_report`, `timestamps`.
    - *Relasi*: `asset`, `checkedOutByUser`, `checkedOutToUser`, `supervisor`, `department`, `location`.
    - *Index*: `asset_id+checked_out_at`, `checked_out_to_user_id+checked_out_at`, `supervisor_user_id`.

#### 4.3.3 Asset Maintenance
17. **AssetMaintenance** (core domain)
    - `id`, `asset_id`, `maintenance_type_id`, `service_provider`, `start_date`, `completion_date`, `cost`, `description`, `evidence_file_path`, `notes`, `timestamps`, `softDeletes`.
    - *Relasi*: `asset`, `maintenanceType`.

#### 4.3.4 Asset Requests
18. **AssetRequest** (self-service)
    - `id`, `user_id`, `requestable_type`, `requestable_id`, `qty`, `status` (`pending/approved/rejected/canceled`), `notes`, `admin_notes`, `processed_by_user_id`, `processed_at`, `timestamps`.
    - *Relasi*: `user`, `processedBy`.

#### 4.3.5 Components
19. **Component** (core domain)
    - `id`, `category_id`, `name`, `manufacturer`, `model_number`, `serial`, `qty`, `min_qty`, `purchase_cost`, `location_id`, `notes`, `image`, `timestamps`, `softDeletes`.
    - *Relasi*: `category`, `location`, `assets`.

20. **ComponentAsset** (pivot table)
    - `id`, `component_id`, `asset_id`, `qty`, `checked_out_at`, `timestamps`.
    - *Relasi*: `component`, `asset`.

### 4.4 Consumable, License & Kit Models

#### 4.4.1 Consumables
21. **Consumable** (core domain)
    - `id`, `product_id`, `status_id`, `notes`, `quantity`, `low_threshold`, `purchase_cost`, `timestamps`, `softDeletes`.
    - *Relasi*: `product`, `status`, `transactions`, `requests`.

22. **ConsumableTransaction** (audit trail)
    - `id`, `consumable_id`, `user_id`, `checked_out_to_user_id`, `type` (`restock/checkout`), `quantity`, `quantity_before`, `quantity_after`, `evidence_file_path`, `notes`, `transaction_date`, `timestamps`.
    - *Relasi*: `consumable`, `user`.

23. **ConsumableRequest** (self-service)
    - `id`, `request_number`, `requester_id`, `department_id`, `location_id`, `processed_by`, `status` (`pending/approved/rejected/completed`), `requested_at`, `processed_at`, `notes`, `admin_notes`, `timestamps`.
    - *Relasi*: `requester`, `department`, `location`, `processedBy`, `items`.

24. **ConsumableRequestItem** (line items)
    - `id`, `consumable_request_id`, `consumable_id`, `quantity_requested`, `quantity_fulfilled`, `notes`, `timestamps`.
    - *Relasi*: `consumableRequest`, `consumable`.

#### 4.4.2 Licenses
25. **License** (core domain)
    - `id`, `category_id`, `name`, `product_key`, `seats`, `license_email`, `manufacturer`, `purchase_date`, `expiration_date`, `purchase_cost`, `notes`, `timestamps`.
    - *Relasi*: `category`, `seats`.

26. **LicenseSeat** (seat allocation)
    - `id`, `license_id`, `assigned_to_type`, `assigned_to_id`, `checked_out_at`, `notes`, `timestamps`.
    - *Relasi*: `license`.

#### 4.4.3 Kits
27. **Kit** (core domain)
    - `id`, `name`, `description`, `timestamps`.
    - *Relasi*: `assets`, `licenses`, `components`, `consumables`.

28. **KitAsset** (pivot)
    - `id`, `kit_id`, `asset_id`, `qty`, `timestamps`.

29. **KitLicense** (pivot)
    - `id`, `kit_id`, `license_id`, `qty`, `timestamps`.

30. **KitComponent** (pivot)
    - `id`, `kit_id`, `component_id`, `qty`, `timestamps`.

31. **KitConsumable** (pivot)
    - `id`, `kit_id`, `consumable_id`, `qty`, `timestamps`.

### 4.5 General Affairs (GA) Models

#### 4.5.1 GA Asset Hierarchy
32. **GaAssetCategory** (GA master)
    - `id`, `name`, `code_prefix`, `depreciation_method`, `timestamps`.
    - *Relasi*: `assets`, `subcategories`.

33. **GaAssetSubcategory** (GA master)
    - `id`, `ga_asset_category_id`, `name`, `timestamps`.
    - *Relasi*: `ga_assets`.

#### 4.5.2 GA Asset Core
34. **GaAsset** (GA core domain)
    - `id`, `category_id`, `subcategory_id`, `name`, `asset_code`, `inventory_number`, `brand`, `model`, `serial_number`, `acquisition_year`, `acquisition_source` (`Pembelian/Hibah/Sewa/Lainnya`), `purchase_date`, `vendor_name`, `invoice_number`, `purchase_price`, `additional_cost`, `warranty_period_month`, `warranty_expiry_date`, `status` (string), `qty`, `retired_at`, `retirement_reason`, `retirement_notes`, `retirement_evidence_path`, `timestamps`, `softDeletes`.
    - *Relasi*: `category`, `subcategory`, `vehicle`, `currentHolder`, `assignments`, `maintenanceLogs`.

35. **GaAssetVehicle** (GA vehicle ext)
    - `id`, `asset_id`, `license_plate`, `chassis_number`, `engine_number`, `bpkb_number`, `stnk_number`, `tax_due_date`, `last_service_date`, `last_odometer`, `insurance_policy_number`, `insurance_expiry_date`, `fuel_consumption`, `timestamps`.
    - *Relasi*: `asset`.

36. **GaAssetAssignment** (GA checkout)
    - `id`, `asset_id`, `user_id`, `assigned_at`, `returned_at`, `notes`, `condition_on_return`, `timestamps`.
    - *Relasi*: `asset`, `user`.

37. **GaMaintenanceLog** (GA maintenance)
    - `id`, `asset_id`, `maintenance_type_id`, `type`, `cost`, `vendor`, `scheduled_date`, `completed_date`, `notes`, `timestamps`.
    - *Relasi*: `asset`, `maintenanceType`.

### 4.6 Custom Fields Engine

#### 4.6.1 Custom Field System
38. **CustomField** (core domain)
    - `id`, `name`, `field_type` (`text/number/date/boolean/select/radio`), `field_values` (JSON), `help_text`, `default_value`, `order`, `timestamps`.

39. **Fieldset** (core domain)
    - `id`, `name`, `timestamps`.
    - *Relasi*: `custom_fields`, `categories`.

40. **CustomFieldFieldset** (pivot)
    - `id`, `fieldset_id`, `custom_field_id`, `order`, `is_required`, `timestamps`.
    - *Relasi*: `fieldset`, `customField`.

41. **CategoryFieldset** (pivot)
    - `id`, `category_id`, `fieldset_id`, `timestamps`.
    - *Relasi*: `category`, `fieldset`.

42. **CustomFieldValue** (value storage)
    - `id`, `custom_field_id`, `valuable_type`, `valuable_id`, `value`, `timestamps`.
    - *Relasi*: `customField`.

### 4.7 Ticketing System Models

#### 4.7.1 Ticket Lookup Tables
43. **TicketCategory** (lookup)
    - `id`, `name`, `code`, `description`, `is_active`, `timestamps`, `softDeletes`.
    - *Relasi*: `tickets`, `subcategories`.

44. **TicketSubcategory** (lookup)
    - `id`, `ticket_category_id`, `name`, `code`, `description`, `is_active`, `timestamps`, `softDeletes`.
    - *Relasi*: `tickets`.

45. **Priority** (lookup)
    - `id`, `name`, `code`, `label`, `color`, `icon`, `response_time_minutes`, `resolution_time_hours`, `description`, `example_cases`, `sort_order`, `is_active`, `timestamps`, `softDeletes`.
    - *Relasi*: `tickets`.

46. **TicketStatus** (lookup)
    - `id`, `name`, `code`, `color`, `icon`, `description`, `sort_order`, `is_active`, `is_closed`, `timestamps`, `softDeletes`.
    - *Relasi*: `tickets`.

47. **SlaStatus** (lookup)
    - `id`, `name`, `code`, `color`, `icon`, `description`, `sort_order`, `is_active`, `timestamps`, `softDeletes`.
    - *Relasi*: `tickets`.

48. **Source** (lookup)
    - `id`, `name`, `code`, `color`, `icon`, `description`, `sort_order`, `is_active`, `timestamps`, `softDeletes`.
    - *Relasi*: `tickets`.

49. **Label** (lookup)
    - `id`, `name`, `slug`, `color`, `icon`, `description`, `is_active`, `sort_order`, `timestamps`, `softDeletes`.
    - *Relasi*: `tickets`.

50. **TicketType** (lookup)
    - `id`, `name`, `code`, `color`, `icon`, `description`, `sort_order`, `is_active`, `timestamps`, `softDeletes`.
    - *Relasi*: `tickets`.

#### 4.7.2 Ticket Core
51. **Ticket** (core domain)
    - `id`, `request_id`, `ticket_type_id`, `requester_user_id`, `request_date`, `request_time`, `requester_name`, `requester_email`, `requester_company_id`, `requester_department_id`, `asset_id`, `category_id`, `priority_id`, `label_id`, `source_id`, `problem_description`, `assigned_to_id`, `created_by_id`, `completed_date`, `completed_time`, `response_time_minutes`, `resolution_time_hours`, `resolution_notes`, `status_id`, `sla_status_id`, `internal_notes`, `timestamps`, `softDeletes`.
    - *Relasi*: `ticketType`, `requester`, `requesterCompany`, `requesterDepartment`, `asset`, `category`, `priority`, `label`, `source`, `assignedTo`, `createdBy`, `status`, `slaStatus`, `comments`, `attachments`, `histories`.

52. **TicketComment** (thread)
    - `id`, `ticket_id`, `user_id`, `body`, `is_internal`, `timestamps`, `softDeletes`.
    - *Relasi*: `ticket`, `user`.

53. **Attachment** (file upload)
    - `id`, `attachable_type`, `attachable_id`, `user_id`, `filename`, `path`, `mime_type`, `size`, `timestamps`.
    - *Relasi*: `user`.

54. **TicketHistory** (audit log)
    - `id`, `ticket_id`, `user_id`, `field`, `old_value`, `new_value`, `description`, `timestamps`.
    - *Relasi*: `ticket`, `user`.

### 4.8 Audit & Master Auxiliary Models

#### 4.8.1 Stock Audit
55. **StockCount** (audit)
    - `id`, `name`, `location_id`, `created_by_user_id`, `status` (`draft/in_progress/completed`), `start_date`, `end_date`, `notes`, `timestamps`.
    - *Relasi*: `location`, `createdBy`, `items`.

56. **StockCountItem** (audit detail)
    - `id`, `stock_count_id`, `asset_id`, `expected_status` (`available/checked_out/maintenance/retired`), `found_status` (`found/missing/damaged/transferred`), `verified_by_user_id`, `verified_at`, `notes`, `timestamps`.
    - *Relasi*: `stockCount`, `asset`, `verifiedBy`.

#### 4.8.2 Master Auxiliary
57. **Eula** (end-user license agreement)
    - `id`, `category_id`, `title`, `body`, `timestamps`.
    - *Relasi*: `category`.

58. **MaintenanceType** (master)
    - `id`, `name`, `description`, `is_default`, `timestamps`.
    - *Relasi*: `asset_maintenances`, `ga_maintenance_logs`.

#### 4.8.3 Projects & Tasks
59. **Project** (project management)
    - `id`, `code`, `name`, `description`, `owner_id`, `status` (`planning/active/on_hold/completed/cancelled`), `priority` (`low/medium/high/urgent`), `start_date`, `due_date`, `budget`, `timestamps`, `softDeletes`.
    - *Relasi*: `owner`, `tasks`.

60. **ProjectTask** (project tasks)
    - `id`, `project_id`, `title`, `description`, `assigned_to_id`, `status` (`todo/in_progress/review/done`), `priority` (`low/medium/high/urgent`), `start_date`, `due_date`, `estimated_hours`, `actual_hours`, `ticket_id`, `asset_id`, `timestamps`, `softDeletes`.
    - *Relasi*: `project`, `assignedTo`, `ticket`, `asset`.

#### 4.8.4 User-Generated Content
61. **Notification** (laravel notifiable)
    - `id`, `type`, `notifiable_type`, `notifiable_id`, `data` (JSON), `read_at`, `timestamps`.
    - *Relasi*: `notifiable`.

62. **Import** (batch import)
    - `id`, `completed_at`, `file_name`, `file_path`, `importer`, `processed_rows`, `total_rows`, `successful_rows`, `user_id`, `timestamps`.
    - *Relasi*: `user`.

63. **Export** (batch export)
    - `id`, `completed_at`, `file_disk`, `file_name`, `exporter`, `processed_rows`, `total_rows`, `successful_rows`, `user_id`, `timestamps`.
    - *Relasi*: `user`.

64. **FailedImportRow** (import validation)
    - `id`, `data` (JSON), `import_id`, `validation_error`, `timestamps`.
    - *Relasi*: `import`.

---

**Notes**:
- **Soft Deletes**: Digunakan pada semua tabel domain kecuali pivot & audit-only tables.
- **Timestamps**: `created_at`, `updated_at` selalu aktif.
- **Foreign Keys**: Menggunakan `nullOnDelete` atau `cascadeOnDelete` sesuai business logic.
- **Stored Calculated Fields**: `total_asset_value` di `ga_assets`, `book_value` di `assets` → bisa di-migrate ke computed column atau derived via application layer.

### 4.2 Asset & Transaction Models
14. **Asset**: `id`, `parent_asset_id`, `product_id`, `operating_system_id`, `status_id`, `vendor_id`, `asset_tag`, `serial_number`, `product_number`, `device_name`, `user_name`, `license_windows`, `license_office`, `mac_address_wireless`, `mac_address_ethernet`, `specification`, `accessories`, `purchase_date`, `release_date`, `warranty_expiry_date`, `purchase_cost`, `invoice_number`, `notes`, `retired_at`, `retirement_reason`, `retirement_notes`, `assigned_to_user_id`, `department_id`, `location_id`, `current_checkout_id`, `softDeletes`, `timestamps`.
15. **AssetCheckout**: `id`, `asset_id`, `checked_out_to_user_id`, `department_id`, `location_id`, `checked_out_by_user_id`, `checked_out_at`, `expected_checkin_date`, `checked_in_at`, `checked_in_by_user_id`, `checkout_notes`, `checkin_notes`, `signature`, `supervisor_id`, `timestamps`.
16. **AssetMaintenance**: `id`, `asset_id`, `maintenance_type_id`, `vendor_id`, `title`, `start_date`, `completion_date`, `cost`, `notes`, `status`, `timestamps`.
17. **AssetRequest**: `id`, `user_id`, `asset_id`, `category_id`, `request_type`, `reason`, `status`, `processed_by`, `processed_at`, `notes`, `timestamps`.

### 4.3 Custom Fields Engine Models
18. **CustomField**: `id`, `name`, `type`, `options`, `is_required`, `timestamps`.
19. **Fieldset**: `id`, `name`, `timestamps`.
20. **FieldsetCustomField (Pivot)**: `fieldset_id`, `custom_field_id`, `order`.
21. **CategoryFieldset (Pivot)**: `category_id`, `fieldset_id`.
22. **CustomFieldValue**: `id`, `custom_field_id`, `model_type`, `model_id`, `value`, `timestamps`.

### 4.4 Consumable, Component & License Models
23. **Consumable**: `id`, `category_id`, `item_no`, `name`, `min_qty`, `qty`, `timestamps`.
24. **ConsumableTransaction**: `id`, `consumable_id`, `user_id`, `transaction_type` (checkout/restock), `qty`, `cost`, `notes`, `timestamps`.
25. **ConsumableRequest**: `id`, `user_id`, `status`, `processed_by`, `processed_at`, `timestamps`.
26. **ConsumableRequestItem**: `id`, `consumable_request_id`, `consumable_id`, `qty`.
27. **Component**: `id`, `category_id`, `name`, `serial_number`, `qty`, `min_qty`, `timestamps`.
28. **ComponentAsset (Pivot)**: `component_id`, `asset_id`, `qty`, `checked_out_at`.
29. **License**: `id`, `category_id`, `product_key`, `seats`, `reassignable`, `timestamps`.
30. **LicenseSeat**: `id`, `license_id`, `assigned_to_user_id`, `asset_id`, `timestamps`.
31. **Kit**: `id`, `name`, `description`, `timestamps`.
32. **KitPivot Tables**: `kit_product`, `kit_component`, `kit_license`, `kit_consumable`.

### 4.5 General Affairs (GA) Models
33. **GaAssetCategory**: `id`, `name`, `code`, `useful_life_years`, `timestamps`.
34. **GaAssetSubcategory**: `id`, `ga_asset_category_id`, `name`, `timestamps`.
35. **GaAsset**: `id`, `ga_asset_category_id`, `ga_asset_subcategory_id`, `name`, `code`, `serial_number`, `purchase_date`, `purchase_cost`, `current_value`, `qty`, `status`, `image`, `retirement_reason`, `retirement_evidence`, `timestamps`.
36. **GaAssetVehicle**: `id`, `ga_asset_id`, `license_plate`, `engine_number`, `chassis_number`, `expiration_date`, `timestamps`.
37. **GaAssetAssignment**: `id`, `ga_asset_id`, `assigned_to_user_id`, `department_id`, `location_id`, `assigned_at`, `returned_at`, `bast_number`, `evidence`, `timestamps`.
38. **GaMaintenanceLog**: `id`, `ga_asset_id`, `maintenance_type_id`, `cost`, `description`, `performed_at`, `timestamps`.

### 4.6 Ticketing System Models
39. **TicketCategory**: `id`, `name`, `code`, `timestamps`.
40. **TicketSubcategory**: `id`, `ticket_category_id`, `name`, `timestamps`.
41. **Priority**: `id`, `name`, `color`, `level`, `timestamps`.
42. **TicketStatus**: `id`, `name`, `color`, `icon`, `is_closed`, `timestamps`.
43. **SlaStatus**: `id`, `name`, `response_time_hours`, `resolution_time_hours`, `timestamps`.
44. **Source**: `id`, `name`, `timestamps`.
45. **Label**: `id`, `name`, `color`, `timestamps`.
46. **TicketType**: `id`, `name`, `timestamps`.
47. **Ticket**: `id`, `ticket_number`, `subject`, `description`, `user_id`, `assigned_to_user_id`, `asset_id`, `category_id`, `subcategory_id`, `priority_id`, `status_id`, `source_id`, `ticket_type_id`, `sla_status_id`, `due_date`, `hours_spent`, `timestamps`.
48. **TicketComment**: `id`, `ticket_id`, `user_id`, `comment`, `is_internal`, `timestamps`.
49. **Attachment**: `id`, `attachable_type`, `attachable_id`, `file_path`, `file_name`, `file_size`, `mime_type`, `timestamps`.
50. **TicketHistory**: `id`, `ticket_id`, `user_id`, `action`, `changes_json`, `timestamps`.

### 4.7 Audit & Master Auxiliary Models
51. **StockCount**: `id`, `name`, `location_id`, `status`, `started_at`, `completed_at`, `timestamps`.
52. **StockCountItem**: `id`, `stock_count_id`, `asset_id`, `scanned_by_user_id`, `status` (verified/mismatch/missing), `scanned_at`, `notes`.
53. **Eula**: `id`, `name`, `content`, `timestamps`.
54. **MaintenanceType**: `id`, `name`, `timestamps`.

---

## 5. Next.js App Router Structure & Route Mapping

Tabel berikut menunjukkan pemetaan route dari Laravel `routes/web.php` ke file/folder pada Next.js App Router (`app/`):

| Laravel Route | Method | Next.js Page / Route Handler Path | Description |
| :--- | :--- | :--- | :--- |
| `/signin` | GET/POST | `app/(auth)/signin/page.tsx`, `app/api/auth/[...nextauth]/route.ts` | Form Login & Auth Handler |
| `/signup` | GET/POST | `app/(auth)/signup/page.tsx` | Form Registrasi |
| `/forgot-password` | GET/POST | `app/(auth)/forgot-password/page.tsx` | Lupa Password |
| `/` | GET | `app/(dashboard)/page.tsx` | IT Asset Main Dashboard |
| `/ga-dashboard` | GET | `app/(dashboard)/ga-dashboard/page.tsx` | General Affairs Dashboard |
| `/ticket-dashboard` | GET | `app/(dashboard)/ticket-dashboard/page.tsx` | Ticket Overview Dashboard |
| `/assets` | GET | `app/(dashboard)/assets/page.tsx` | Index IT Asset (Pill Tabs, Search, Filter) |
| `/assets/create` | GET/POST | `app/(dashboard)/assets/create/page.tsx` | Form Tambah Asset |
| `/assets/[id]` | GET | `app/(dashboard)/assets/[id]/page.tsx` | Detail Asset & Sub-tabs |
| `/assets/[id]/edit` | GET/PUT | `app/(dashboard)/assets/[id]/edit/page.tsx` | Edit Asset Form |
| `/assets/[id]/checkout` | GET/POST | `app/(dashboard)/assets/[id]/checkout/page.tsx` | Form Checkout Asset |
| `/assets/[id]/checkin` | GET/POST | `app/(dashboard)/assets/[id]/checkin/page.tsx` | Form Checkin Asset |
| `/assets/[id]/bast/[checkout]`| GET | `app/(dashboard)/assets/[id]/bast/[checkout]/page.tsx` | Render Surat BAST PDF |
| `/assets/[id]/maintenance` | GET/POST | `app/(dashboard)/assets/[id]/maintenance/page.tsx` | Input Maintenance Log |
| `/my-assets` | GET | `app/(dashboard)/my-assets/page.tsx` | Portal Aset Saya |
| `/scan/asset/[id]` | GET | `app/scan/asset/[id]/page.tsx` | Public QR Code Scan View |
| `/consumables` | GET/POST | `app/(dashboard)/consumables/page.tsx` | Manajemen Barang Habis Pakai |
| `/consumables/[id]/restock`| GET/POST | `app/(dashboard)/consumables/[id]/restock/page.tsx` | Restock Consumable |
| `/consumable-requests` | GET/POST | `app/(dashboard)/consumable-requests/page.tsx` | Pengajuan Consumable |
| `/licenses` | GET/POST | `app/(dashboard)/licenses/page.tsx` | Manajemen Lisensi Software |
| `/components` | GET/POST | `app/(dashboard)/components/page.tsx` | Manajemen Komponen Hardware |
| `/kits` | GET/POST | `app/(dashboard)/kits/page.tsx` | Pre-defined Hardware Kits |
| `/ga-assets` | GET/POST | `app/(dashboard)/ga-assets/page.tsx` | Index GA Assets |
| `/tickets` | GET/POST | `app/(dashboard)/tickets/page.tsx` | Index Support Tickets |
| `/tickets/[id]` | GET/POST | `app/(dashboard)/tickets/[id]/page.tsx` | Detail Ticket, Balas Komentar & Log |
| `/stock-counts` | GET/POST | `app/(dashboard)/stock-counts/page.tsx` | Audit Stock Opname |
| `/stock-counts/[id]/add-assets`| GET/POST| `app/(dashboard)/stock-counts/[id]/add-assets/page.tsx` | Input Aset Audit |
| `/reports` | GET | `app/(dashboard)/reports/page.tsx` | Activity & Depreciation Reports |
| `/import` | GET/POST | `app/(dashboard)/import/page.tsx` | Bulk Import Excel/CSV |
| `/labels` | GET/POST | `app/(dashboard)/labels/page.tsx` | Generate & Print QR Barcode Labels |
| `/companies` | CRUD | `app/(dashboard)/master/companies/page.tsx` | Master Data Perusahaan |
| `/departments` | CRUD | `app/(dashboard)/master/departments/page.tsx` | Master Data Departemen |
| `/locations` | CRUD | `app/(dashboard)/master/locations/page.tsx` | Master Data Lokasi |
| `/categories` | CRUD | `app/(dashboard)/master/categories/page.tsx` | Master Kategori Aset |
| `/custom-fields` | CRUD | `app/(dashboard)/master/custom-fields/page.tsx` | Engine Field Custom |
| `/users` | CRUD | `app/(dashboard)/users/page.tsx` | Manajemen Pengguna & Access Control |

---

## 6. Business Logic Controllers to Server Actions Mapping

Dalam Next.js App Router, logika pada Laravel Controller dikonversikan menjadi **Server Actions** (`actions/*.ts`) atau API Route Handlers (`app/api/*/route.ts`):

### 6.1 Asset Controller Actions (`actions/assets.ts`)
- `getAssets(params)`: Fetching paginated asset list dengan multi-context search & tab filtering.
- `createAsset(data)`: Validasi form, trigering auto-generation tag/device name, insert DB.
- `updateAsset(id, data)`: Re-validate dan update record aset & custom fields.
- `deleteAsset(id)` / `bulkDeleteAssets(ids)`: Soft delete single atau multiple aset.
- `checkoutAsset(assetId, checkoutData)`: Transaksi checkout, update status aset ke `Deployed`, dan generate record BAST.
- `checkinAsset(assetId, checkinData)`: Transaksi checkin, clear `current_checkout_id`, update status ke `Ready to Deploy`.

### 6.2 Ticket Controller Actions (`actions/tickets.ts`)
- `getTickets(params)`: Mendapatkan tiket berdasarkan status, prioritas, SLA, atau assignee.
- `createTicket(data)`: Pembuatan tiket baru + attachment upload handling.
- `addTicketComment(ticketId, commentData)`: Menambah komentar internal/publik, update status tiket jika perlu, dan broadcast notifikasi.

### 6.3 Consumable & License Actions (`actions/consumables.ts`, `actions/licenses.ts`)
- `processConsumableCheckout(id, data)`: Mengurangi stok `qty` consumable & mencatat `ConsumableTransaction`.
- `processConsumableRestock(id, qty, cost)`: Menambah stok `qty` consumable & mencatat transaksi restock.
- `assignLicenseSeat(licenseId, target)`: Menghubungkan lisensi ke `User` atau `Asset`.

---

## 7. Next.js Frontend Components & Resource Standardization Architecture

Proyek Next.js wajib mengikuti **CRUD Standardization Rules** sesuai panduan `docs/CRUD_STANDARDIZATION.md`:

### 7.1 Unified Table Card Pattern (`components/crud/table-card.tsx`)
- **Card Container**: Wrapper `rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]`.
- **Card Header**:
  - **Pill Tabs Component** (`components/ui/pill-tabs.tsx`): Filter segmentasi (e.g. *All Assets*, *Ready to Deploy*, *Deployed*, *Service*, *Broken*).
  - **Toolbar Component**: Search bar dengan icon search, filter dropdown dengan active counter badge, dan primary button (e.g. `+ Add Asset`).
- **Table Structure**: `table` tanpa `bg-gray-50` pada `thead`. Header cell menggunakan `font-medium text-gray-500 text-xs sm:px-6 py-3`.
- **Bulk Action Bar**: Tampil melayang di atas card ketika checkbox row dipilih (`selectedItems.length > 0`).

### 7.2 Form Layout Pattern (`components/crud/form-layout.tsx`)
- **Split Grid Layout**: Responsive grid 2 kolom `grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]`.
- **Left Column**: Card Form Utama dengan divider `<hr className="border-gray-100 dark:border-gray-800" />` antar section.
- **Right Column (Sidebar)**: Sticky Card Actions containing `Save`, `Save & Create Another`, dan `Cancel`.
- **Form Controls**: Input TailAdmin style (`h-11 rounded-lg border-gray-300 dark:border-gray-700 shadow-sm`). Date Picker menggunakan Flatpickr / React Day Picker.

### 7.3 Detail/Show Page Layout (`components/crud/show-layout.tsx`)
- **Header Card**: Memuat Avatar Icon, Title, Asset Code, Status Badge (`<Badge>`), serta Quick Action Buttons (Edit, Checkout, Maintenance).
- **Tab Navigation**: Pill style tab switching untuk berpindah antara detail spesifikasi, riwayat checkout (`AssetCheckout`), riwayat maintenance, dan helpdesk tickets terkait.

---

## 8. Migration Steps & Execution Plan

1. **Database Setup**:
   - Transpile schema Laravel DB Migrations menjadi Prisma Schema (`prisma/schema.prisma`) atau Drizzle Schema.
   - Run database migration & seed initial data (Statuses, Priorities, Ticket Categories).
2. **Setup Base Next.js Project**:
   - Inisialisasi Next.js App Router dengan TypeScript, Tailwind CSS v4, dan shadcn/ui.
   - Konfigurasi NextAuth.js untuk autentikasi pengguna & RBAC.
3. **Core Shared Layouts & UI Components**:
   - Porting komponen TailAdmin (Sidebar, Header, Breadcrumb, Page Header) ke React RSC.
   - Implementasi komponen standar CRUD (`TableCard`, `FormLayout`, `PillTabs`, `BulkActionBar`).
4. **Module-by-Module Migration Order**:
   - **Phase 1: Master Data**: Companies, Departments, Positions, Locations, Brands, Categories, Statuses, Vendors, Operating Systems, Products.
   - **Phase 2: Core IT Assets & Peripherals**: Asset CRUD, Custom Fields Engine, Checkout/Checkin Flow, BAST PDF Generation.
   - **Phase 3: Consumables, Components, Licenses, Kits**: Consumable Requests, License Seat Allocation, Component Inventory.
   - **Phase 4: General Affairs (GA) Assets**: GA Categories, Vehicle Tracking, GA Maintenance Logs, BAST GA.
   - **Phase 5: Helpdesk & Ticketing System**: Ticket Index, SLA Tracking, Ticket Detail & Discussion Thread, Admin Lookup Config.
   - **Phase 6: Audits & Reports**: Stock Count Audit Opname, Depreciation Reports, Activity Logs, Backup & Import Engine.
5. **Testing & QA**:
   - Unit test untuk Server Actions (auto tag generation, sequence uniqueness, stock balance calculation).
   - E2E testing untuk flow kritis: Checkout Asset -> BAST PDF -> Checkin Asset -> Maintenance Log.

---
*End of PRD Document.*
