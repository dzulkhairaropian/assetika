# PRD: Snipe-IT Gap Implementation — Assetika

> **Sumber studi**: `docs/template_snipe_it/` (routes, views, modals, reports, settings)
> **Tanggal**: 2026-08-07
> **Status**: Draft — menunggu approval Fase 1

---

## 1. Executive Summary

Studi struktur Snipe-IT mengungkap arsitektur enterprise asset management yang matang: custom fields engine, component tracking, pre-defined kits, license management, depreciation calculator, digital signature acceptance, bulk operations, CSV importer, dan label/barcode generator.

Assetika sudah memiliki pondasi (IT Asset, GA Asset, Consumables, Helpdesk Ticket, Role/Permission). PRD ini menutup gap fungsionalitas untuk mencapai paritas enterprise.

---

## 2. Gap Analysis Lengkap

| # | Modul Snipe-IT | Ada di Assetika? | Prioritas | Keterangan |
|:-:|:---|:---|:---|:---|
| 1 | **Custom Fields & Fieldsets** | ❌ | HIGH | Atribut dinamis per kategori tanpa ubah skema DB |
| 2 | **Component Management** | ❌ | HIGH | Tracking sub-komponen (RAM, SSD) terpisah dari main asset |
| 3 | **Pre-defined Kits** | ❌ | MEDIUM | Bundle hardware+software+accessory untuk onboarding cepat |
| 4 | **Software Licenses & Seats** | ❌ | MEDIUM | Manajemen lisensi, alokasi seat, expiration alert |
| 5 | **Depreciation Calculator** | ❌ | LOW | Kalkulasi penyusutan nilai finansial (straight-line) |
| 6 | **Bulk Operations** | ❌ | MEDIUM | Edit/checkout/delete massal aset & user |
| 7 | **CSV/Excel Importer** | ❌ | MEDIUM | Import massal via file |
| 8 | **Digital Signature & EULA** | ❌ | LOW | Tanda tangan digital serah terima aset |
| 9 | **Label/Barcode Generator Engine** | Parsial | MEDIUM | PDF label dengan QR code, layout fleksibel |
| 10 | **Requestable Assets** | ❌ | LOW | User bisa request aset, admin approve |
| 11 | **Maintenance Types** | ✅ (partial) | LOW | Sudah ada `maintenance_types` |
| 12 | **Action Log / Audit Trail** | ✅ (partial) | LOW | Sudah ada `activity_log` |
| 13 | **Settings Hub (LDAP, SAML, Slack, Backups)** | Parsial | LOW | Backups ada, sisanya belum |
| 14 | **Reports Engine** | ❌ | MEDIUM | Laporan custom, depreciation, audit, activity |
| 15 | **Suppliers** | ✅ | — | Sudah ada `Vendor` |
| 16 | **Manufacturers** | ❌ | LOW | Belum ada, bisa pakai `Brand` |
| 17 | **Status Labels** | ✅ | — | Sudah ada `Status` |
| 18 | **Departments** | ✅ | — | Sudah ada `Department` |
| 19 | **Companies** | ✅ | — | Sudah ada `Company` |
| 20 | **Categories** | ✅ | — | Sudah ada `Category` + `MasterCategory` |
| 21 | **Locations** | ✅ | — | Sudah ada `Location` |

---

## 3. Detail Fitur per Fase

### FASE 1: Core Flexibility & Component Tracking

#### 3.1.1 Custom Fields & Fieldsets

**Tujuan**: Admin bisa menambah atribut custom pada form aset tanpa developer.

**Entity**:
```
custom_fields
  - id, name, field_type (text|textarea|dropdown|date|boolean|number)
  - field_values (JSON: opsi dropdown)
  - help_text, default_value, is_required, order

fieldsets
  - id, name

fieldset_field (pivot)
  - fieldset_id, custom_field_id, order, is_required

category_fieldset (pivot)
  - category_id, fieldset_id
```

**Flow**:
1. Admin buat `CustomField` (contoh: "MAC Address", tipe text)
2. Admin buat `Fieldset` (contoh: "Network Devices")
3. Admin attach CustomField ke Fieldset
4. Admin attach Fieldset ke Category "Network"
5. Form create/edit asset kategori "Network" otomatis tampilkan field "MAC Address"

