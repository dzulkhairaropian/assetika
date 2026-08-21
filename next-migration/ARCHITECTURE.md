# 🏗️ Assetika (IT Only Focus) — Architecture & Next.js 15 Mapping Plan

Dokumen ini berisi arsitektur & pemetaan lengkap **Next.js 15 (App Router)** untuk **Assetika** setelah pembersihan seluruh modul **General Affairs (GA)** agar sistem fokus murni pada **IT Asset Management (ITAM)** dan **Helpdesk (ITSM)** tanpa over-engineering.

---

## 📌 Rekomendasi Tech Stack Next.js

- **Framework**: Next.js 15 (App Router + React 19)
- **Language**: TypeScript (Strict Mode)
- **Database & ORM**: PostgreSQL + Prisma ORM / Drizzle ORM
- **Auth**: Better-Auth / Auth.js dengan Spatie-style RBAC (Roles & Permissions)
- **UI & Design**: Tailwind CSS v4 + Shadcn UI + Lucide/Feather Icons
- **Data Table**: TanStack Table v8 + TanStack Query v5
- **PDF Generation**: `@react-pdf/renderer` (Generasi BAST PDF di client/server)
- **Background Jobs**: BullMQ (Redis) / Inngest (SLA evaluate, Expiry check, Reservation auto checkout)

---

## 🗺️ App Router Route & API Mapping

### 1. Authentication & Access Control

| Feature | Next.js Page Route | API Route / Server Action | Model Utama |
|---|---|---|---|
| Sign In | `/app/(auth)/signin/page.tsx` | `POST /api/auth/signin` | `User` |
| Sign Up | `/app/(auth)/signup/page.tsx` | `POST /api/auth/signup` | `User` |
| Forgot Password | `/app/(auth)/forgot-password/page.tsx` | `POST /api/auth/forgot-password` | `User` |
| Users Management | `/app/(dashboard)/users/page.tsx` | `/api/users` | `User` |
| Roles & Permissions | `/app/(dashboard)/roles/page.tsx` | `/api/roles` | `Role`, `Permission` |

---

### 2. Organization & Master Data

| Feature | Next.js Page Route | API Route | Model |
|---|---|---|---|
| Companies | `/app/(dashboard)/companies/page.tsx` | `/api/companies` | `Company` |
| Departments | `/app/(dashboard)/departments/page.tsx` | `/api/departments` | `Department` |
| Positions | `/app/(dashboard)/positions/page.tsx` | `/api/positions` | `Position` |
| Locations | `/app/(dashboard)/locations/page.tsx` | `/api/locations` | `Location` |
| Brands | `/app/(dashboard)/brands/page.tsx` | `/api/brands` | `Brand` |
| Vendors | `/app/(dashboard)/vendors/page.tsx` | `/api/vendors` | `Vendor` |
| Master Categories | `/app/(dashboard)/master-categories/page.tsx` | `/api/master-categories` | `MasterCategory` |
| Categories | `/app/(dashboard)/categories/page.tsx` | `/api/categories` | `Category` |
| Products | `/app/(dashboard)/products/page.tsx` | `/api/products` | `Product` |
| Operating Systems | `/app/(dashboard)/operating-systems/page.tsx` | `/api/operating-systems` | `OperatingSystem` |
| Asset Statuses | `/app/(dashboard)/statuses/page.tsx` | `/api/statuses` | `Status` |
| Custom Fields & Fieldsets | `/app/(dashboard)/custom-fields/page.tsx` | `/api/custom-fields` | `CustomField`, `Fieldset` |
| Depreciation Profiles | `/app/(dashboard)/depreciations/page.tsx` | `/api/depreciations` | `Depreciation` |
| EULA Templates | `/app/(dashboard)/eulas/page.tsx` | `/api/eulas` | `Eula` |
| Maintenance Types | `/app/(dashboard)/maintenance-types/page.tsx` | `/api/maintenance-types` | `MaintenanceType` |

---

### 3. IT Assets & Inventory Management

