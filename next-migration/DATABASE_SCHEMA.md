# 📑 Assetika (IT Only Focus) — Database Schema Reference

Dokumen ini berisi spesifikasi skema database **Assetika Next.js (Fokus Khusus IT Asset & Helpdesk Management)**. Seluruh modul **General Affairs (GA)** (tabel `ga_assets`, `ga_asset_categories`, `ga_asset_subcategories`, `ga_asset_assignments`, `ga_maintenance_logs`, `ga_asset_vehicles`) dan tipe `vehicle_tax`/`vehicle_insurance` pada `renewals` telah dibuang agar sistem tidak over-engineered dan ringan.

---

## 📌 Ringkasan Modul Database (61 Tabel)

1. [Core & Auth System (14 tabel)](#1-core--auth-system)
2. [Organization & Master Data (19 tabel)](#2-organization--master-data)
3. [IT Assets & Inventory Management (16 tabel)](#3-it-assets--inventory-management)
4. [Helpdesk & IT Requests (15 tabel)](#4-helpdesk--it-requests)
5. [Project & Task Management (2 tabel)](#5-project--task-management)
6. [Knowledge Base & IT Next-Phase Modules (6 tabel)](#6-knowledge-base--it-next-phase-modules)
7. [Import & Export System (3 tabel)](#7-import--export-system)

---

## 1. Core & Auth System

### `users`
| Kolom | Tipe Data | Atribut & Constraint |
|---|---|---|
| `id` | `bigint unsigned` | PK, Auto Increment |
| `name` | `varchar(255)` | NOT NULL |
| `email` | `varchar(255)` | NOT NULL, Unique |
| `email_verified_at` | `timestamp` | NULL |
| `password` | `varchar(255)` | NOT NULL |
| `remember_token` | `varchar(100)` | NULL |
| `phone` | `varchar(255)` | NULL, Unique |
| `avatar` | `varchar(255)` | NULL |
| `department_id` | `bigint unsigned` | NULL |
| `company_id` | `bigint unsigned` | NULL |
| `location_id` | `bigint unsigned` | NULL, FK $\rightarrow$ `locations.id` (ON DELETE SET NULL) |
| `position_id` | `bigint unsigned` | NULL, FK $\rightarrow$ `positions.id` (ON DELETE SET NULL) |
| `is_active` | `tinyint(1)` | DEFAULT `true` |
| `created_at` / `updated_at` | `timestamp` | NULL |

### `password_reset_tokens`
| Kolom | Tipe Data | Atribut & Constraint |
|---|---|---|
| `email` | `varchar(255)` | PK |
| `token` | `varchar(255)` | NOT NULL |
| `created_at` | `timestamp` | NULL |

### `sessions`
- `id` PK, `user_id` (index), `ip_address`, `user_agent`, `payload`, `last_activity` (index)

### `cache` & `cache_locks`
- `cache` (`key` PK, `value` mediumtext, `expiration` bigint)
- `cache_locks` (`key` PK, `owner` varchar, `expiration` bigint)

### `jobs`, `job_batches`, `failed_jobs`
- `jobs` (`id` PK, `queue` index, `payload` longtext, `attempts`, `reserved_at`, `available_at`, `created_at`)
- `job_batches` (`id` PK, `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at`)
- `failed_jobs` (`id` PK, `uuid` unique, `connection`, `queue`, `payload`, `exception`, `failed_at`)

### `notifications`
- `id` (`uuid` PK), `type`, `notifiable_type`, `notifiable_id` (Polymorphic Index), `data` text, `read_at`, `timestamps`

### Roles & Permissions (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`)
- Standar RBAC Spatie / Better-Auth Next.js RBAC.

---

## 2. Organization & Master Data

### `companies`
- `id` PK, `name` (index), `code` (unique), `email` (unique), `phone` (unique), `website`, `address`, `timestamps`, `deleted_at`

### `departments`
- `id` PK, `name` (index), `code` (unique), `company_id` (FK $\rightarrow$ `companies.id`), `description`, `timestamps`, `deleted_at`

### `positions`
- `id` PK, `name` (index), `code` (unique), `description`, `timestamps`, `deleted_at`

### `locations`
- `id` PK, `name` (index), `company_id` (FK $\rightarrow$ `companies.id`), `description`, `timestamps`, `deleted_at`

### `brands`
- `id` PK, `name` (index), `code` (unique), `website`, `description`, `timestamps`, `deleted_at`

### `vendors`
- `id` PK, `name` (index), `email` (unique), `contact_person`, `phone` (unique), `website`, `address`, `npwp_number`, `npwp_name`, `npwp_photo`, `nib`, `bank_name`, `bank_account_number`, `bank_account_name`, `notes`, `timestamps`, `deleted_at`

### `master_categories`
- `id` PK, `name` (index), `description`, `timestamps`, `deleted_at`

### `categories`
- `id` PK, `name` (index), `code` (unique), `master_category_id` (FK $\rightarrow$ `master_categories.id`), `description`, `timestamps`, `deleted_at`

### `operating_systems`
- `id` PK, `name` (index), `brand_id` (FK $\rightarrow$ `brands.id`), `description`, `timestamps`, `deleted_at`

### `statuses`
- `id` PK, `name` (index), `icon`, `color`, `master_category_id` (FK $\rightarrow$ `master_categories.id`), `description`, `timestamps`, `deleted_at`

### `products`
- `id` PK, `name` (index), `product_type` (index), `brand_id` (FK $\rightarrow$ `brands.id`), `category_id` (FK $\rightarrow$ `categories.id`), `master_category_id` (FK $\rightarrow$ `master_categories.id`), `description`, `timestamps`, `deleted_at`

### `maintenance_types`
- `id` PK, `name`, `description`, `is_default` (boolean), `timestamps`

### `depreciations`
- `id` PK, `name`, `months` (integer default 36), `timestamps`

### `eulas`
- `id` PK, `category_id` (FK $\rightarrow$ `categories.id`), `title`, `body`, `timestamps`

### Custom Fields (`custom_fields`, `fieldsets`, `custom_field_fieldset`, `category_fieldset`, `custom_field_values`)
- System dynamic fields untuk atribut spesifik hardware IT.

---

## 3. IT Assets & Inventory Management

### `assets`
- `id` PK
- `parent_asset_id` (FK $\rightarrow$ `assets.id` self-referencing)
- `department_id` (FK $\rightarrow$ `departments.id`)
- `product_id` (FK $\rightarrow$ `products.id`)
- `operating_system_id` (FK $\rightarrow$ `operating_systems.id`)
- `status_id` (FK $\rightarrow$ `statuses.id`)
- `location_id` (FK $\rightarrow$ `locations.id`)
- `vendor_id` (FK $\rightarrow$ `vendors.id`)
- `assigned_to_user_id` (FK $\rightarrow$ `users.id`)
- `asset_tag` (unique), `serial_number` (index), `product_number` (index), `device_name` (index), `user_name` (index)
- `license_windows`, `license_office`, `mac_address_wireless`, `mac_address_ethernet`
- `specification` (text), `accessories` (text), `notes` (text)
- `purchase_date`, `release_date`, `warranty_expiry_date`, `retired_at`
- `purchase_cost` (decimal 15,2), `invoice_number`
- `current_checkout_id` (FK $\rightarrow$ `asset_checkouts.id`)
- `depreciation_id` (FK $\rightarrow$ `depreciations.id`)
- `retirement_reason`, `retirement_notes`
- `timestamps`, `deleted_at`

### `asset_checkouts`
- `id` PK, `asset_id` (FK $\rightarrow$ `assets.id`)
- `checked_out_by_user_id` (FK $\rightarrow$ `users.id`), `checked_out_to_user_id` (FK $\rightarrow$ `users.id`), `supervisor_user_id` (FK $\rightarrow$ `users.id`)
- `department_id` (FK), `location_id` (FK)
- `checked_out_at`, `checkout_notes`, `checkout_evidence_file_path`, `checkout_evidence_file_name`
- `eula_text`, `signature_path`, `accepted_at`
- `checked_in_at`, `checked_in_by_user_id` (FK), `checkin_status_id` (FK $\rightarrow$ `statuses.id`)
- `condition_report`, `checkin_evidence_file_path`, `checkin_evidence_file_name`, `checkin_notes`
- `timestamps`

### `asset_maintenances`
- `id` PK, `asset_id` (FK $\rightarrow$ `assets.id`), `maintenance_type_id` (FK $\rightarrow$ `maintenance_types.id`), `service_provider`, `start_date`, `completion_date`, `cost`, `description`, `evidence_file_path`, `notes`, `timestamps`

### `consumables` & `consumable_transactions`
- `consumables`: `id` PK, `product_id` (FK), `status_id` (FK), `quantity`, `low_threshold`, `purchase_cost`, `notes`, `timestamps`, `deleted_at`
- `consumable_transactions`: `id` PK, `consumable_id` (FK), `user_id` (FK), `checked_out_to_user_id` (FK), `type` ('restock','checkout'), `quantity`, `quantity_before`, `quantity_after`, `unit_cost`, `total_cost`, `evidence_file_path`, `notes`, `transaction_date`, `timestamps`

### `components` & `component_asset`
- `components`: `id` PK, `name`, `category_id` (FK), `manufacturer`, `model_number`, `serial`, `qty`, `min_qty`, `purchase_cost`, `location_id` (FK), `notes`, `image`, `timestamps`
- `component_asset`: pivot (`component_id`, `asset_id`, `qty`, `checked_out_at`)

### `licenses` & `license_seats`
- `licenses`: `id` PK, `name`, `product_key`, `seats`, `license_email`, `manufacturer`, `purchase_date`, `expiration_date`, `purchase_cost`, `notes`, `timestamps`
- `license_seats`: `id` PK, `license_id` (FK), `assigned_to_type`/`assigned_to_id` (Polymorphic), `checked_out_at`, `notes`, `timestamps`

### Kits System (`kits`, `kit_asset`, `kit_license`, `kit_component`, `kit_consumable`)
- Paket starter kit IT (Laptop + Mouse + Lisensi) untuk onboarding karyawan.

### Stock Audit (`stock_counts`, `stock_count_items`)
- `stock_counts`: `id` PK, `name`, `location_id` (FK), `created_by_user_id` (FK), `status` ('draft','in_progress','completed'), `start_date`, `end_date`, `notes`, `timestamps`
- `stock_count_items`: `id` PK, `stock_count_id` (FK), `asset_id` (FK), `expected_status`, `found_status`, `verified_by_user_id` (FK), `verified_at`, `notes`, `timestamps`

---

## 4. Helpdesk & IT Requests

### Lookups (`priorities`, `ticket_statuses`, `status_slas`, `labels`, `sources`, `ticket_types`, `ticket_categories`, `ticket_subcategories`)
- Master data penanganan tiket helpdesk & SLA.

### `tickets`
- `id` PK, `request_id` (unique)
- `ticket_type_id` (FK), `requester_user_id` (FK), `requester_name`, `requester_email`, `requester_company_id` (FK), `requester_department_id` (FK)
- `asset_id` (FK $\rightarrow$ `assets.id`), `category_id` (FK), `ticket_category_id` (FK), `ticket_subcategory_id` (FK)
- `priority_id` (FK), `label_id` (FK), `source_id` (FK), `status_id` (FK), `sla_status_id` (FK)
- `problem_description`, `internal_notes`, `resolution_notes`
- `assigned_to_id` (FK $\rightarrow$ `users.id`), `created_by_id` (FK $\rightarrow$ `users.id`)
- `request_date`, `request_time` (datetime), `completed_date`, `completed_time` (datetime)
- `response_time_minutes`, `resolution_time_hours`, `estimated_hours`, `actual_hours`
- `sla_due_at`, `sla_paused_at`, `total_paused_minutes`
- `timestamps`, `deleted_at`

### `ticket_comments`, `attachments`, `ticket_histories`
- Tracking obrolan, lampiran gambar/file, dan riwayat audit tiket IT.

### Requests System (`asset_requests`, `consumable_requests`, `consumable_request_items`)
- Modul self-service permintaan barang/lisensi IT dari karyawan.

---

## 5. Project & Task Management

### `projects` & `project_tasks`
- Proyek & penugasan teknis IT.

---

## 6. Knowledge Base & IT Next-Phase Modules

### Knowledge Base (`kb_categories`, `kb_articles`, `kb_tags`, `kb_article_tag`)
- Artikel solusi mandiri seputar IT.

### `sla_policies`
- Definisi target waktu respon & penyelesaian tiket IT per tingkat prioritas.

### `asset_reservations`
- `id` PK, `asset_id` (FK $\rightarrow$ `assets.id`), `user_id` (FK $\rightarrow$ `users.id`), `approved_by_id` (FK $\rightarrow$ `users.id`), `start_time` (datetime), `end_time` (datetime), `status` ('pending','approved','rejected','checked_out','completed','cancelled'), `purpose` (text), `notes`, `timestamps`
- *Note Improvement*: Menyederhanakan polymorphic `reservable` langsung menjadi `asset_id` karena fokus murni IT Asset (Laptop/Proyektor).

### `renewals`
- `id` PK, `name`, `type` ('domain','ssl_certificate','license','custom'), `license_id` (FK $\rightarrow$ `licenses.id` nullable), `issued_date`, `expiration_date`, `reminder_days_before`, `vendor_id` (FK $\rightarrow$ `vendors.id`), `responsible_user_id` (FK $\rightarrow$ `users.id`), `cost`, `auto_renew`, `status` ('active','expiring_soon','expired','renewed'), `notes`, `timestamps`
- *Note Improvement*: Menghapus tipe `vehicle_tax` & `vehicle_insurance` GA.

---

## 7. Import & Export System

### `imports`, `exports`, `failed_import_rows`
- Modul background import/export CSV/Excel data masal IT.