**UI Pages**:
- `admin/custom-fields` — list, create, edit, delete
- `admin/fieldsets` — list, create, edit, delete
- Dynamic render di form `asset/create` & `asset/edit`

#### 3.1.2 Component Management

**Tujuan**: Tracking stok komponen internal hardware (RAM, SSD, GPU, PSU) yang bisa dipasang/dilepas.

**Entity**:
```
components
  - id, name, category_id, manufacturer_id, model_number
  - serial, qty, min_qty (alert threshold), purchase_cost
  - location_id, notes, image

component_assets (pivot)
  - component_id, asset_id, qty, checked_out_at
```

**Fitur**:
- CRUD komponen dengan stok `qty`
- Checkout komponen ke Asset (qty terpakai vs tersedia)
- Checkin komponen dari Asset
- Alert stok menipis (qty <= min_qty)
- History log komponen

**UI Pages**:
- `components/` — index, create, edit, view
- Checkout/checkin modal dari detail komponen atau detail asset

---

### FASE 2: Kits & Software Licenses

#### 3.2.1 Pre-defined Kits

**Tujuan**: Bundle item untuk onboarding karyawan (1 klik checkout semua).

**Entity**:
```
kits
  - id, name, description

kit_assets (pivot)
  - kit_id, asset_id, qty

kit_licenses (pivot)
  - kit_id, license_id, qty

kit_components (pivot)
  - kit_id, component_id, qty

kit_consumables (pivot)
  - kit_id, consumable_id, qty
```

**Fitur**:
- CRUD kit: pilih kombinasi asset model, license, component, consumable
- 1-Click Checkout Kit → semua item otomatis checkout ke user
- View kit detail & history

**UI Pages**:
- `kits/` — index, create, edit, view
- `users/{id}/checkout-kit` — modal pilih kit

#### 3.2.2 Software Licenses

**Tujuan**: Manajemen aset digital & alokasi lisensi.

**Entity**:
```
licenses
  - id, name, manufacturer_id, category_id
  - serial, seats (total), purchase_cost, purchase_date
  - expiration_date, notes

license_seats
  - id, license_id, asset_id (nullable), user_id (nullable)
  - assigned_to_type, assigned_to_id (polymorphic)
  - checked_out_at
```

**Fitur**:
- CRUD license dengan total seats
- Checkout license ke User atau Asset
- Auto-decrement available seats
- Alert expired / nearing expiration
- Laporan lisensi

**UI Pages**:
- `licenses/` — index, create, edit, view
- Checkout modal

---

### FASE 3: Finance & Reporting

#### 3.3.1 Depreciation Calculator

**Tujuan**: Hitung penyusutan nilai aset otomatis.

**Entity**:
```
depreciations
  - id, name, months (durasi)

asset_model (existing, tambah field)
  - depreciation_id (nullable)
```

**Fitur**:
- CRUD profil depresiasi
- Assign profil ke model aset
- Kalkulasi book value real-time di detail aset
- Laporan depresiasi bulanan/tahunan

#### 3.3.2 Label & Barcode Generator

**Tujuan**: PDF label dengan QR code, layout fleksibel.

**Fitur**:
- Pilih template layout (Avery, custom)
- Generate QR code dari asset tag
- Batch print label dari list aset

#### 3.3.3 Reports Engine

**Tujuan**: Laporan terstruktur untuk audit, aktivitas, depresiasi.

**Fitur**:
- Activity Report (log history)
- Asset Report (daftar aset filterable)
- Depreciation Report
- Audit Report
- Custom Report (pilih field & filter)

---

### FASE 4: Operations & Experience

#### 3.4.1 Bulk Operations

**Tujuan**: Operasi massal pada aset.

**Fitur**:
- Bulk delete, bulk edit, bulk checkout, bulk status change
- Checkbox select di tabel index
- Konfirmasi modal

#### 3.4.2 CSV/Excel Importer

**Tujuan**: Import massal data aset, user, consumable.

**Fitur**:
- Upload CSV/Excel
- Column mapping UI
- Validation & error reporting
- Dry-run preview sebelum commit

#### 3.4.3 Digital Signature & EULA

**Tujuan**: Bukti serah terima digital.

**Fitur**:
- EULA template per kategori
- Signature pad di acceptance flow
- Stored signature & EULA di action log