| Feature | Next.js Page Route | API Route | Model |
|---|---|---|---|
| IT Dashboard | `/app/(dashboard)/page.tsx` | `/api/dashboard/summary` | Multi |
| IT Assets Inventory | `/app/(dashboard)/assets/page.tsx` | `/api/assets` | `Asset` |
| Asset Detail | `/app/(dashboard)/assets/[id]/page.tsx` | `/api/assets/[id]` | `Asset` |
| My Assigned Assets | `/app/(dashboard)/my-assets/page.tsx` | `/api/my-assets` | `Asset` |
| Asset Checkout Form | `/app/(dashboard)/assets/[id]/checkout/page.tsx` | `POST /api/assets/[id]/checkout` | `AssetCheckout` |
| Asset Checkin Form | `/app/(dashboard)/assets/[id]/checkin/page.tsx` | `POST /api/assets/[id]/checkin` | `AssetCheckout` |
| Download BAST PDF | `/app/(dashboard)/assets/[id]/bast/[checkoutId]/page.tsx` | `GET /api/assets/[id]/bast` | `AssetCheckout` |
| Asset Maintenance | `/app/(dashboard)/assets/[id]/maintenance/page.tsx` | `/api/assets/[id]/maintenance` | `AssetMaintenance` |
| Asset Movement Log | `/app/(dashboard)/asset-transactions/page.tsx` | `/api/asset-transactions` | `AssetCheckout` |
| Consumables | `/app/(dashboard)/consumables/page.tsx` | `/api/consumables` | `Consumable` |
| Consumable Log | `/app/(dashboard)/consumable-transactions/page.tsx` | `/api/consumable-transactions` | `ConsumableTransaction` |
| Hardware Components | `/app/(dashboard)/components/page.tsx` | `/api/components` | `Component` |
| Software Licenses | `/app/(dashboard)/licenses/page.tsx` | `/api/licenses` | `License`, `LicenseSeat` |
| Starter Kits | `/app/(dashboard)/kits/page.tsx` | `/api/kits` | `Kit` |
| Stock Opname / Audit | `/app/(dashboard)/stock-counts/page.tsx` | `/api/stock-counts` | `StockCount`, `StockCountItem` |

---

### 4. Helpdesk & IT Requests

| Feature | Next.js Page Route | API Route | Model |
|---|---|---|---|
| Helpdesk Dashboard | `/app/(dashboard)/ticket-dashboard/page.tsx` | `/api/dashboard/tickets` | `Ticket` |
| Tickets Management | `/app/(dashboard)/tickets/page.tsx` | `/api/tickets` | `Ticket`, `TicketHistory` |
| Ticket Detail & Chat | `/app/(dashboard)/tickets/[id]/page.tsx` | `/api/tickets/[id]/comments` | `TicketComment` |
| Priority Config | `/app/(dashboard)/admin/priorities/page.tsx` | `/api/admin/priorities` | `Priority` |
| Ticket Statuses | `/app/(dashboard)/admin/ticket-statuses/page.tsx` | `/api/admin/ticket-statuses` | `TicketStatus` |
| Ticket Types | `/app/(dashboard)/admin/ticket-types/page.tsx` | `/api/admin/ticket-types` | `TicketType` |
| Ticket Categories | `/app/(dashboard)/admin/ticket-categories/page.tsx` | `/api/admin/ticket-categories` | `TicketCategory`, `TicketSubcategory` |

---

### 5. Self-Service Portal & Next-Phase Modules

| Feature | Next.js Page Route | API Route | Model |
|---|---|---|---|
| Self-Service Catalog | `/app/(dashboard)/requestable-assets/page.tsx` | `/api/requestable-assets` | Multi |
| IT Asset Requests | `/app/(dashboard)/asset-requests/page.tsx` | `/api/asset-requests` | `AssetRequest` |
| Consumable Requests | `/app/(dashboard)/consumable-requests/page.tsx` | `/api/consumable-requests` | `ConsumableRequest` |
| Knowledge Base (KB) | `/app/(dashboard)/kb/page.tsx` | `/api/kb` | `KbArticle`, `KbCategory` |
| KB Suggestions API | — | `GET /api/kb/suggestions` | `KbArticle` |
| SLA Policies Config | `/app/(dashboard)/sla-policies/page.tsx` | `/api/sla-policies` | `SlaPolicy` |
| Asset Reservations | `/app/(dashboard)/reservations/page.tsx` | `/api/reservations` | `AssetReservation` |
| Expiry & Renewal Tracker | `/app/(dashboard)/renewals/page.tsx` | `/api/renewals` | `Renewal` |
| Interactive Calendar | `/app/(dashboard)/calendar/page.tsx` | `/api/calendar/events` | Multi |

---

## ⚡ Improvements & Optimization Over Old Design

1. **Pembersihan Modul Non-IT (General Affairs)**:
   - Dihapus: `ga_assets`, `ga_asset_categories`, `ga_asset_subcategories`, `ga_asset_assignments`, `ga_maintenance_logs`, `ga_asset_vehicles`.
   - Hasil: Mengurangi 6 tabel berlebih dan mencegah kebingungan entitas antara IT Asset vs GA Asset.

2. **Penyederhanaan `AssetReservation`**:
   - Menghapus polymorphic `reservable` $\rightarrow$ langsung menggunakan `asset_id` (FK ke `assets.id`). Peminjaman aset difokuskan murni pada IT Assets (laptop, proyektor, perlengkapan IT).

3. **Penyederhanaan `Renewal` Tracker**:
   - Menghapus tipe `vehicle_tax` & `vehicle_insurance`. Fokus penuh pada expiry SSL, Web Domain, Lisensi Software, dan Kontrak Vendor IT.

4. **Kinerja UI Next.js**:
   - Penggunaan **React Server Components (RSC)** untuk rendering tabel server-side tanpa latency Livewire network round-trip.
   - Penggunaan **TanStack Table** untuk filter client-side kilat, urutan kolom, dan pagination tanpa full-page reload.
