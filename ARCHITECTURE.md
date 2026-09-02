# Arsitektur Aplikasi Assetika

Dokumen ini menjelaskan arsitektur teknis dan struktur sistem **Assetika** (Unified Enterprise Asset & Maintenance System).

---

## 1. Topologi & Hierarchy (Organizational Structure)

Aplikasi ini menggunakan struktur hirarki multi-organisasi bertingkat 3:

```
[ HOLDING / GROUP ]
        │
        ├──► [ COMPANY A ] 
        │          ├──► [ DEPARTMENT IT ]  ──► [ User / Asset ]
        │          └──► [ DEPARTMENT HR ]  ──► [ User / Asset ]
        │
        └──► [ COMPANY B ] (Optional / Multi-subsidiary)
                   └──► [ DEPARTMENT GA ]  ──► [ User / Asset ]
```

- **Group (Holding)**: Wadah induk untuk beberapa perusahaan (`Company`).
- **Company**: Perusahaan/entitas anak usaha (dapat terikat pada Group atau independen).
- **Department**: Divisi dalam perusahaan yang memayungi `User` dan `Asset`.
- **Location**: Lokasi fisik/kantor/cabang yang terkait dengan `Company` dan `Asset`.

---

## 2. Core Modules Architecture

```
                                  ┌───────────────────────────┐
                                  │      System Security      │
                                  │  Spatie RBAC & Auth Guard │
                                  └─────────────┬─────────────┘
                                                │
       ┌────────────────────────┬───────────────┴───────────────┬────────────────────────┐
       ▼                        ▼                               ▼                        ▼
┌──────────────┐       ┌─────────────────┐             ┌────────────────┐       ┌────────────────┐
│   IT Assets  │       │    GA Assets    │             │   Consumables  │       │ Service Desk / │
│  & Hardware  │       │ & Vehicle Regs  │             │  & Components  │       │   Ticketing    │
└──────┬───────┘       └────────┬────────┘             └───────┬────────┘       └───────┬────────┘
       │                        │                              │                        │
       ├─ Auto Asset Tag        ├─ Vehicle Expiry              ├─ Stock Opname          ├─ Ticket SLA
       ├─ Checkout / Checkin    ├─ BAST Auto-Gen               ├─ Component Alloc.      ├─ SLA Policy
       ├─ Maintenance Log       └─ Maintenance Log             └─ Consumable Req.       └─ KnowledgeBase
       └─ Asset Transfer
```

---

## 3. Technology Stack & Frameworks

| Layer | Teknologi / Library | Functionality |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 11/12 (PHP 8.2+) | MVC Core, Routing, Eloquent ORM |
| **Database** | MySQL / PostgreSQL | Relational Database & Foreign Constraints |
| **Authentication & AuthZ** | Spatie Laravel-Permission | Multi-role RBAC (`super_admin`, `group_admin`, `it_admin`, `ga_admin`, `user`) |
| **Frontend Rendering** | Blade Templates + Alpine.js | Reactive UI Components & Client Interactivity |
| **Styling & UI** | Tailwind CSS v4 | Responsive Design, Dark/Light Mode |
| **Export / PDF / Print** | DomPDF, Maatwebsite Excel | Print BAST, Export Report Excel/CSV, Label QR |
| **Media / Storage** | Laravel Storage Disk | Logo Company/Group, Upload BAST Evidence & Attachments |

---

## 4. Key Data Models & Relations

### Organizational Core
- `Group` (`hasMany` Companies)
- `Company` (`belongsTo` Group, `hasMany` Departments, `hasMany` Locations)
- `Department` (`belongsTo` Company, `hasMany` Users, `hasMany` Assets)
- `User` (`belongsTo` Company, Department, Location, Position)

### Asset & Inventory Core
- `Asset` (IT Hardware & Equipment)
  - BelongsTo: `Product`, `Department`, `Location`, `Vendor`, `OperatingSystem`, `Status`
  - HasMany: `AssetCheckout`, `AssetMaintenance`, `AssetTransfer`, `Ticket`
- `GaAsset` (General Affairs Assets & Vehicle Fleet)
  - BelongsTo: `GaAssetCategory`, `GaAssetSubcategory`, `Department`, `Location`
  - HasMany: `GaAssetAssignment`, `GaMaintenanceLog`
- `Consumable` & `ConsumableTransaction` (Tracking stok barang pakai habis)
- `Component`, `License`, `Kit` (Aksesori hardware & Software Licenses)

### Operations & Helpdesk
- `Ticket` (Service desk & Issue tracking)
  - BelongsTo: `User` (Requester & Assigned IT), `Priority`, `TicketStatus`, `TicketCategory`, `SlaPolicy`
- `StockCount` & `StockCountItem` (Stock Opname & Audit)

---

## 5. Security & Scope Boundaries

1. **Super Admin**: Akses penuh seluruh sistem, Group, dan Perusahaan.
2. **Group Admin**: Akses terisolasi pada seluruh Company yang berada di bawah Group miliknya.
3. **Company / IT / GA Admin**: Akses terbatas pada Company/Department terkait.
4. **User / Employee**: Akses self-service (`My Assets`, Pengajuan `Consumable Request`, & `Create Ticket`).

---

## 6. Workflow Utama

1. **Penerimaan & Auto Tagging**:
   - Format otomatis: `[COMPANY_CODE]/[CATEGORY_CODE]/[YYMMDD]/[SEQUENCE]` (Contoh: `DB/NB/260902/001`)
2. **Check-out / Check-in & BAST**:
   - Penyerahan unit melegalisir bukti BAST dengan tandatangan / bukti upload.
3. **Transfer Antar Company / Department**:
   - Pengajuan via `AssetTransfer` -> Approval -> Eksekusi update otomatis lokasi & unit.
4. **Helpdesk & SLA Tracking**:
   - Permasalahan ditangani via Ticket berdasar matriks Prioritas & SLA.