---

## 4. Roadmap Visual

```
FASE 1 (2-3 sprint)           FASE 2 (2 sprint)            FASE 3 (2 sprint)          FASE 4 (1-2 sprint)
┌─────────────────────┐      ┌───────────────────┐       ┌──────────────────────┐    ┌───────────────────────┐
│ Custom Fields       │      │ Pre-defined Kits  │       │ Depreciation Calc    │    │ Bulk Operations       │
│ Fieldsets           │  →   │ License Management│   →   │ Label/Barcode Engine │ →  │ CSV/Excel Importer    │
│ Component Tracking  │      │                   │       │ Reports Engine       │    │ Digital Signature     │
└─────────────────────┘      └───────────────────┘       └──────────────────────┘    └───────────────────────┘
```

---

## 5. Non-Functional Requirements

| Aspek | Requirement |
|:---|:---|
| **Security** | Sanitize custom field values (XSS). RBAC per modul. |
| **Performance** | Custom field values di JSON column atau EAV indexed. N+1 prevention. |
| **Backward Compatibility** | Asset & GA Asset existing flow tidak boleh rusak. |
| **UI Consistency** | Ikuti pola Blade + Bootstrap existing di Assetika. |
| **Audit Trail** | Semua mutasi catat di `activity_log`. |
| **Testing** | Unit test untuk kalkulasi depresiasi. Feature test untuk checkout flow. |

---

## 6. Open Questions

1. Custom field values: JSON column vs EAV table? → Rekomendasi: JSON column (MariaDB 10.2+ support).
2. Component checkout: boleh partial qty atau harus full? → Snipe-IT support partial qty.
3. Kit: perlu approval workflow atau langsung checkout? → Fase 2 langsung checkout, approval di Fase 4.
4. License: perlu tracking renewal reminder email? → Fase 2 basic alert, email di Fase 3.

---

## 7. Acceptance Criteria per Fase

### Fase 1
- [ ] Admin bisa membuat CustomField dan Fieldset via UI
- [ ] Fieldset bisa di-attach ke Category
- [ ] Form create/edit Asset menampilkan custom field sesuai kategori
- [ ] Nilai custom field tersimpan dan tampil di detail aset
- [ ] Admin bisa membuat Component dengan stok qty
- [ ] Component bisa di-checkout ke Asset (qty berkurang)
- [ ] Component bisa di-checkin dari Asset (qty bertambah)
- [ ] Alert tampil jika qty component <= min_qty

### Fase 2
- [ ] Admin bisa membuat Kit (kombinasi aset, license, component, consumable)
- [ ] Kit bisa di-checkout ke User (semua item otomatis)
- [ ] Admin bisa membuat License dengan N seats
- [ ] License bisa di-checkout ke User/Asset (seat berkurang)
- [ ] Alert lisensi nearing expiration

### Fase 3
- [ ] Admin bisa membuat profil depresiasi (straight-line, N bulan)
- [ ] Nilai buku aset terhitung otomatis di detail aset
- [ ] Generate PDF label dengan QR code
- [ ] Batch print label dari checkbox select
- [ ] Activity Report dengan filter tanggal & user
- [ ] Asset Report dengan multi-filter

### Fase 4
- [ ] Bulk delete, bulk edit, bulk status change aset
- [ ] Upload CSV, mapping kolom, import dengan error report
- [ ] EULA template per kategori
- [ ] Signature pad di checkout flow
- [ ] Signature & EULA tersimpan di action log

---

## 8. Referensi

- `docs/template_snipe_it/routes/web.php` — routing structure
- `docs/template_snipe_it/routes/web/hardware.php` — asset CRUD pattern
- `docs/template_snipe_it/routes/web/models.php` — asset model pattern
- `docs/template_snipe_it/routes/web/licenses.php` — license pattern
- `docs/template_snipe_it/routes/web/kits.php` — kit pattern
- `docs/template_snipe_it/routes/web/components.php` — component pattern
- `docs/template_snipe_it/routes/web/fields.php` — custom fields pattern
- `docs/template_snipe_it/routes/web/consumables.php` — consumable pattern
- `docs/template_snipe_it/views/settings/` — admin settings hub
- `docs/template_snipe_it/views/reports/` — reporting engine
